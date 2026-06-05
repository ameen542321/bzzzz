<?php

namespace App\Http\Controllers\Accountant;

use Carbon\Carbon;
use App\Models\Log;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Accountant;
use App\Models\Withdrawal;
use App\Models\DailyBalance;
use App\Models\Debt;
use App\Models\Notification;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Support\ArabicPdf as PDF;

class DashboardController extends Controller
{
    public function index()
    {
        $accountant = auth('accountant')->user();
        $storeId = $accountant->store_id;
        $lastBalance = null;

        try {
            // 1. البحث عن آخر إقفال يدوي مسجل
            $lastBalance = DailyBalance::where('store_id', $storeId)
                ->with(['accountant'])
                ->latest()
                ->first();

            if ($lastBalance) {
                $startTime = $lastBalance->end_time ?? $lastBalance->created_at;
                $lastBalanceTime = $lastBalance->end_time
                    ? $lastBalance->end_time->format('Y-m-d h:i A')
                    : $lastBalance->created_at->format('Y-m-d h:i A');
                $lastBalanceAmount = $lastBalance->system_sales_total;
                $lastBalanceAccountant = optional($lastBalance->accountant)->name ?? 'غير معروف';
            } else {
                $startTime = Carbon::parse('2024-01-01');
                $lastBalanceTime = 'بانتظار أول إقفال';
                $lastBalanceAmount = 0;
                $lastBalanceAccountant = '--';
            }

            $shiftDuration = $startTime->diffInHours(now());
            $shiftDurationText = $this->formatShiftDuration($shiftDuration);

            // تحسين: استعلام واحد لجمع إحصائيات المبيعات
            $salesStats = $this->getSalesStatistics($storeId, $startTime);

            $totalSinceBalance = $salesStats['total_sales'];
            $cashSales = $salesStats['cash_sales'];
            $cardSales = $salesStats['card_sales'];
            $officialCreditSales = $salesStats['official_credit_sales'];
            $paymentGaps = $salesStats['payment_gaps'];
            $pendingCreditTotal = $officialCreditSales + $paymentGaps;

            $currentShiftExpenses = Expense::where('store_id', $storeId)
                ->where('created_at', '>', $startTime)
                ->sum('amount');

            $currentShiftWithdrawals = Withdrawal::where('store_id', $storeId)
                ->where('created_at', '>', $startTime)
                ->sum('amount');

            $cashFromCollectionsResult = $this->getCreditCollections($storeId, $startTime, now());
            $cashFromCollections = $cashFromCollectionsResult['total'] ?? 0;
            $collectedFromCurrentPeriod = $cashFromCollectionsResult['from_current_period'] ?? 0;
            $collectedFromOldPeriod = $cashFromCollectionsResult['from_old_period'] ?? 0;

            // ✅ التصحيح: جميع التحصيلات تضاف للكاش
            $cashInSafe = ($cashSales + $cashFromCollections) - ($currentShiftExpenses + $currentShiftWithdrawals);
            $totalCashInShift = $cashSales + $cashFromCollections;

            // إحصائيات الشهر
            $startOfMonth = now()->startOfMonth();
            $stats = [
                'monthly_withdrawals' => Withdrawal::where('store_id', $storeId)
                    ->where('created_at', '>=', $startOfMonth)
                    ->sum('amount'),
                'monthly_expenses' => Expense::where('store_id', $storeId)
                    ->where('created_at', '>=', $startOfMonth)
                    ->sum('amount'),
            ];

            $workingDays = DailyBalance::where('store_id', $storeId)
                ->whereMonth('created_at', now()->month)
                ->count();
            $dailyAverage = $workingDays > 0 ? ($totalSinceBalance / $workingDays) : 0;

            $lowStockProducts = \App\Models\Product::where('store_id', $storeId)
                ->whereColumn('quantity', '<=', 'min_stock')
                ->take(5)
                ->get();
            $lowStockProductsCount = $lowStockProducts->count();

            $pendingCollections = DB::table('employee_credit_sales')
                ->where('store_id', $storeId)
                ->where('remaining_amount', '>', 0)
                ->where('status', 'pending')
                ->count();

            $lastOperations = $this->getLastOperations($storeId);

            $shiftStatus = ($shiftDuration > 15) ? 'warning' : 'normal';
            $shiftStatusClass = ($shiftDuration > 15) ? 'warning' : 'success';
            $shiftStatusMessage = ($shiftDuration > 15)
                ? 'لم يتم إغلاق الحسابات منذ فترة طويلة'
                : '';

            $salesEfficiency = $lastBalanceAmount > 0
                ? (($totalSinceBalance - $lastBalanceAmount) / $lastBalanceAmount) * 100
                : 0;

            $quickStats = $this->getDashboardQuickStats($storeId, $startTime);

            $pendingCreditCount = Sale::where('store_id', $storeId)
                ->where('created_at', '>', $startTime)
                ->where('remaining_amount', '>', 0)
                ->count();

            // تحصيلات البيع الآجل
            $creditCollections = $this->getCreditCollections($storeId, $startTime, now());
            $shiftOperationDetails = $this->getShiftOperationDetails($storeId, $startTime, $creditCollections);

            // تنظيف المتغيرات الكبيرة بعد استخدامها
            unset($salesStats);

            return view('dashboard.accountant.index', compact(
                'totalSinceBalance', 'currentShiftExpenses', 'currentShiftWithdrawals', 'stats',
                'lastOperations', 'startTime', 'lastBalanceTime', 'lastBalanceAmount', 'lastBalanceAccountant',
                'shiftDuration', 'shiftDurationText', 'workingDays', 'dailyAverage', 'cashInSafe',
                'lowStockProducts', 'lowStockProductsCount', 'pendingCreditTotal', 'officialCreditSales',
                'paymentGaps', 'pendingCollections', 'shiftStatus', 'shiftStatusClass',
                'shiftStatusMessage', 'salesEfficiency', 'cashFromCollections', 'cashSales',
                'cardSales', 'totalCashInShift', 'quickStats', 'pendingCreditCount', 'lastBalance', 'accountant',
                'creditCollections', 'collectedFromCurrentPeriod', 'collectedFromOldPeriod',
                'shiftOperationDetails'
            ));

        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return redirect()->back()->with('error', 'حدث خطأ في تحميل البيانات');
        }
    }

    private function getSalesStatistics($storeId, $startTime)
    {
        return Sale::where('store_id', $storeId)
            ->where('created_at', '>', $startTime)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->selectRaw('
                COALESCE(SUM(final_total), 0) as total_sales,

                -- المبالغ النقدية: من مبيعات كاش + الجزء النقدي من المختلط
                COALESCE(SUM(CASE WHEN sale_type = "cash" THEN paid_amount ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN sale_type = "mixed" THEN cash_amount ELSE 0 END), 0) as cash_sales,

                -- مبالغ الشبكة: من مبيعات شبكة + الجزء الشبكي من المختلط
                COALESCE(SUM(CASE WHEN sale_type = "card" THEN paid_amount ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN sale_type = "mixed" THEN card_amount ELSE 0 END), 0) as card_sales,

                -- مدفوعات الآجل (ما تم دفعه من أصل آجل)
                COALESCE(SUM(CASE WHEN sale_type = "credit" THEN paid_amount ELSE 0 END), 0) as credit_payments,

                -- الآجل الرسمي (مع موظف)
                COALESCE(SUM(CASE
                    WHEN (sale_type = "credit" OR has_partial_credit = 1)
                    AND employee_id IS NOT NULL
                    AND remaining_amount > 0
                    THEN remaining_amount
                    ELSE 0
                END), 0) as official_credit_sales,

                -- فروقات الدفع (بدون موظف)
                COALESCE(SUM(CASE
                    WHEN (sale_type = "credit" OR has_partial_credit = 1)
                    AND employee_id IS NULL
                    AND remaining_amount > 0
                    THEN remaining_amount
                    ELSE 0
                END), 0) as payment_gaps
            ')
            ->first()
            ->toArray();
    }

    private function formatShiftDuration($hours)
    {
        if ($hours < 1) {
            $minutes = $hours * 60;
            return round($minutes) . ' دقيقة';
        } elseif ($hours < 24) {
            $hoursInt = floor($hours);
            $minutes = round(($hours - $hoursInt) * 60);

            if ($minutes > 0) {
                return $hoursInt . ' ساعة و ' . $minutes . ' دقيقة';
            }
            return $hoursInt . ' ساعة';
        } else {
            $days = floor($hours / 24);
            $remainingHours = floor($hours % 24);

            if ($remainingHours > 0) {
                return $days . ' يوم و ' . $remainingHours . ' ساعة';
            }
            return $days . ' يوم';
        }
    }

    private function getDashboardQuickStats($storeId, $startTime)
    {
        $topEmployee = Sale::where('store_id', $storeId)
            ->where('created_at', '>', $startTime)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->whereNotNull('employee_id')
            ->select('employee_id', DB::raw('SUM(final_total) as total_sales'))
            ->groupBy('employee_id')
            ->orderBy('total_sales', 'desc')
            ->first();

        return [
            'avg_sale_amount' => Sale::where('store_id', $storeId)
                ->where('created_at', '>', $startTime)
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->avg('final_total') ?? 0,

            'invoice_count' => Sale::where('store_id', $storeId)
                ->where('created_at', '>', $startTime)
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->count(),

            'highest_sale' => Sale::where('store_id', $storeId)
                ->where('created_at', '>', $startTime)
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->max('final_total') ?? 0,

            'top_employee' => $topEmployee ? [
                'employee_id' => $topEmployee->employee_id,
                'total_sales' => $topEmployee->total_sales
            ] : null,
        ];
    }

    private function getLastOperations($storeId)
    {
        try {
            \Log::info('=== getLastOperations START ===');

            // 1. المبيعات - بدون select معقد
            $sales = Sale::where('store_id', $storeId)
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->latest()
                ->take(5)
                ->get()
                ->map(function($model) {
                    return $this->formatOp($model, 'sale');
                });

            // 2. المصروفات - بدون select معقد
            $expenses = Expense::where('store_id', $storeId)
                ->latest()
                ->take(5)
                ->get()
                ->map(function($model) {
                    return $this->formatOp($model, 'expense');
                });

            // 3. السحوبات - بدون select معقد
            $withdrawals = Withdrawal::where('store_id', $storeId)
                ->latest()
                ->take(5)
                ->get()
                ->map(function($model) {
                    return $this->formatOp($model, 'withdrawal');
                });

            \Log::info('=== getLastOperations COMPLETE ===');

            // الدمج والترتيب
            return $sales->concat($expenses)->concat($withdrawals)
                         ->sortByDesc('created_at')
                         ->take(10);

        } catch (\Exception $e) {
            \Log::error('❌ getLastOperations ERROR: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return collect();
        }
    }


    private function getShiftOperationDetails($storeId, $startTime, array $creditCollections = []): array
    {
        $sales = Sale::with(['items.product:id,name'])
            ->where('store_id', $storeId)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->where('created_at', '>', $startTime)
            ->latest()
            ->get()
            ->map(function ($sale) {
                $productName = optional($sale->items->first()?->product)->name
                    ?? ($sale->items->count() > 1 ? 'عدة منتجات' : '-');

                $paymentType = match ($sale->sale_type) {
                    'cash' => 'كاش',
                    'card' => 'شبكة',
                    'mixed' => 'مكس',
                    'credit' => 'آجل',
                    default => 'غير محدد',
                };

                return [
                    'time' => $sale->created_at,
                    'operation_type' => 'بيع',
                    'product' => $productName,
                    'amount' => (float) ($sale->paid_amount ?: $sale->final_total ?: $sale->total ?: 0),
                    'payment_type' => $paymentType,
                    'note' => $sale->description ?: $sale->internal_notes,
                ];
            });

        $collections = collect($creditCollections['details'] ?? [])->map(function ($item) {
            return [
                'time' => Carbon::parse($item['collection_date'] ?? now()),
                'operation_type' => 'تحصيل',
                'product' => $item['employee_name'] ?? '-',
                'amount' => (float) ($item['collected_amount'] ?? 0),
                'payment_type' => 'تحصيل',
                'note' => $item['description'] ?? null,
            ];
        });

        $expenses = Expense::where('store_id', $storeId)
            ->where('created_at', '>', $startTime)
            ->latest()
            ->get()
            ->map(fn ($exp) => [
                'time' => $exp->created_at,
                'operation_type' => 'مصروف',
                'product' => $exp->type ?: '-',
                'amount' => (float) $exp->amount,
                'payment_type' => 'مصروف',
                'note' => $exp->description,
            ]);

        $withdrawals = Withdrawal::where('store_id', $storeId)
            ->where('created_at', '>', $startTime)
            ->latest()
            ->get()
            ->map(fn ($w) => [
                'time' => $w->created_at,
                'operation_type' => 'سحب',
                'product' => $w->description ?: '-',
                'amount' => (float) $w->amount,
                'payment_type' => 'سحب',
                'note' => $w->description,
            ]);

        $debts = Debt::where('store_id', $storeId)
            ->where('created_at', '>', $startTime)
            ->latest()
            ->get()
            ->map(fn ($d) => [
                'time' => $d->created_at,
                'operation_type' => 'مديونية',
                'product' => $d->description ?: '-',
                'amount' => (float) $d->amount,
                'payment_type' => 'مديونية',
                'note' => $d->description,
            ]);

        $rows = $sales
            ->concat($collections)
            ->concat($expenses)
            ->concat($withdrawals)
            ->concat($debts)
            ->sortByDesc('time')
            ->values();

        return [
            'rows' => $rows,
            'count' => $rows->count(),
            'total_in' => (float) $rows->whereIn('operation_type', ['بيع', 'تحصيل'])->sum('amount'),
            'total_out' => (float) $rows->whereIn('operation_type', ['مصروف', 'سحب', 'مديونية'])->sum('amount'),
        ];
    }

    public function viewReport($filename)
    {
        // البحث في المسار الموحد للتخزين
        $path = storage_path('app/public/reports/' . $filename);

        if (!file_exists($path)) {
            \Log::error("التقرير غير موجود في المسار: " . $path);
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"'
        ]);
    }

    private function sendReportToOwner($phone, $fileName)
    {
        // الرابط المباشر للملف (يجب أن يكون موقعك مرفوعاً على سيرفر حقيقي ليعمل)
        $fileUrl = route('report.view', ['filename' => $fileName]);

        // إعدادات API الواتساب (مثال UltraMsg)
        $params = [
            'token' => 'YOUR_ULTRAMSG_TOKEN',
            'to'    => $phone, // رقم المالك
            'filename' => $fileName,
            'document' => $fileUrl,
            'caption'  => "تقرير إقفال المتجر ليوم " . now()->format('Y-m-d')
        ];

        // إرسال الطلب (Curl أو Guzzle)
        $curl = curl_init();
        curl_setopt_array($curl, [
          CURLOPT_URL => "https://api.ultramsg.com/YOUR_INSTANCE_ID/messages/document",
          CURLOPT_POST => true,
          CURLOPT_POSTFIELDS => http_build_query($params),
          CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
    }

    private function getCreditCollections($storeId, $startTime, $endTime)
    {
        try {
            $collections = DB::table('employee_credit_sales')
                ->where('store_id', $storeId)
                ->where('updated_at', '>=', $startTime)
                ->where('updated_at', '<=', $endTime)
                ->whereColumn('remaining_amount', '<', 'amount')
                ->select('id', 'person_id', 'amount', 'remaining_amount',
                         'partial_payments', 'status', 'updated_at', 'created_at', 'description')
                ->get();

            $totalCollected = 0;
            $collectedFromCurrentPeriod = 0;
            $collectedFromOldPeriod = 0;
            $collectionDetails = [];

            foreach ($collections as $collection) {
                $collectedInShift = $this->calculateCollectionInPeriod(
                    $collection,
                    $startTime,
                    $endTime
                );

                if ($collectedInShift > 0) {
                    $totalCollected += $collectedInShift;

                    // ⚠️ الافتراض: المديونيات التي أنشئت في نفس الشفت تعتبر "من هذا الشفت"
                    $isFromCurrentPeriod = $collection->created_at >= $startTime;

                    if ($isFromCurrentPeriod) {
                        $collectedFromCurrentPeriod += $collectedInShift;
                    } else {
                        $collectedFromOldPeriod += $collectedInShift;
                    }

                    $employeeName = $this->getEmployeeName($collection->person_id);

                    $collectionDetails[] = [
                        'id' => $collection->id,
                        'employee_id' => $collection->person_id,
                        'employee_name' => $employeeName,
                        'original_amount' => (float) $collection->amount,
                        'collected_in_shift' => $collectedInShift,
                        'remaining_amount' => (float) $collection->remaining_amount,
                        'is_full_payment' => $collection->remaining_amount == 0,
                        'is_partial_payment' => $collection->remaining_amount > 0,
                        'collection_date' => $collection->updated_at,
                        'credit_created_at' => $collection->created_at,
                        'is_from_current_period' => $isFromCurrentPeriod,
                        'type' => $isFromCurrentPeriod ? 'current' : 'old',
                        'description' => $collection->description,
                        'note' => $isFromCurrentPeriod
                            ? 'افتراض: مديونية من هذا الشفت'
                            : 'افتراض: مديونية قديمة',
                    ];
                }
            }

            return [
                'total' => $totalCollected,
                'from_current_period' => $collectedFromCurrentPeriod,
                'from_old_period' => $collectedFromOldPeriod,
                'details' => $collectionDetails,
                'count' => count($collectionDetails),
                'warning' => !empty($collectionDetails) ? 'يتم الاعتماد على تاريخ إنشاء المديونية فقط' : null,
            ];

        } catch (\Exception $e) {
            \Log::error('Error getting credit collections: ' . $e->getMessage());
            return [
                'total' => 0,
                'from_current_period' => 0,
                'from_old_period' => 0,
                'details' => [],
                'count' => 0,
            ];
        }
    }

    private function calculateCollectionInPeriod($collection, $startTime, $endTime)
    {
        $collectedAmount = 0;

        try {
            if ($collection->partial_payments && $collection->partial_payments != '[]' && $collection->partial_payments != 'null') {
                $payments = json_decode($collection->partial_payments, true);

                if (is_array($payments)) {
                    foreach ($payments as $payment) {
                        $paymentDate = isset($payment['date']) ? Carbon::parse($payment['date']) : null;
                        if ($paymentDate && $paymentDate >= $startTime && $paymentDate <= $endTime) {
                            $collectedAmount += $payment['amount'] ?? 0;
                        }
                    }
                }
            } else {
                if ($collection->updated_at >= $startTime && $collection->updated_at <= $endTime) {
                    $collectedAmount = (float) $collection->amount - (float) $collection->remaining_amount;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error calculating collection: ' . $e->getMessage());
        }

        return $collectedAmount;
    }

    private function getEmployeeName($personId)
    {
        try {
            $employee = DB::table('employees')
                ->where('id', $personId)
                ->select('name')
                ->first();

            return $employee ? $employee->name : 'موظف #' . $personId;
        } catch (\Exception $e) {
            \Log::error('Error getting employee name: ' . $e->getMessage());
            return 'غير معروف';
        }
    }

  public function storeBalance(Request $request)
{

    $validator = Validator::make($request->all(), [
        'actual_cash' => 'required|numeric|min:0',
        'notes' => 'nullable|string|max:500'
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    DB::beginTransaction();

    try {
        $accountant = auth('accountant')->user();
        $store = $accountant->store;

        if (!$store) {
            throw new \Exception('المحاسب غير مرتبط بأي متجر');
        }

        // ✅ تحقق من وجود إقفال حديث
        $recentBalance = DailyBalance::where('store_id', $store->id)
            ->where('created_at', '>', now()->subMinutes(1))
            ->first();

        if ($recentBalance) {
            throw new \Exception('تم إصدار الموازنة مؤخراً. الرجاء الانتظار قليلاً.');
        }

        $lastBalance = DailyBalance::where('store_id', $store->id)
            ->latest()
            ->first();

        $startTime = $lastBalance ? $lastBalance->end_time : now()->startOfDay();
        $endTime = now();

        \Log::info('Balance closure started', [
            'store_id' => $store->id,
            'accountant_id' => $accountant->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'actual_cash' => $request->actual_cash
        ]);

        $salesSummary = Sale::where('store_id', $store->id)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->selectRaw('
                COALESCE(SUM(CASE WHEN (description IS NULL OR description != "manual_invoice_entry") THEN final_total ELSE 0 END), 0) as total_sales,
                COALESCE(SUM(CASE WHEN sale_type = "cash" THEN paid_amount ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN sale_type = "mixed" THEN cash_amount ELSE 0 END), 0) as cash_sales,
                COALESCE(SUM(CASE WHEN sale_type = "card" THEN paid_amount ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN sale_type = "mixed" THEN card_amount ELSE 0 END), 0) as card_sales,
                COALESCE(SUM(CASE WHEN sale_type = "credit" THEN paid_amount ELSE 0 END), 0) as credit_payments,
                COALESCE(SUM(CASE WHEN sale_type = "credit" THEN final_total ELSE 0 END), 0) as credit_sales,
                COALESCE(SUM(CASE WHEN sale_type = "internal_use" THEN final_total ELSE 0 END), 0) as internal_use_sales,
                COALESCE(SUM(CASE WHEN (sale_type = "credit" OR has_partial_credit = 1) AND employee_id IS NOT NULL AND remaining_amount > 0 THEN remaining_amount ELSE 0 END), 0) as official_credit_sales,
                COALESCE(SUM(CASE WHEN (sale_type = "credit" OR has_partial_credit = 1) AND employee_id IS NULL AND remaining_amount > 0 THEN remaining_amount ELSE 0 END), 0) as payment_gaps,
                COALESCE(SUM(CASE WHEN (description IS NULL OR description != "manual_invoice_entry") THEN labor_total ELSE 0 END), 0) as total_labor
            ')
            ->first();

        \Log::info('Sales summary calculated', ['total_sales' => $salesSummary->total_sales]);

        $totalSales = $salesSummary->total_sales ?? 0;
        $cashSales = $salesSummary->cash_sales ?? 0;
        $cardSales = $salesSummary->card_sales ?? 0;
        $creditSales = $salesSummary->credit_sales ?? 0;
        $officialCreditSales = $salesSummary->official_credit_sales ?? 0;
        $paymentGaps = $salesSummary->payment_gaps ?? 0;
        $laborTotal = $salesSummary->total_labor ?? 0;
        $internalUseSales = $salesSummary->internal_use_sales ?? 0;

        $creditCollections = $this->getCreditCollections($store->id, $startTime, $endTime);
        $cashFromCollections = $creditCollections['total'];
        $collectedFromCurrentPeriod = $creditCollections['from_current_period'];
        $collectedFromOldPeriod = $creditCollections['from_old_period'];

        $totalCashInShift = $cashSales + $cashFromCollections;

        $productsStats = $this->calculateProductsProfit($store->id, $startTime, $endTime);
        $productsProfit = $productsStats['profit'];
        $totalProductsSalesValue = $productsStats['sales_value'];
        $totalProductsCostValue = $productsStats['cost_value'];

        $netProfit = ($totalSales - $totalProductsCostValue) + $collectedFromCurrentPeriod;

        $expenses = Expense::where('store_id', $store->id)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->sum('amount') ?? 0;

        $withdrawals = Withdrawal::where('store_id', $store->id)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->sum('amount') ?? 0;

        $totalOutgoing = $expenses + $withdrawals;
        $remainingBalance = $netProfit - $totalOutgoing;
        $expectedCashInHand = $totalCashInShift - $totalOutgoing;
        $actualCash = (float) $request->actual_cash;
        $cashDifference = $actualCash - $expectedCashInHand;

        \Log::info('Cash calculation', [
            'expected' => $expectedCashInHand,
            'actual' => $actualCash,
            'difference' => $cashDifference
        ]);



        $detailedSales = Sale::where('store_id', $store->id)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->with(['employee', 'accountant', 'items.product'])
            ->get()->map(function($s) {
                $productsList = [];
                $productsCost = 0.0;
                $productsSalesValue = 0.0;

                foreach ($s->items as $item) {
                    $product = $item->product;
                    $stockQuantity = $this->saleItemStockQuantityForReport($item, $product);
                    $lineTotal = (float) ($item->total ?? (((float) $item->quantity) * ((float) $item->price)));
                    $costPrice = (float) ($product->cost_price ?? 0);
                    $lineCost = $costPrice * $stockQuantity;

                    $productsSalesValue += $lineTotal;
                    $productsCost += $lineCost;

                    $productsList[] = [
                        'name' => $product->name ?? 'منتج',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $lineTotal,
                        'stock_quantity' => $stockQuantity,
                        'cost_price' => $costPrice,
                        'cost_total' => $lineCost,
                        'profit' => $lineTotal - $lineCost,
                    ];
                }

                return [
                    'id' => $s->id,
                    'time' => $s->created_at->format('h:i A'),
                    'type' => $s->sale_type,
                    'received' => $s->paid_amount,
                    'total' => $s->final_total,
                    'labor_total' => $s->labor_total,
                    'labor_desc' => $s->description,
                    // تكلفة المنتجات في التقرير تعتمد على عناصر البيع نفسها لا على ربح مخزن قديم،
                    // حتى تظهر تكلفة الرول/الحبة/الكمية المعدلة بشكل صحيح عند إغلاق الشفت.
                    'cost' => $productsCost,
                    'profit' => ($productsSalesValue + (float) ($s->labor_total ?? 0)) - $productsCost,
                    'employee' => $s->employee->name ?? '---',
                    'accountant' => $s->accountant->name ?? '---',
                    'products' => $productsList,
                    'products_count' => count($productsList)
                ];
            });

        $detailedExpenses = Expense::where('store_id', $store->id)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->get()->map(function($e) {
                return [
                    'time' => $e->created_at->format('h:i A'),
                    // نعتمد على الحقول الفعلية في جدول المصروفات (type / description)
                    'category' => $e->type ?? 'مصروف عام',
                    'reason' => $e->description ?? '—',
                    'amount' => $e->amount
                ];
            });

        $detailedWithdrawals = Withdrawal::where('store_id', $store->id)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->get()->map(function($w) {
                return [
                    'time' => $w->created_at->format('h:i A'),
                    'reason' => $w->reason ?? 'سحب نقدي',
                    'amount' => $w->amount
                ];
            });

        $reportData = [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'accountant_id' => $accountant->id,
            'accountant_name' => $accountant->name,
            'start_time' => $startTime->format('Y-m-d H:i'),
            'end_time' => $endTime->format('Y-m-d H:i'),
            'report_date' => now()->format('Y-m-d H:i'),

            'total_sales' => $totalSales,
            'sales_breakdown' => [
                'cash_from_new_sales' => $cashSales,
                'card_from_new_sales' => $cardSales,
                'credit_sales' => $creditSales,
                'official_credit' => $officialCreditSales,
                'payment_gaps' => $paymentGaps,
                'internal_use' => $internalUseSales,
            ],

            'details_tables' => [
                'all_sales' => $detailedSales,
                'withdrawals_list' => $detailedWithdrawals,
                'expenses_list' => $detailedExpenses,
                'collections' => $creditCollections['details'] ?? [],
            ],

            'credit_collections' => [
                'total' => $creditCollections['total'],
                'from_current_period' => $collectedFromCurrentPeriod,
                'from_old_period' => $collectedFromOldPeriod,
                'details' => $creditCollections['details'],
                'count' => $creditCollections['count'],
            ],

            'products_details' => [
                'sales_value' => $totalProductsSalesValue,
                'cost_value' => $totalProductsCostValue,
                'profit' => $productsProfit,
            ],
            'labor_total' => $laborTotal,
            'net_profit' => $netProfit,

            'outgoing_today' => [
                'expenses' => $expenses,
                'withdrawals' => $withdrawals,
                'total' => $totalOutgoing,
            ],

            'remaining_balance' => $remainingBalance,

            'cash_details' => [
                'cash_from_new_sales' => $cashSales,
                'cash_from_current_collections' => $collectedFromCurrentPeriod,
                'cash_from_old_collections' => $collectedFromOldPeriod,
                'total_cash_collections' => $cashFromCollections,
                'total_cash_in_shift' => $totalCashInShift,
                'expected' => $expectedCashInHand,
                'actual' => $actualCash,
                'difference' => $cashDifference,
            ],

            'notes' => $request->notes,
        ];

        // \Log::info('Creating DailyBalance record...');

        $dailyBalance = DailyBalance::create([
            'store_id' => $store->id,
            'accountant_id' => $accountant->id,
            'system_sales_total' => $totalSales,
            'system_cash_expected' => $expectedCashInHand,
            'actual_cash_submitted' => $actualCash,
            'difference' => $cashDifference,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'notes' => $request->notes,
        ]);

        // \Log::info('DailyBalance created with ID: ' . $dailyBalance->id);

        // Log::create([
        //     'store_id' => $store->id,
        //     'user_id' => null,
        //     'actor_type' => 'accountant',
        //     'actor_id' => $accountant->id,
        //     'model_type' => 'DailyBalance',
        //     'model_id' => $dailyBalance->id,
        //     'action' => 'balance_done',
        //     'description' => 'تم إصدار الموازنة اليومية',
        //     'details' => json_encode($reportData, JSON_UNESCAPED_UNICODE),
        //     'ip' => $request->ip(),
        //     'user_agent' => $request->userAgent(),
        // ]);

        $waUrl = $this->generateReportAndWhatsApp($store, $accountant, $reportData);

        DB::commit();

        Cache::forget('shift_sales_' . $store->id . '_' . $startTime->timestamp);
        Cache::forget('shift_expenses_' . $store->id . '_' . $startTime->timestamp);
        Cache::forget('shift_withdrawals_' . $store->id . '_' . $startTime->timestamp);

        \Log::info('✅ Balance closed successfully', ['balance_id' => $dailyBalance->id]);

        return redirect()->route('accountant.dashboard')->with([
            'success' => 'تم اصدار الموازنة بنجاح',
            'balance_id' => $dailyBalance->id,
            'wa_url' => $waUrl
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('❌ Balance closure failed: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return redirect()->back()->with('error', 'فشل إصدار الموازنة: ' . $e->getMessage())->withInput();
    }
}

    private function calculateProductsProfit($storeId, $startTime, $endTime)
    {
        $totalSalesValue = 0.0;
        $totalCostValue = 0.0;

        Sale::where('store_id', $storeId)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->with(['items.product'])
            ->select('id')
            ->chunk(100, function ($salesChunk) use (&$totalSalesValue, &$totalCostValue) {
                foreach ($salesChunk as $sale) {
                    foreach ($sale->items as $item) {
                        // سعر البيع يجب أن يأتي من sale_items.total لأنه يمثل إجمالي السطر وقت البيع
                        // وقد يختلف عن quantity * price في الرولات أو بعد تعديل العملية.
                        $lineSalesValue = (float) ($item->total ?? (((float) $item->quantity) * ((float) $item->price)));
                        $stockQuantity = $this->saleItemStockQuantityForReport($item, $item->product);
                        $lineCostValue = $stockQuantity * (float) ($item->product->cost_price ?? 0);

                        $totalSalesValue += $lineSalesValue;
                        $totalCostValue += $lineCostValue;
                    }
                }
            });

        return [
            'sales_value' => $totalSalesValue,
            'cost_value' => $totalCostValue,
            'profit' => $totalSalesValue - $totalCostValue,
        ];
    }

    private function saleItemStockQuantityForReport($item, $product): float
    {
        $quantity = (float) ($item->quantity ?? 0);
        $stockQuantity = (float) ($item->custom_consumption ?? $quantity);

        // للرول/المتر/القص المخصص نخزن غالباً custom_consumption ككمية المخزون الأساسية
        // (مثل عدد الأمتار المخصومة)، لذلك نعتمدها مباشرة في تكلفة التقرير.
        if ($item->custom_consumption !== null) {
            return $stockQuantity;
        }

        if (!$product) {
            return $quantity;
        }

        // احتياط لعمليات الرول القديمة التي قد لا تحتوي custom_consumption:
        // إذا كانت الوحدة رول/وحدة لمنتج fractional نحول عدد الرولات إلى أمتار قبل حساب التكلفة.
        if (($product->product_type ?? null) === 'fractional' && in_array((string) ($item->unit_type ?? ''), ['roll', 'unit'], true)) {
            $rollLength = (float) ($item->roll_length_at_sale ?: $product->roll_length ?: 0);
            if ($rollLength > 0) {
                return $quantity * $rollLength;
            }
        }

        // إذا كان المنتج طقماً وتم بيعه بالحبة، نحول الحبات إلى كمية الطقم الأساسية.
        if (!empty($product->is_splittable) && (string) ($item->unit_type ?? '') === 'piece') {
            return $quantity / max(1, (float) ($product->items_per_unit ?? 1));
        }

        return $quantity;
    }

    private function generateReportAndWhatsApp($store, $accountant, $reportData)
    {
        // 1. التحقق من الحد اليومي
        $cacheKey = 'whatsapp_messages_' . $store->id . '_' . now()->format('Ymd');
        $todayMessages = Cache::get($cacheKey, 0);

        if ($todayMessages >= 10) {
            \Log::warning('WhatsApp rate limit exceeded for store: ' . $store->id);
            return null;
        }

        // تنظيف التقارير القديمة مرة واحدة يومياً لمنع تراكم ملفات PDF.
        // نستخدم Key عام لليوم حتى لا يتكرر التنظيف مع كل عملية إقفال.
        $cleanupKey = 'reports_cleanup_' . now()->format('Ymd');
        if (!Cache::has($cleanupKey)) {
            $this->cleanupOldReports();
            Cache::put($cleanupKey, true, now()->endOfDay());
        }

        // 2. تجهيز رقم الهاتف
        $storeOwner = $store->user;
        $managerPhone = $storeOwner->phone ?? $store->phone ?? null;

        if (!$managerPhone) {
            \Log::warning('No phone number found for store: ' . $store->id);
            return null;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $managerPhone);
        if (!str_starts_with($cleanPhone, '966')) {
            $cleanPhone = '966' . ltrim($cleanPhone, '0');
        }

        // 3. ✅ إنشاء PDF
        $fileName = 'Report_' . time() . '_' . $store->id . '.pdf';
        $filePath = public_path('reports/' . $fileName);

        try {
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            $pdfData = [
                'store' => $store,
                'accountant' => $accountant,
                'data' => $reportData,
                'report_title' => 'موازنة - ' . $store->name,
            ];

            PDF::loadView('pdf.pdf_report', $pdfData)
               ->setOption('encoding', 'utf-8')
               ->setOption('enable-local-file-access', true)
               ->save($filePath);

            \Log::info('✅ PDF created successfully: ' . $fileName);

        } catch (\Exception $e) {
            \Log::error('❌ PDF creation failed: ' . $e->getMessage());
            $fileName = null;
        }

        $reportUrl = $fileName ? url('reports/' . $fileName) : 'غير متوفر';
        $message = $this->buildWhatsAppMessage($store, $accountant, $reportData, $reportUrl);

        $encodedMessage = rawurlencode($message);
        $waUrl = "https://wa.me/{$cleanPhone}?text={$encodedMessage}";

        Cache::put($cacheKey, $todayMessages + 1, now()->addDay());

        return $waUrl;
    }

    private function buildWhatsAppMessage($store, $accountant, $reportData, $reportUrl)
{
    $date = now()->format('Y-m-d');
    $time = now()->format('h:i A');

    $cashSales = (float) ($reportData['sales_breakdown']['cash_from_new_sales'] ?? 0);
    $cardSales = (float) ($reportData['sales_breakdown']['card_from_new_sales'] ?? 0);
    $salesCount = isset($reportData['details_tables']['all_sales']) && is_countable($reportData['details_tables']['all_sales'])
        ? count($reportData['details_tables']['all_sales'])
        : 0;
    $collectionsCount = (int) ($reportData['credit_collections']['count'] ?? 0);
    $operationsCount = $salesCount + $collectionsCount;
    $totalSales = (float) ($reportData['total_sales'] ?? 0);
    $totalOutgoing = (float) ($reportData['outgoing_today']['total'] ?? 0);
    $productsSalesValue = (float) ($reportData['products_details']['sales_value'] ?? 0);
    $productsCostValue = (float) ($reportData['products_details']['cost_value'] ?? 0);

    $message = "📊 *تقرير إقفال المتجر*\n";
    $message .= "🏪 " . $store->name . "\n";
    $message .= "👤 " . $accountant->name . "\n";
    $message .= "📅 " . $date . " | " . $time . "\n\n";

    $message .= "🧾 *ملخص الشفت :*\n";
    $message .= "🕒 الفترة: " . ($reportData['start_time'] ?? '-') . " → " . ($reportData['end_time'] ?? '-') . "\n";
    if (!empty($reportData['notes'])) {
        $message .= "📝 ملاحظة الإغلاق: " . $reportData['notes'] . "\n";
    }
    $message .= "💰 اجمالي العمليات: " . number_format($totalSales, 2) . " ريال\n";
    $message .= "🛒 قيمة المبيعات (بسعر البيع): " . number_format($productsSalesValue, 2) . " ريال\n";
    $message .= "📦 تكلفة المنتجات (بسعر التكلفة): " . number_format($productsCostValue, 2) . " ريال\n";
    $message .= "📈 ربح المنتجات: " . number_format($productsSalesValue - $productsCostValue, 2) . " ريال\n";
    $message .= "💵 عمليات الكاش: " . number_format($cashSales, 2) . " ريال\n";
    $message .= "💳 عمليات الشبكة: " . number_format($cardSales, 2) . " ريال\n";
    $message .= "📤 مصاريف: " . number_format($totalOutgoing, 2) . " ريال\n";
    $message .= "🔢 عدد العمليات: " . number_format($operationsCount) . "\n\n";

    if (($reportData['labor_total'] ?? 0) > 0) {
        $message .= "👷 *أجرة اليد:* " . number_format((float) $reportData['labor_total'], 2) . " ريال\n\n";
    }

    $message .= "💵 *مطابقة الصندوق:*\n";
    $message .= "💰 الكاش المتوقع: " . number_format((float) ($reportData['cash_details']['expected'] ?? 0), 2) . " ريال\n";
    $message .= "💵 الكاش المستلم: " . number_format((float) ($reportData['cash_details']['actual'] ?? 0), 2) . " ريال\n";

    $diff = (float) ($reportData['cash_details']['difference'] ?? 0);
    if ($diff > 0) {
        $message .= "➕ فائض: " . number_format($diff, 2) . " ريال ✅\n";
    } elseif ($diff < 0) {
        $message .= "➖ عجز: " . number_format(abs($diff), 2) . " ريال ⚠️\n";
    } else {
        $message .= "✓ مطابق تماماً ✅\n";
    }

    if (!empty($reportData['notes'])) {
        $message .= "\n📝 *ملاحظات:*\n" . $reportData['notes'] . "\n";
    }

    $message .= "\n📄 *تقرير PDF:*\n";
    $message .= $reportUrl;

    return $message;
}

    private function createShiftHtmlReport($data)
    {
        $reportData = $data['data'];
        $salesRows = $reportData['details_tables']['all_sales'] ?? [];
        $salesRows = is_iterable($salesRows) ? $salesRows : [];

        $cashSales = (float) ($reportData['sales_breakdown']['cash_from_new_sales'] ?? 0);
        $cardSales = (float) ($reportData['sales_breakdown']['card_from_new_sales'] ?? 0);
        $productsSalesValue = (float) ($reportData['products_details']['sales_value'] ?? 0);
        $productsCostValue = (float) ($reportData['products_details']['cost_value'] ?? 0);
        $productsProfitValue = (float) ($reportData['products_details']['profit'] ?? 0);
        $collectionsCount = (int) ($reportData['credit_collections']['count'] ?? 0);
        $operationsCount = (is_countable($salesRows) ? count($salesRows) : 0) + $collectionsCount;
        $diff = (float) ($reportData['cash_details']['difference'] ?? 0);
        $diffClass = $diff >= 0 ? 'positive' : 'negative';
        $diffLabel = $diff > 0 ? 'فائض' : ($diff < 0 ? 'عجز' : 'مطابق');

        $typeMap = [
            'cash' => 'نقد',
            'card' => 'شبكة',
            'mixed' => 'ميكس',
            'credit' => 'آجل',
            'internal_use' => 'استهلاك داخلي',
        ];

        $html = '<!DOCTYPE html><html dir="rtl"><head><meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; direction: rtl; color: #1f2937; }
                h1 { color: #111827; text-align: center; margin-bottom: 12px; }
                h2 { color: #1f2937; margin: 18px 0 8px; }
                .header { background: #f8fafc; padding: 12px; border-radius: 8px; border-right: 4px solid #0ea5e9; margin-bottom: 16px; }
                .note { background: #fffbeb; border-right: 4px solid #f59e0b; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
                th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: center; font-size: 13px; }
                th { background-color: #0f172a; color: #fff; }
                .total { font-weight: bold; background-color: #f3f4f6; }
                .negative { color: #dc2626; font-weight: bold; }
                .positive { color: #16a34a; font-weight: bold; }
                .muted { color: #6b7280; font-size: 12px; }
            </style></head><body>';

        $html .= '<h1>' . $data['report_title'] . '</h1>
            <div class="header">
                <p><strong>المتجر:</strong> ' . htmlspecialchars((string) ($data['store']->name ?? '-')) . ' | <strong>المحاسب:</strong> ' . htmlspecialchars((string) ($data['accountant']->name ?? '-')) . '</p>
                <p><strong>الفترة:</strong> ' . htmlspecialchars((string) ($reportData['start_time'] ?? '-')) . ' → ' . htmlspecialchars((string) ($reportData['end_time'] ?? '-')) . '</p>
            </div>';

        if (!empty($reportData['notes'])) {
            $html .= '<div class="note"><strong>ملاحظة الإغلاق:</strong> ' . nl2br(htmlspecialchars((string) $reportData['notes'])) . '</div>';
        }

        $html .= '<h2>📊 ملخص الشفت (مطابق صفحة المبيعات)</h2>
            <table>
                <tr><th>البند</th><th>القيمة</th></tr>
                <tr><td>إجمالي العمليات</td><td>' . number_format((float) ($reportData['total_sales'] ?? 0), 2) . ' ريال</td></tr>
                <tr><td>قيمة المبيعات (بسعر البيع)</td><td>' . number_format($productsSalesValue, 2) . ' ريال</td></tr>
                <tr><td>قيمة التكلفة</td><td>' . number_format($productsCostValue, 2) . ' ريال</td></tr>
                <tr><td>ربح المنتجات</td><td>' . number_format($productsProfitValue, 2) . ' ريال</td></tr>
                <tr><td>عمليات الكاش</td><td>' . number_format($cashSales, 2) . ' ريال</td></tr>
                <tr><td>عمليات الشبكة</td><td>' . number_format($cardSales, 2) . ' ريال</td></tr>
                <tr><td>المصاريف + السحوبات</td><td>' . number_format((float) ($reportData['outgoing_today']['total'] ?? 0), 2) . ' ريال</td></tr>
                <tr><td>عدد العمليات</td><td>' . number_format($operationsCount) . '</td></tr>
                <tr><td>أجرة اليد</td><td>' . number_format((float) ($reportData['labor_total'] ?? 0), 2) . ' ريال</td></tr>
                <tr class="total"><td>صافي الربح</td><td>' . number_format((float) ($reportData['net_profit'] ?? 0), 2) . ' ريال</td></tr>
            </table>';

        $html .= '<h2>🧾 تفاصيل العمليات</h2>
            <table>
                <tr>
                    <th>#</th>
                    <th>الوقت</th>
                    <th>نوع العملية</th>
                    <th>طريقة الدفع</th>
                    <th>القيمة</th>
                    <th>المستلم</th>
                </tr>';

        $index = 1;
        foreach ($salesRows as $row) {
            $row = (array) $row;
            $saleType = (string) ($row['type'] ?? '');
            $paymentLabel = $typeMap[$saleType] ?? $saleType;

            $productsCount = (int) ($row['products_count'] ?? 0);
            $laborTotal = (float) ($row['labor_total'] ?? 0);
            $operationKind = ($laborTotal > 0 && $productsCount === 0) ? 'شغل يد' : 'منتجات';

            $displayTotal = (float) ($row['total'] ?? 0);
            $received = (float) ($row['received'] ?? 0);
            $amountLabel = $displayTotal > 0 ? $displayTotal : $received;

            $html .= '<tr>
                <td>' . $index++ . '</td>
                <td>' . htmlspecialchars((string) ($row['time'] ?? '--')) . '</td>
                <td>' . htmlspecialchars($operationKind) . '</td>
                <td>' . htmlspecialchars($paymentLabel ?: '-') . '</td>
                <td>' . number_format($amountLabel, 2) . ' ريال</td>
                <td>' . number_format($received, 2) . ' ريال</td>
            </tr>';
        }

        if ($index === 1) {
            $html .= '<tr><td colspan="6" class="muted">لا توجد عمليات ضمن هذه الفترة.</td></tr>';
        }

        $html .= '</table>';

        $html .= '<h2>🏁 مطابقة الصندوق</h2>
            <table>
                <tr><td>الكاش المتوقع</td><td>' . number_format((float) ($reportData['cash_details']['expected'] ?? 0), 2) . ' ريال</td></tr>
                <tr><td>الكاش الفعلي المسلم</td><td>' . number_format((float) ($reportData['cash_details']['actual'] ?? 0), 2) . ' ريال</td></tr>
                <tr class="total"><td>الحالة</td><td class="' . $diffClass . '">' . $diffLabel . ' (' . number_format(abs($diff), 2) . ' ريال)</td></tr>
            </table>';

        $html .= '</body></html>';
        return $html;
    }

    private function formatOp($model, $type)
    {
        $employeeName = '--';

        try {
            \Log::info("Formatting $type - ID: {$model->id}");

            if ($type === 'withdrawal') {
                if (isset($model->person_id) && $model->person_id) {
                    \Log::info("Withdrawal person_id: {$model->person_id}");

                    if (method_exists($model, 'person') && $model->person) {
                        $employeeName = $model->person->name ?? '--';
                        \Log::info("Got name from person relationship: {$employeeName}");
                    } else {
                        $employee = \App\Models\Employee::find($model->person_id);
                        $employeeName = $employee ? $employee->name : 'موظف #' . $model->person_id;
                        \Log::info("Got name from Employee model: {$employeeName}");
                    }
                } else {
                    \Log::info("No person_id found in withdrawal");
                }
            } else {
                if (isset($model->employee_id) && $model->employee_id) {
                    \Log::info("{$type} employee_id: {$model->employee_id}");

                    if (method_exists($model, 'employee') && $model->employee) {
                        $employeeName = $model->employee->name ?? '--';
                        \Log::info("Got name from employee relationship: {$employeeName}");
                    } else {
                        $employee = \App\Models\Employee::find($model->employee_id);
                        $employeeName = $employee ? $employee->name : 'موظف #' . $model->employee_id;
                        \Log::info("Got name from Employee model: {$employeeName}");
                    }
                } else {
                    \Log::info("No employee_id found in {$type}");
                }
            }

        } catch (\Exception $e) {
            \Log::error("Error in formatOp for $type: " . $e->getMessage());
            $employeeName = 'نظام';
        }

        $description = $model->description ?? $model->reason ?? $model->note ?? 'عملية نظام';
        $amount = $model->final_total ?? $model->amount ?? 0;

        return (object)[
            'type' => $type,
            'employee' => $employeeName,
            'description' => \Str::limit($description, 30),
            'amount' => $amount,
            'created_at' => $model->created_at,
            'formatted_time' => optional($model->created_at)->format('h:i A') ?? '--',
        ];
    }

    public function showReport($id)
    {
        $accountant = auth('accountant')->user();
        $balance = DailyBalance::where('store_id', $accountant->store_id)->findOrFail($id);

        $logDetails = Log::where('model_type', 'DailyBalance')
            ->where('model_id', $balance->id)
            ->where('action', 'balance_done')
            ->first();

        $data = $logDetails ? json_decode($logDetails->details, true) : [];

        return view('accountant.balance.report', [
            'balance' => $balance,
            'store' => $accountant->store,
            'accountant' => $accountant,
            'data' => $data,
        ]);
    }

    private function cleanupOldReports()
    {
        try {
            // سياسة الاحتفاظ: حذف تقارير PDF الأقدم من 10 أيام.
            $cutoffDate = now()->subDays(10)->getTimestamp();
            $folder = public_path('reports/');

            if (file_exists($folder)) {
                $files = glob($folder . 'Report_*.pdf');

                foreach ($files as $file) {
                    if (filemtime($file) < $cutoffDate) {
                        @unlink($file);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error cleaning up old reports: ' . $e->getMessage());
        }
    }
}
