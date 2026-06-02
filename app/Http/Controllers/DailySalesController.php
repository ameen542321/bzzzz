<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Store;
use App\Models\Product;
use App\Models\CreditSale;
use App\Models\Expense;
use App\Models\Withdrawal;
use App\Models\DailyBalance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class DailySalesController extends Controller
{
    private array $processedSalesCache = [];

    public function index(Store $store, Request $request)
    {
        $selectedDate = $request->filled('date')
            ? Carbon::parse($request->date)->startOfDay()
            : Carbon::today()->startOfDay();

        $shiftWindows = $this->buildShiftWindows($store->id, $selectedDate);

        // fallback آمن: إذا لا توجد شفتات مغلقة/حالية نرجع لفترة يومية تقويمية
        if ($shiftWindows->isEmpty()) {
            $shiftWindows = collect([[
                'key' => 'default_shift',
                'label' => 'الفترة اليومية',
                'start' => $selectedDate->copy()->startOfDay(),
                'end' => $selectedDate->copy()->endOfDay(),
                'source' => 'calendar',
            ]]);
        }

        $startTime = $shiftWindows->first()['start'];
        $endTime = $shiftWindows->last()['end'];
        $selectedShift = $shiftWindows->contains(fn($w) => ($w['source'] ?? null) === 'balance') ? 'shift_based' : null;

        $buildSalesQuery = function () use ($store) {
            return Sale::where('sales.store_id', $store->id)
            ->where(function ($q) {
                $q->whereNull('sales.description')
                    ->orWhere('sales.description', '!=', 'manual_invoice_entry');
            })
            ->leftJoin('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'sales.*',
                'sale_items.id as item_id',
                'sale_items.product_id',
                'sale_items.quantity as item_quantity',
                'sale_items.price as item_price',
                'sale_items.total as item_total',
                'sale_items.is_custom',
                'sale_items.custom_name',
                'sale_items.custom_consumption',
                'sale_items.custom_meters',
                'products.name as product_name',
                'products.cost_price as product_cost_price',
                'products.product_type as product_type',
                'products.is_splittable as product_is_splittable',
                'products.items_per_unit as product_items_per_unit',
                'products.roll_length as product_roll_length'
            )
            ->with(['employee', 'accountant']);
        };

        // استعلام المبيعات مع المنتجات
        $query = $buildSalesQuery();

        $this->applyPeriodFilter($query, $shiftWindows);

        // فلترة البحث (رقم العملية / اسم المنتج / اسم العنصر المخصص / وصف العملية)
        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->orWhere('sales.id', (int) $search);
                }

                $q->orWhere('products.name', 'like', "%{$search}%")
                    ->orWhere('sale_items.custom_name', 'like', "%{$search}%")
                    ->orWhere('sales.description', 'like', "%{$search}%");
            });
        }

        // تنفيذ الاستعلام
        $results = $query->orderBy('sales.created_at', 'desc')->get();

        // fallback: إذا الشفتات المغلقة أعادت نتائج صفرية نرجع للفترة اليومية لنفس التاريخ
        if ($results->isEmpty() && $shiftWindows->contains(fn($w) => ($w['source'] ?? null) === 'balance')) {
            $shiftWindows = collect([[
                'key' => 'default_shift',
                'label' => 'الفترة اليومية',
                'start' => $selectedDate->copy()->startOfDay(),
                'end' => $selectedDate->copy()->endOfDay(),
                'source' => 'calendar_fallback',
            ]]);

            $startTime = $shiftWindows->first()['start'];
            $endTime = $shiftWindows->last()['end'];
            $selectedShift = null;

            $query = $buildSalesQuery();
            $this->applyPeriodFilter($query, $shiftWindows);

            if ($request->filled('search')) {
                $search = trim((string) $request->search);

                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->orWhere('sales.id', (int) $search);
                    }

                    $q->orWhere('products.name', 'like', "%{$search}%")
                        ->orWhere('sale_items.custom_name', 'like', "%{$search}%")
                        ->orWhere('sales.description', 'like', "%{$search}%");
                });
            }

            $results = $query->orderBy('sales.created_at', 'desc')->get();
        }

        // إعادة تجميع النتائج حسب كل عملية بيع
        $sales = collect();
        $currentSale = null;

        foreach ($results as $row) {
            if (!$currentSale || $currentSale->id != $row->id) {
                if ($currentSale) {
                    $processed = $this->processSale($currentSale);
                    $processed->shift_key = $this->resolveShiftKey($processed->created_at, $shiftWindows);
                    $sales->push($processed);
                }

                $currentSale = clone $row;
                $currentSale->items = collect();
                $currentSale->total_cost = 0;
                $currentSale->total_profit = 0;
                $currentSale->products_total_value = 0;
            }

            if ($row->item_id) {
                $currentSale->items->push((object)[
                    'id' => $row->item_id,
                    'product_id' => $row->product_id,
                    'quantity' => $row->item_quantity,
                    'price' => $row->item_price,
                    'total' => $row->item_total,
                    'is_custom' => $row->is_custom,
                    'custom_name' => $row->custom_name,
                    'custom_consumption' => $row->custom_consumption,
                    'custom_meters' => $row->custom_meters,
                    'product_name' => $row->product_name ?? 'منتج غير معروف',
                    'cost_price' => $row->product_cost_price ?? 0,
                    'product_type' => $row->product_type,
                    'is_splittable' => (bool) ($row->product_is_splittable ?? false),
                    'items_per_unit' => (float) ($row->product_items_per_unit ?? 0),
                    'roll_length' => (float) ($row->product_roll_length ?? 0)
                ]);

                $currentSale->products_total_value += $row->item_total ?? 0;
            }
        }

        if ($currentSale) {
            $processed = $this->processSale($currentSale);
            $processed->shift_key = $this->resolveShiftKey($processed->created_at, $shiftWindows);
            $sales->push($processed);
        }

        $visibleSaleIds = $sales->pluck('id')->filter()->map(fn ($id) => (int) $id)->values()->all();
        $collectionOperations = $this->getCreditCollectionOperations($store->id, $shiftWindows, $visibleSaleIds);
        $sales = $sales
            ->concat($collectionOperations)
            ->sortByDesc(fn ($entry) => $entry->display_time ?? $entry->created_at ?? now())
            ->values();

        $employees = $store->employees()->select('id', 'name')->orderBy('name')->get();

        $shiftSummaries = $shiftWindows->map(function ($window) use ($sales, $store) {
            $shiftSales = $sales->filter(fn($sale) => ($sale->shift_key ?? 'default_shift') === $window['key']);
            $shiftSaleOperations = $shiftSales->filter(fn($sale) => ($sale->operation_kind ?? null) !== 'collection');
            $shiftCollectionOperations = $shiftSales->filter(fn($sale) => ($sale->operation_kind ?? null) === 'collection');
            $tadlilOperations = $shiftSaleOperations->filter(function ($sale) {
                $description = trim((string) ($sale->description ?? ''));
                if ($description === '') {
                    return false;
                }

                return mb_stripos($description, 'تضليل') !== false || mb_stripos($description, 'تظليل') !== false;
            });

            $cashFromSales = $shiftSaleOperations->sum(function ($sale) {
                $cash = (float) ($sale->cash_paid ?? 0);
                if ($cash > 0) {
                    return $cash;
                }

                if (($sale->sale_type ?? null) === 'cash') {
                    return (float) ($sale->paid_amount ?? 0);
                }

                if (($sale->sale_type ?? null) === 'mixed') {
                    return (float) ($sale->cash_amount ?? 0);
                }

                return 0;
            });

            $cardFromSales = $shiftSaleOperations->sum(function ($sale) {
                $card = (float) ($sale->card_paid ?? 0);
                if ($card > 0) {
                    return $card;
                }

                if (($sale->sale_type ?? null) === 'card') {
                    return (float) ($sale->paid_amount ?? 0);
                }

                if (($sale->sale_type ?? null) === 'mixed') {
                    return (float) ($sale->card_amount ?? 0);
                }

                return 0;
            });
            $creditCollections = $shiftCollectionOperations->sum(fn($sale) => (float) ($sale->cash_paid ?? 0) + (float) ($sale->card_paid ?? 0));

            $expenses = Expense::where('store_id', $store->id)
                ->where('actor_type', '!=', 'owner_purchase')
                ->whereBetween('created_at', [$window['start'], $window['end']])
                ->sum('amount');

            $withdrawals = Withdrawal::where('store_id', $store->id)
                ->whereBetween('created_at', [$window['start'], $window['end']])
                ->sum('amount');

            $stats = [
                'total' => $shiftSaleOperations->sum(function ($sale) use ($cashFromSales, $cardFromSales) {
                    $splitTotal = (float) ($sale->cash_paid ?? 0) + (float) ($sale->card_paid ?? 0);
                    if ($splitTotal <= 0) {
                        $splitTotal = (float) ($sale->cash_amount ?? 0) + (float) ($sale->card_amount ?? 0);
                    }

                    return max($splitTotal, (float) ($sale->paid_amount ?? 0));
                }),
                'total_cost' => $shiftSaleOperations->sum('total_cost'),
                'total_profit' => $shiftSaleOperations->sum('recognized_profit'),
                'deferred_profit' => $shiftSaleOperations->sum('deferred_profit'),
                'cash_sales' => $cashFromSales,
                'card_sales' => $cardFromSales,
                'credit_collections' => $creditCollections,
                'tadlil_total' => $tadlilOperations->sum(function ($sale) {
                    $operationTotal = (float) ($sale->operation_total ?? $sale->final_total ?? 0);
                    $productsTotal = (float) ($sale->products_total_value ?? 0);
                    return max(0, $operationTotal - $productsTotal);
                }),
                'tadlil_count' => $tadlilOperations->count(),
                'collected_total' => $shiftSales->sum('paid_amount'),
                'expenses' => (float) $expenses,
                'withdrawals' => (float) $withdrawals,
                'outgoing_total' => (float) $expenses + (float) $withdrawals,
                'count' => $shiftSales->count(),
            ];

            return [
                'key' => $window['key'],
                'label' => $window['label'],
                'start' => $window['start'],
                'end' => $window['end'],
                // تمرير ملاحظة إغلاق الشفت للواجهة كما هي (إن وُجدت) لعرضها في ملخص الشفت.
                'notes' => $window['notes'] ?? null,
                'stats' => $stats,
            ];
        });

        // الإحصائيات العامة عبر كل الشفتات ضمن الفترة المختارة
        $stats = [
            'total' => $shiftSummaries->sum(fn($s) => $s['stats']['total']),
            'total_cost' => $shiftSummaries->sum(fn($s) => $s['stats']['total_cost']),
            'total_profit' => $shiftSummaries->sum(fn($s) => $s['stats']['total_profit']),
            'deferred_profit' => $shiftSummaries->sum(fn($s) => $s['stats']['deferred_profit']),
            'cash_sales' => $shiftSummaries->sum(fn($s) => $s['stats']['cash_sales']),
            'card_sales' => $shiftSummaries->sum(fn($s) => $s['stats']['card_sales']),
            'tadlil_total' => $shiftSummaries->sum(fn($s) => $s['stats']['tadlil_total'] ?? 0),
            'tadlil_count' => $shiftSummaries->sum(fn($s) => $s['stats']['tadlil_count'] ?? 0),
            'collected_total' => $shiftSummaries->sum(fn($s) => $s['stats']['collected_total']),
            'expenses' => $shiftSummaries->sum(fn($s) => $s['stats']['expenses']),
            'withdrawals' => $shiftSummaries->sum(fn($s) => $s['stats']['withdrawals']),
            'outgoing_total' => $shiftSummaries->sum(fn($s) => $s['stats']['outgoing_total']),
            'count' => $sales->count(),
            'products_count' => $sales->filter(fn($sale) => ($sale->operation_kind ?? null) !== 'collection' && $sale->items->isNotEmpty())->count(),
            'labor_count' => $sales->filter(fn($sale) => ($sale->operation_kind ?? null) !== 'collection' && $sale->items->isEmpty())->count(),
            'shift_count' => $shiftSummaries->count(),
        ];

        $sales = $this->paginateSalesCollection($sales, $request);

        return view('user.stores.daily', compact('store', 'sales', 'stats', 'startTime', 'endTime', 'selectedShift', 'shiftSummaries', 'employees'));
    }

    private function paginateSalesCollection($sales, Request $request): LengthAwarePaginator
    {
        $perPage = 25;
        $total = $sales->count();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = min(max((int) $request->query('page', 1), 1), $lastPage);

        return new LengthAwarePaginator(
            $sales->slice(($page - 1) * $perPage, $perPage)->values(),
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function getCreditCollectionOperations(int $storeId, $shiftWindows, array $visibleSaleIds = [])
    {
        $startTime = $shiftWindows->first()['start'] ?? now()->startOfDay();
        $endTime = $shiftWindows->last()['end'] ?? now();

        $collections = DB::table('employee_credit_sales')
            ->leftJoin('employees', 'employee_credit_sales.person_id', '=', 'employees.id')
            ->where('employee_credit_sales.store_id', $storeId)
            ->whereNull('employee_credit_sales.deleted_at')
            ->where('employee_credit_sales.created_at', '<=', $endTime)
            ->whereColumn('employee_credit_sales.remaining_amount', '<', 'employee_credit_sales.amount')
            ->select(
                'employee_credit_sales.id',
                'employee_credit_sales.amount',
                'employee_credit_sales.remaining_amount',
                'employee_credit_sales.partial_payments',
                'employee_credit_sales.updated_at',
                'employee_credit_sales.created_at',
                'employee_credit_sales.description',
                'employees.name as employee_name'
            )
            ->get();

        return $collections->flatMap(function ($collection) use ($shiftWindows, $startTime, $endTime, $storeId, $visibleSaleIds) {
            $linkedSaleId = $this->extractLinkedSaleId((string) ($collection->description ?? ''));
            if ($linkedSaleId && in_array($linkedSaleId, $visibleSaleIds, true)) {
                return collect();
            }

            $allPayments = $this->extractCollectionPayments($collection);
            $payments = array_values(array_filter(
                $allPayments,
                fn ($payment) => ($payment['date'] ?? null) >= $startTime && ($payment['date'] ?? null) <= $endTime
            ));

            return collect($payments)->map(function ($payment, $index) use ($collection, $shiftWindows, $storeId) {
                $profitBreakdown = $this->calculateCollectionProfitBreakdown($storeId, $collection, $payment);

                $operation = (object) [
                    'id' => 'collection-' . $collection->id . '-' . $index,
                    'store_id' => null,
                    'items' => collect(),
                    'description' => $collection->description,
                    'internal_notes' => null,
                    'employee_name' => $collection->employee_name ?: 'غير معروف',
                    'employee_id' => null,
                    'operation_kind' => 'collection',
                    'sale_type' => 'collection',
                    'has_partial_credit' => false,
                    'paid_amount' => (float) ($payment['amount'] ?? 0),
                    'remaining_amount' => 0,
                    'cash_amount' => (float) ($payment['amount'] ?? 0),
                    'card_amount' => 0,
                    'cash_paid' => (float) ($payment['amount'] ?? 0),
                    'card_paid' => 0,
                    'labor_total' => 0,
                    'total' => (float) ($payment['amount'] ?? 0),
                    'final_total' => (float) ($payment['amount'] ?? 0),
                    'operation_total' => (float) ($payment['amount'] ?? 0),
                    'total_cost' => 0,
                    'products_profit' => 0,
                    'total_profit' => (float) ($profitBreakdown['recognized_profit'] ?? 0),
                    'recognized_profit' => (float) ($profitBreakdown['recognized_profit'] ?? 0),
                    'deferred_profit' => (float) ($profitBreakdown['deferred_profit_remaining'] ?? 0),
                    'profit_is_deferred' => (bool) ($profitBreakdown['has_deferred_profit'] ?? false),
                    'payment_label' => 'تحصيل',
                    'employee' => null,
                    'accountant' => null,
                    'created_at' => Carbon::parse($payment['date']),
                    'updated_at' => Carbon::parse($payment['date']),
                    'display_time' => Carbon::parse($payment['date']),
                ];

                $operation->shift_key = $this->resolveShiftKey($operation->created_at, $shiftWindows);

                return $operation;
            });
        })->values();
    }

    private function extractCollectionPayments($collection): array
    {
        $payments = [];
        $partialPayments = $collection->partial_payments;

        if (is_string($partialPayments)) {
            $partialPayments = json_decode($partialPayments, true);
        }

        if (is_array($partialPayments) && !empty($partialPayments)) {
            foreach ($partialPayments as $payment) {
                $paymentDate = isset($payment['date']) ? Carbon::parse($payment['date']) : null;
                if (!$paymentDate) {
                    continue;
                }

                $payments[] = [
                    'amount' => (float) ($payment['amount'] ?? 0),
                    'date' => $paymentDate,
                ];
            }
        }

        if (empty($payments)) {
            $updatedAt = Carbon::parse($collection->updated_at);
            $payments[] = [
                'amount' => (float) $collection->amount - (float) $collection->remaining_amount,
                'date' => $updatedAt,
            ];
        }

        usort($payments, fn ($a, $b) => Carbon::parse($a['date'])->getTimestamp() <=> Carbon::parse($b['date'])->getTimestamp());

        return array_values(array_filter($payments, fn ($payment) => ($payment['amount'] ?? 0) > 0));
    }

    private function calculateCollectionProfitBreakdown(int $storeId, $collection, array $targetPayment): array
    {
        $linkedSaleId = $this->extractLinkedSaleId((string) ($collection->description ?? ''));
        if (!$linkedSaleId) {
            return [
                'cost_component' => 0,
                'recognized_profit' => 0,
                'deferred_profit_remaining' => 0,
                'has_deferred_profit' => false,
            ];
        }

        $sale = $this->getProcessedSaleById($storeId, $linkedSaleId);
        if (!$sale) {
            return [
                'cost_component' => 0,
                'recognized_profit' => 0,
                'deferred_profit_remaining' => 0,
                'has_deferred_profit' => false,
            ];
        }

        $allPayments = $this->extractCollectionPayments($collection);
        $baseCollected = max(0, (float) (($sale->cash_amount ?? 0) + ($sale->card_amount ?? 0)));
        $totalCost = max(0, (float) ($sale->total_cost ?? 0));
        $finalProfit = max(0, (float) ($sale->operation_total ?? 0) - $totalCost);

        $collectedBefore = $baseCollected;
        foreach ($allPayments as $payment) {
            $samePayment = (float) ($payment['amount'] ?? 0) === (float) ($targetPayment['amount'] ?? 0)
                && Carbon::parse($payment['date'])->equalTo(Carbon::parse($targetPayment['date']));

            if ($samePayment) {
                break;
            }

            $collectedBefore += (float) ($payment['amount'] ?? 0);
        }

        $collectedAfter = $collectedBefore + (float) ($targetPayment['amount'] ?? 0);

        $coveredCostBefore = min($totalCost, $collectedBefore);
        $coveredCostAfter = min($totalCost, $collectedAfter);
        $costComponent = max(0, $coveredCostAfter - $coveredCostBefore);

        $recognizedProfitBefore = max(0, $collectedBefore - $totalCost);
        $recognizedProfitAfter = max(0, $collectedAfter - $totalCost);
        $recognizedProfit = max(0, min($finalProfit, $recognizedProfitAfter) - min($finalProfit, $recognizedProfitBefore));
        $deferredProfitRemaining = max(0, $finalProfit - min($finalProfit, $recognizedProfitAfter));

        return [
            'cost_component' => $costComponent,
            'recognized_profit' => $recognizedProfit,
            'deferred_profit_remaining' => $deferredProfitRemaining,
            'has_deferred_profit' => $deferredProfitRemaining > 0,
        ];
    }

    private function extractLinkedSaleId(string $description): ?int
    {
        if (preg_match('/#(\d+)/', $description, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    private function getProcessedSaleById(int $storeId, int $saleId)
    {
        if (array_key_exists($saleId, $this->processedSalesCache)) {
            return $this->processedSalesCache[$saleId];
        }

        $rows = Sale::where('sales.store_id', $storeId)
            ->where('sales.id', $saleId)
            ->where(function ($q) {
                $q->whereNull('sales.description')
                    ->orWhere('sales.description', '!=', 'manual_invoice_entry');
            })
            ->leftJoin('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'sales.*',
                'sale_items.id as item_id',
                'sale_items.product_id',
                'sale_items.quantity as item_quantity',
                'sale_items.price as item_price',
                'sale_items.total as item_total',
                'sale_items.is_custom',
                'sale_items.custom_name',
                'sale_items.custom_consumption',
                'sale_items.custom_meters',
                'products.name as product_name',
                'products.cost_price as product_cost_price',
                'products.product_type as product_type',
                'products.is_splittable as product_is_splittable',
                'products.items_per_unit as product_items_per_unit',
                'products.roll_length as product_roll_length'
            )
            ->orderBy('sale_items.id')
            ->get();

        if ($rows->isEmpty()) {
            return $this->processedSalesCache[$saleId] = null;
        }

        $base = clone $rows->first();
        $base->items = collect();
        $base->total_cost = 0;
        $base->total_profit = 0;
        $base->products_total_value = 0;

        foreach ($rows as $row) {
            if (!$row->item_id) {
                continue;
            }

            $base->items->push((object) [
                'id' => $row->item_id,
                'product_id' => $row->product_id,
                'quantity' => $row->item_quantity,
                'price' => $row->item_price,
                'total' => $row->item_total,
                'is_custom' => $row->is_custom,
                'custom_name' => $row->custom_name,
                'custom_consumption' => $row->custom_consumption,
                'custom_meters' => $row->custom_meters,
                'product_name' => $row->product_name ?? 'منتج غير معروف',
                'cost_price' => $row->product_cost_price ?? 0,
                'product_type' => $row->product_type,
                'is_splittable' => (bool) ($row->product_is_splittable ?? false),
                'items_per_unit' => (float) ($row->product_items_per_unit ?? 0),
                'roll_length' => (float) ($row->product_roll_length ?? 0),
            ]);
        }

        return $this->processedSalesCache[$saleId] = $this->processSale($base);
    }

    private function buildShiftWindows(int $storeId, Carbon $selectedDate)
    {
        $dayStart = $selectedDate->copy()->startOfDay();
        $dayEnd = $selectedDate->copy()->endOfDay();

        // الأساس: نعتمد على الشفتات المغلقة فقط (DailyBalance).
        // نعتمد تاريخ الإغلاق (end_time) كمرجع اليوم حتى لا تظهر شفتات قديمة متداخلة زمنيًا بعد النقل.
        $balances = DailyBalance::where('store_id', $storeId)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->whereDate('created_at', $selectedDate->toDateString())
            ->orderBy('start_time')
            ->get();

        $windows = $balances->values()->map(function ($balance, $index) {
            return [
                'key' => 'shift_' . $balance->id,
                'label' => 'شفت #' . ($index + 1),
                'start' => Carbon::parse($balance->start_time),
                'end' => Carbon::parse($balance->end_time),
                'source' => 'balance',
                // تطبيع الملاحظة: النص الفارغ يتحول إلى null حتى لا يظهر بلوك فارغ في الواجهة.
                'notes' => trim((string) ($balance->notes ?? '')) !== '' ? (string) $balance->notes : null,
            ];
        })->values();

        // إذا التاريخ اليوم: أضف الشفت المفتوح الحالي حتى تظهر عملياته قبل الإغلاق.
        if ($selectedDate->isToday()) {
            $lastClosed = DailyBalance::where('store_id', $storeId)
                ->whereNotNull('end_time')
                ->latest('end_time')
                ->first();

            $openStart = $lastClosed ? Carbon::parse($lastClosed->end_time) : $selectedDate->copy()->startOfDay();
            $openEnd = now();

            if ($openStart->lt($openEnd)) {
                $windows->push([
                    'key' => 'current_open_shift',
                    'label' => 'الشفت الحالي (غير مغلق)',
                    'start' => $openStart,
                    'end' => $openEnd,
                    'source' => 'open_shift',
                    // الشفت المفتوح لا يملك ملاحظة إغلاق بعد؛ تُترك null عمدًا.
                    'notes' => null,
                ]);
            }
        }

        // مهم: إذا لا يوجد شفت مغلق/مفتوح لليوم المختار، fallback يتم في index إلى اليوم التقويمي.
        return $windows->sortBy('start')->values();
    }

    private function applyPeriodFilter($query, $windows): void
    {
        $query->where(function ($q) use ($windows) {
            foreach ($windows as $window) {
                $q->orWhereBetween('sales.created_at', [$window['start'], $window['end']]);
            }
        });
    }

    private function resolveShiftKey($createdAt, $windows): string
    {
        $createdAt = Carbon::parse($createdAt);

        foreach ($windows as $window) {
            if ($createdAt->betweenIncluded($window['start'], $window['end'])) {
                return $window['key'];
            }
        }

        return 'default_shift';
    }

    public function update(Store $store, Sale $sale, Request $request)
    {
        if ($sale->store_id !== $store->id) {
            abort(403, 'هذه العملية لا تنتمي لهذا المتجر');
        }

        $validated = $request->validate([
            'sale_type'   => 'required|in:cash,card,credit,mixed',
            'paid_amount' => 'required|numeric|min:0',
            'labor_total' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'cash_amount' => 'nullable|numeric|min:0',
            'card_amount' => 'nullable|numeric|min:0',
            'employee_id' => 'nullable|exists:employees,id',
            'debt_amount' => 'nullable|numeric|min:0',
        ]);

        $originalSaleType = $sale->sale_type;
        $productsTotal = (float) ($sale->products_total ?? 0);
        $taxRate = (float) ($sale->tax_rate ?? 0);
        $laborTotal = (float) ($validated['labor_total'] ?? 0);

        $taxAmount = $productsTotal * ($taxRate / 100);
        $finalTotal = $productsTotal + $taxAmount + $laborTotal;

        $enteredAmount = (float) $validated['paid_amount'];
        $enteredDebtAmount = (float) ($validated['debt_amount'] ?? 0);
        $selectedEmployeeId = $validated['employee_id'] ?? $sale->employee_id;
        $paidAmount = $enteredAmount;
        $cashAmount = 0.0;
        $cardAmount = 0.0;
        $storedOperationAmount = (float) (($sale->paid_amount ?? 0) + ($sale->remaining_amount ?? 0));
        $baseOperationAmount = max($finalTotal, $storedOperationAmount);
        $hasCollectedCreditConversion = $originalSaleType === 'credit'
            && (float) ($sale->paid_amount ?? 0) > 0
            && (float) ($sale->remaining_amount ?? 0) > 0
            && $validated['sale_type'] !== 'credit';
        $alreadyCollectedAmount = $hasCollectedCreditConversion ? (float) ($sale->paid_amount ?? 0) : 0.0;
        $editableOperationAmount = $hasCollectedCreditConversion ? (float) ($sale->remaining_amount ?? 0) : $baseOperationAmount;
        $baseCashAmount = $hasCollectedCreditConversion ? $alreadyCollectedAmount : 0.0;

        if (!empty($validated['employee_id'])) {
            $employeeBelongsToStore = $store->employees()->where('id', $validated['employee_id'])->exists();
            if (!$employeeBelongsToStore) {
                return back()->withErrors(['employee_id' => 'الموظف المختار لا يتبع هذا المتجر.'])->withInput()->with('edit_sale_modal', $sale->id);
            }
        }

        if ($validated['sale_type'] === 'cash') {
            $cashEditableAmount = $hasCollectedCreditConversion ? $editableOperationAmount : $enteredAmount;
            $paidAmount = $alreadyCollectedAmount + $cashEditableAmount;
            $remainingAmount = 0;
            $cashAmount = $baseCashAmount + $cashEditableAmount;
        } elseif ($validated['sale_type'] === 'card') {
            $cardEditableAmount = $hasCollectedCreditConversion ? $editableOperationAmount : $enteredAmount;
            $paidAmount = $alreadyCollectedAmount + $cardEditableAmount;
            $remainingAmount = 0;
            $cashAmount = $baseCashAmount;
            $cardAmount = $cardEditableAmount;
        } elseif ($validated['sale_type'] === 'mixed') {
            $hasCashInput = $request->filled('cash_amount');
            $hasCardInput = $request->filled('card_amount');
            $hasDebtInput = $request->filled('debt_amount');
            $isCreditToMixedConversion = $originalSaleType === 'credit';
            $debtAmount = max(0, $enteredDebtAmount);

            if ($isCreditToMixedConversion && !($hasCashInput || $hasCardInput)) {
                return back()->withErrors([
                    'sale_type' => 'عند التحويل من آجل إلى ميكس يجب إدخال توزيع الكاش/الشبكة صراحة.'
                ])->withInput()->with('edit_sale_modal', $sale->id);
            }

            if ($hasCashInput || $hasCardInput) {
                $newCashAmount = (float) ($validated['cash_amount'] ?? 0);
                $newCardAmount = (float) ($validated['card_amount'] ?? 0);
                $cashAmount = $baseCashAmount + $newCashAmount;
                $cardAmount = $newCardAmount;
                $paidAmount = $alreadyCollectedAmount + $newCashAmount + $newCardAmount;

                if (($newCashAmount + $newCardAmount) <= 0) {
                    return back()->withErrors([
                        'paid_amount' => 'في عملية الميكس يجب أن يكون مجموع الكاش والشبكة أكبر من صفر.'
                    ])->withInput()->with('edit_sale_modal', $sale->id);
                }
            } else {
                // fallback محافظ للحالات غير الآجلة القديمة فقط
                $cashAmount = $baseCashAmount + $paidAmount;
                $cardAmount = 0;
            }

            if ($debtAmount < 0 || $debtAmount > $editableOperationAmount) {
                return back()->withErrors(['debt_amount' => 'قيمة المديونية يجب أن تكون بين صفر وقيمة العملية الأساسية.'])->withInput()->with('edit_sale_modal', $sale->id);
            }

            $enteredMixedTotal = max(0, $cashAmount - $baseCashAmount) + max(0, $cardAmount);

            if (!$hasDebtInput && abs($enteredMixedTotal - $editableOperationAmount) > 0.01) {
                return back()->withErrors([
                    'debt_amount' => 'عند تعديل العملية إلى ميكس يجب أن يساوي (كاش + شبكة) قيمة العملية، أو يتم إدخال المديونية صراحة.'
                ])->withInput()->with('edit_sale_modal', $sale->id);
            }

            $remainingAmount = $debtAmount > 0 ? $debtAmount : ($editableOperationAmount - $enteredMixedTotal);

            if (abs(($enteredMixedTotal + $remainingAmount) - $editableOperationAmount) > 0.01) {
                return back()->withErrors([
                    'debt_amount' => 'في الميكس يجب أن يساوي (كاش + شبكة + مديونية) قيمة العملية الأساسية.'
                ])->withInput()->with('edit_sale_modal', $sale->id);
            }
        } else {
            $remainingAmount = max($enteredDebtAmount, $baseOperationAmount);

            if (abs($remainingAmount - $baseOperationAmount) > 0.01) {
                return back()->withErrors([
                    'debt_amount' => 'في الآجل الكامل يجب أن تساوي قيمة المديونية كامل العملية. إذا أردت آجلًا جزئيًا استخدم ميكس.'
                ])->withInput()->with('edit_sale_modal', $sale->id);
            }

            $paidAmount = 0;
            $remainingAmount = $baseOperationAmount;
        }

        $hasPartialCredit = in_array($validated['sale_type'], ['credit', 'mixed'], true) && $remainingAmount > 0;
        $creditDescriptionSuffix = $validated['sale_type'] === 'credit' ? '' : ' (آجل جزئي)';

        if (($validated['sale_type'] === 'credit' || $remainingAmount > 0) && !$selectedEmployeeId) {
            return back()->withErrors(['employee_id' => 'يجب اختيار الموظف الذي ستضاف عليه المديونية.'])->withInput()->with('edit_sale_modal', $sale->id);
        }

        if ($hasPartialCredit) {
            $hasExistingCredit = CreditSale::where('store_id', $store->id)
                ->where('description', 'like', '%#' . $sale->id . '%')
                ->exists();

            if (!$selectedEmployeeId && !$hasExistingCredit) {
                return back()->withErrors(['sale_type' => 'لا يمكن التحويل إلى ميكس/آجل جزئي بدون موظف مرتبط بهذه العملية.'])->withInput()->with('edit_sale_modal', $sale->id);
            }
        }

        DB::transaction(function () use ($sale, $store, $validated, $laborTotal, $finalTotal, $paidAmount, $remainingAmount, $cashAmount, $cardAmount, $hasPartialCredit, $selectedEmployeeId, $creditDescriptionSuffix) {
            $sale->update([
                'sale_type'          => $validated['sale_type'],
                'labor_total'        => $laborTotal,
                'description'        => $validated['description'] ?? null,
                'final_total'        => $finalTotal,
                'paid_amount'        => $paidAmount,
                'remaining_amount'   => $remainingAmount,
                'cash_amount'        => $cashAmount,
                'card_amount'        => $cardAmount,
                'has_partial_credit' => $hasPartialCredit,
                'employee_id'        => ($validated['sale_type'] === 'credit' || $remainingAmount > 0)
                    ? $selectedEmployeeId
                    : null,
            ]);

            $creditRows = CreditSale::where('store_id', $store->id)
                ->where('description', 'like', '%#' . $sale->id . '%')
                ->orderBy('id')
                ->get();

            if ($hasPartialCredit) {
                $personId = $sale->employee_id ?: optional($creditRows->first())->person_id;

                if (!$personId) {
                    // حارس إضافي، من المفترض تم التحقق منه قبل المعاملة
                    return;
                }

                if ($creditRows->isNotEmpty()) {
                    $first = $creditRows->first();
                    $alreadyCollectedOnCredit = max(
                        0,
                        (float) ($first->amount ?? 0) - (float) ($first->remaining_amount ?? 0)
                    );
                    $updatedCreditAmount = $alreadyCollectedOnCredit + $remainingAmount;

                    $first->update([
                        'person_id' => $personId,
                        'person_type' => \App\Models\Employee::class,
                        'amount' => $updatedCreditAmount,
                        'remaining_amount' => $remainingAmount,
                        'description' => 'مديونية من فاتورة رقم #' . $sale->id . $creditDescriptionSuffix,
                        'date' => now()->format('Y-m-d'),
                        'status' => 'pending',
                        'month' => now()->format('m-Y'),
                        'added_by' => $sale->accountant_id,
                    ]);

                    if ($creditRows->count() > 1) {
                        CreditSale::whereIn('id', $creditRows->slice(1)->pluck('id'))->delete();
                    }
                } else {
                    CreditSale::create([
                        'person_id' => $personId,
                        'person_type' => \App\Models\Employee::class,
                        'store_id' => $store->id,
                        'amount' => $remainingAmount,
                        'remaining_amount' => $remainingAmount,
                        'description' => 'مديونية من فاتورة رقم #' . $sale->id . $creditDescriptionSuffix,
                        'date' => now()->format('Y-m-d'),
                        'status' => 'pending',
                        'month' => now()->format('m-Y'),
                        'added_by' => $sale->accountant_id,
                    ]);
                }
            } else {
                if ($creditRows->isNotEmpty()) {
                    CreditSale::whereIn('id', $creditRows->pluck('id'))->delete();
                }
            }
        });

        return back()->with('success', 'تم تعديل العملية بنجاح.');
    }

    public function updateShiftDate(Store $store, Request $request)
    {
        $validated = $request->validate([
            'shift_key' => 'required|string',
            'shift_date' => 'required|date',
        ]);

        if (!preg_match('/^shift_(\d+)$/', $validated['shift_key'], $matches)) {
            return back()->withErrors(['shift_date' => 'لا يمكن تعديل تاريخ هذا الشفت.'])->withInput();
        }

        $balanceId = (int) ($matches[1] ?? 0);
        $balance = DailyBalance::where('store_id', $store->id)->find($balanceId);

        if (!$balance || !$balance->start_time || !$balance->end_time) {
            return back()->withErrors(['shift_date' => 'الشفت غير صالح أو غير مغلق.'])->withInput();
        }

        $targetDate = Carbon::parse($validated['shift_date'])->startOfDay();
        $startTime = Carbon::parse($balance->start_time);
        $endTime = Carbon::parse($balance->end_time);

        $newStartTime = $targetDate->copy()->startOfDay();
        $newEndTime = $targetDate->copy()->endOfDay();

        $overlappingShiftExists = DailyBalance::where('store_id', $store->id)
            ->where('id', '!=', $balance->id)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->where(function ($q) use ($newStartTime, $newEndTime) {
                $q->whereBetween('start_time', [$newStartTime, $newEndTime])
                    ->orWhereBetween('end_time', [$newStartTime, $newEndTime])
                    ->orWhere(function ($q2) use ($newStartTime, $newEndTime) {
                        $q2->where('start_time', '<', $newStartTime)
                            ->where('end_time', '>', $newEndTime);
                    });
            })
            ->exists();

        if ($overlappingShiftExists) {
            return back()->withErrors(['shift_date' => 'لا يمكن نقل الشفت لهذا التاريخ لأنه سيتداخل مع شفت مغلق آخر.'])->withInput();
        }

        DB::transaction(function () use ($store, $startTime, $endTime, $targetDate, $balance) {
            $newShiftStart = null;
            $newShiftEnd = null;

            $moveByDayShift = function ($query, callable $afterSet = null) use ($targetDate, &$newShiftStart, &$newShiftEnd) {
                $query->orderBy('id')->get()->each(function ($row) use ($targetDate, $afterSet) {
                    $original = Carbon::parse($row->created_at);
                    $row->created_at = $original->copy()->setDateFrom($targetDate);

                    $rowTime = Carbon::parse($row->created_at);
                    $newShiftStart = $newShiftStart ? $newShiftStart->copy()->min($rowTime) : $rowTime->copy();
                    $newShiftEnd = $newShiftEnd ? $newShiftEnd->copy()->max($rowTime) : $rowTime->copy();

                    if ($afterSet) {
                        $afterSet($row);
                    }
                    $row->save();
                });
            };

            $moveByDayShift(
                Sale::where('store_id', $store->id)
                    ->whereBetween('created_at', [$startTime, $endTime])
            );

            $moveByDayShift(
                \App\Models\Expense::where('store_id', $store->id)
                    ->whereBetween('created_at', [$startTime, $endTime])
            );

            $moveByDayShift(
                \App\Models\Purchase::where('store_id', $store->id)
                    ->whereBetween('created_at', [$startTime, $endTime])
            );

            $moveByDayShift(
                \App\Models\Withdrawal::where('store_id', $store->id)
                    ->whereBetween('created_at', [$startTime, $endTime]),
                function ($row) {
                    if (isset($row->date)) {
                        $row->date = Carbon::parse($row->created_at)->toDateString();
                    }
                }
            );

            $balance->start_time = $newShiftStart ? $newShiftStart->copy() : $targetDate->copy()->startOfDay();
            $balance->end_time = $newShiftEnd ? $newShiftEnd->copy() : $targetDate->copy()->endOfDay();
            // مهم: حتى لا يظهر الشفت في يوم إغلاقه القديم بقيم صفر بعد النقل.
            $balance->created_at = Carbon::parse($balance->created_at)->setDateFrom($targetDate);
            $balance->updated_at = now();
            $balance->save();
        });

        return back()->with('success', 'تم تعديل تاريخ الشفت والعمليات المرتبطة به بنجاح.');
    }

    public function destroy(Store $store, Sale $sale)
    {
        if ($sale->store_id !== $store->id) {
            abort(403, 'هذه العملية لا تنتمي لهذا المتجر');
        }

        DB::transaction(function () use ($sale, $store) {
            $sale->loadMissing(['items.product', 'invoice']);

            foreach ($sale->items as $item) {
                if (!$item->product || $item->product->store_id !== $store->id) {
                    continue;
                }

                $restoreQty = (float) ($item->custom_consumption ?? $item->quantity ?? 0);
                if ($restoreQty <= 0) {
                    continue;
                }

                $item->product->increment('quantity', $restoreQty);

                $item->product->stockMovements()->create([
                    'store_id' => $store->id,
                    'user_id' => auth()->id(),
                    'product_id' => $item->product->id,
                    'type' => 'increase',
                    'quantity' => $restoreQty,
                    'note' => 'استرجاع مخزون بعد حذف عملية مبيعات #' . $sale->id,
                ]);
            }

            // حذف الفاتورة المرتبطة إن وجدت
            if ($sale->invoice) {
                $sale->invoice->delete();
            }

            // حذف المديونية المرتبطة بهذه الفاتورة نهائياً، بما فيها أي سجلات soft-deleted أو تحصيلات جزئية مخزنة داخلها
            CreditSale::withTrashed()
                ->where('store_id', $store->id)
                ->where('description', 'like', '%#' . $sale->id . '%')
                ->forceDelete();

            $sale->items()->delete();
            $sale->delete();
        });

        return back()->with('success', 'تم حذف العملية واسترجاع المخزون بنجاح.');
    }

    /**
     * معالجة عملية بيع وحساب التكاليف والأرباح بشكل صحيح
     */
    private function processSale($sale)
    {
        $totalCost = 0;
        $productsProfit = 0;

        foreach ($sale->items as $item) {
            // اسم المنتج
            if ($item->is_custom && $item->custom_name) {
                $item->display_name = $item->custom_name;
            } else {
                $item->display_name = $item->product_name ?? 'منتج غير معروف';
            }

            // الكمية الأساسية المحسوبة للمخزون
            $stockQuantity = (float) ($item->custom_consumption ?? $item->quantity ?? 0);

            // الكمية/الوحدة المعروضة للمستخدم
            $displayQuantity = (float) ($item->quantity ?? 0);
            $displayUnit = 'وحدة';

            if (!empty($item->custom_meters)) {
                $displayQuantity = (float) $item->custom_meters;
                $displayUnit = 'متر';
            } elseif (($item->product_type ?? null) === 'fractional') {
                $displayUnit = ((float) ($item->roll_length ?? 0) > 0) ? 'رول' : 'متر';
            } elseif (!empty($item->is_splittable)) {
                $itemsPerUnit = (float) ($item->items_per_unit ?? 0);
                $displayUnit = ($itemsPerUnit > 1 && abs($displayQuantity - $stockQuantity) > 0.0001) ? 'حبة' : 'طقم';
            }

            // إجمالي المنتج
            $itemTotal = $item->total ?? ($item->price * $item->quantity);

            // تكلفة المنتج
            $itemCost = $item->cost_price * $stockQuantity;

            // ربح المنتج
            $itemProfit = $itemTotal - $itemCost;

            // تخزين القيم المحسوبة
            $item->calculated_cost = $itemCost;
            $item->calculated_profit = $itemProfit;
            $item->display_quantity = $displayQuantity;
            $item->display_unit = $displayUnit;

            $totalCost += $itemCost;
            $productsProfit += $itemProfit;
        }

        // ✅ حساب الربح بناءً على القيمة الأساسية الفعلية للعملية
        $operationTotal = max(
            (float) ($sale->final_total ?? 0),
            (float) (($sale->paid_amount ?? 0) + ($sale->remaining_amount ?? 0))
        );

        $hasOutstandingCredit = ((float) ($sale->remaining_amount ?? 0)) > 0
            && ($sale->sale_type === 'credit' || (int) ($sale->has_partial_credit ?? 0) === 1 || $sale->sale_type === 'mixed');

        $sale->total_cost = $totalCost;
        $sale->products_profit = $productsProfit;
        $sale->operation_total = $operationTotal;
        $sale->total_profit = $operationTotal - $totalCost;
        $sale->recognized_profit = $hasOutstandingCredit ? 0 : ((float) ($sale->paid_amount ?? 0) - $totalCost);
        $sale->deferred_profit = $hasOutstandingCredit ? ($operationTotal - $totalCost) : 0;
        $sale->profit_is_deferred = $hasOutstandingCredit;
        $sale->shift_key = 'default_shift';
        $sale->cash_paid = (float) ($sale->cash_amount ?? 0);
        $sale->card_paid = (float) ($sale->card_amount ?? 0);
        $sale->payment_label = match (true) {
            $sale->sale_type === 'mixed' && (float) ($sale->remaining_amount ?? 0) > 0 => 'ميكس + آجل',
            $sale->sale_type === 'mixed' => 'ميكس',
            $sale->sale_type === 'cash' && (float) ($sale->remaining_amount ?? 0) > 0 => 'نقداً + آجل',
            $sale->sale_type === 'card' && (float) ($sale->remaining_amount ?? 0) > 0 => 'بطاقة + آجل',
            $sale->sale_type === 'cash' => 'نقداً',
            $sale->sale_type === 'card' => 'بطاقة',
            $sale->sale_type === 'credit' && (float) ($sale->remaining_amount ?? 0) <= 0 => 'تم التحصيل',
            $sale->sale_type === 'credit' => 'آجل',
            default => 'آجل',
        };

        // ✅ للتأكد: إجمالي المبيعات يجب أن يساوي (المنتجات + شغل اليد + الضريبة)
        // final_total = products_total + labor_total + tax

        return $sale;
    }
}
