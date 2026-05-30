<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\ArabicPdf;

class InternalUseController extends Controller
{
    /**
     * عرض صفحة إنشاء استهلاك داخلي (للمحاسب فقط)
     */
    public function create()
    {
        $accountant = Auth::guard('accountant')->user();

        if (!$accountant) {
            abort(403, 'هذه الصفحة مخصصة للمحاسبين فقط');
        }

        $storeId = $accountant->store_id;

        $products = Product::where('store_id', $storeId)
            ->select(
                'id',
                'name',
                'cost_price as price',
                'quantity',
                'barcode',
                'product_type',
                'is_splittable',
                'items_per_unit',
                'roll_length',
                'waste_percentage',
                'piece_price'
            )
            ->get();

        $fractions = DB::table('product_fractions')
            ->whereIn('product_id', $products->pluck('id'))
            ->get();

        return view('cashier.internal-use.create', compact('products', 'fractions'));
    }

    /**
     * تخزين عملية استهلاك داخلي (للمحاسب فقط)
     */
    public function store(Request $request)
{
    $accountant = Auth::guard('accountant')->user();

    if (!$accountant) {
        abort(403, 'هذه الصفحة مخصصة للمحاسبين فقط');
    }

    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|numeric|min:0.01',
        'unit_type' => 'nullable|in:default,meters,roll,piece,kit',
        'reason' => 'nullable|string|max:255',
        'internal_notes' => 'nullable|string|max:500',
    ]);

    $storeId = $accountant->store_id;

    // جلب الـ user_id الخاص بصاحب المتجر
    $store = DB::table('stores')->where('id', $storeId)->first();
    $userId = $store->user_id;  // هذا هو ID المالك في جدول users

    $product = Product::where('id', $request->product_id)
        ->where('store_id', $storeId)
        ->first();

    if (!$product) {
        return redirect()->back()->with('error', 'المنتج غير موجود');
    }

    $unitType = $request->unit_type ?? 'default';
    $actualDeduction = $this->calculateActualDeduction($product, $request->quantity, $unitType);

    if ($product->quantity < $actualDeduction) {
        return redirect()->back()->with('error', 'الكمية غير كافية في المخزون');
    }

    DB::beginTransaction();
    try {
        // حساب سعر الوحدة
        $unitPrice = $product->price;
        if ($product->is_splittable && $unitType === 'piece' && $product->piece_price > 0) {
            $unitPrice = $product->piece_price;
        }

        $totalCost = $unitPrice * $request->quantity;

        // ✅ تجهيز الملاحظات مع إضافة اسم المحاسب
        $reason = $request->reason ?? 'استهلاك داخلي';
        $internalNotes = $request->internal_notes;

        // إضافة اسم المحاسب إلى الملاحظات الداخلية
        $accountantName = $accountant->name ?? 'محاسب';
        $internalNotesWithAccountant = "($accountantName) " . ($internalNotes ?? '');

        // ✅ دمج الملاحظات للعرض في سجل المخزون
        $finalNotes = $reason;
        if ($internalNotesWithAccountant) {
            $finalNotes .= ' | ' . $internalNotesWithAccountant;
        }

        // إنشاء سجل البيع
        $sale = Sale::create([
            'store_id'        => $storeId,
            'accountant_id'   => $accountant->id,
            'user_id'         => $userId,  // ✅ إضافة user_id الخاص بالمالك
            'total'           => $totalCost,
            'paid_amount'     => $totalCost,
            'sale_type'       => 'internal_use',
            'internal_notes'  => $internalNotesWithAccountant,  // ✅ مع اسم المحاسب
        ]);

        // إنشاء عنصر البيع
        $saleItemPayload = [
            'sale_id'     => $sale->id,
            'product_id'  => $product->id,
            'quantity'    => $request->quantity,
            'price'       => $unitPrice,
            'total'       => $totalCost,
        ];

        if ($this->supportsSaleItemUnitType()) {
            $saleItemPayload['unit_type'] = $unitType;
        }

        SaleItem::create($saleItemPayload);

        // ✅ خصم المخزون باستخدام user_id الخاص بالمالك
        $product->decreaseStock(
            $actualDeduction,
            'استهلاك داخلي: ' . $finalNotes,
            $userId,  // ✅ user_id صحيح من جدول users
            'normalized'
        );

        DB::commit();

        $unitName = $this->getUnitName($unitType);
        $message = "✅ تم خصم {$request->quantity} {$unitName} من المخزون بواسطة {$accountantName}";

        return redirect()->back()->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', '❌ حدث خطأ: ' . $e->getMessage());
    }
}

    /**
     * عرض صفحة تقرير الاستهلاك (للمالك - تعرض HTML)
     */
    public function reportView(Request $request, $storeId)
    {
        $user = Auth::user();

        if (!$user) {
            abort(401);
        }

        $store = DB::table('stores')
            ->where('id', $storeId)
            ->where('user_id', $user->id)
            ->first();

        if (!$store) {
            abort(403, 'غير مصرح لك بالوصول لهذا المتجر');
        }

        $month = max(1, min(12, (int) $request->get('month', now()->month)));
        $year = max(2020, min(2100, (int) $request->get('year', now()->year)));

        $reportData = $this->buildMonthlyConsumptionData((int) $storeId, $month, $year);
        $ownerPurchaseGroups = $this->buildOwnerPurchaseGroupsFromRecords($reportData['records']);
        $ownerPurchaseTypeOptions = array_values($this->ownerPurchaseGroupingMap());

        return view('cashier.internal-use.report', compact(
            'storeId',
            'store',
            'month',
            'year',
            'reportData',
            'ownerPurchaseGroups',
            'ownerPurchaseTypeOptions'
        ));
    }

    public function storeByOwner(Request $request, $storeId)
    {
        $owner = Auth::user();
        if (!$owner) {
            abort(401);
        }

        $store = DB::table('stores')
            ->where('id', $storeId)
            ->where('user_id', $owner->id)
            ->first();

        if (!$store) {
            abort(403, 'غير مصرح لك بالوصول لهذا المتجر');
        }

        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $description = trim((string) ($validated['description'] ?? ''));

        Purchase::create([
            'store_id' => $storeId,
            'user_id' => $owner->id,
            'product_id' => null,
            'purchase_name' => trim($validated['type']),
            'quantity' => 1,
            'cost' => (float) $validated['amount'],
            'description' => $description !== '' ? $description : null,
        ]);

        return redirect()->back()->with('success', 'تم تسجيل مشتريات استهلاك المالك بنجاح');
    }

    public function updateOwnerPurchase(Request $request, $storeId, $purchaseId)
    {
        $owner = Auth::user();
        if (!$owner) {
            abort(401);
        }

        $store = DB::table('stores')
            ->where('id', $storeId)
            ->where('user_id', $owner->id)
            ->first();

        if (!$store) {
            abort(403, 'غير مصرح لك بالوصول لهذا المتجر');
        }

        $purchase = Purchase::where('id', $purchaseId)
            ->where('store_id', $storeId)
            ->firstOrFail();

        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $description = trim((string) ($validated['description'] ?? ''));

        $purchase->update([
            'purchase_name' => trim($validated['type']),
            'cost' => (float) $validated['amount'],
            'description' => $description !== '' ? $description : null,
        ]);

        return redirect()->back()->with('success', 'تم تعديل مشتريات المالك بنجاح');
    }

    public function destroyOwnerPurchase($storeId, $purchaseId)
    {
        $owner = Auth::user();
        if (!$owner) {
            abort(401);
        }

        $store = DB::table('stores')
            ->where('id', $storeId)
            ->where('user_id', $owner->id)
            ->first();

        if (!$store) {
            abort(403, 'غير مصرح لك بالوصول لهذا المتجر');
        }

        $purchase = Purchase::where('id', $purchaseId)
            ->where('store_id', $storeId)
            ->firstOrFail();

        $purchase->delete();

        return redirect()->back()->with('success', 'تم حذف مشتريات المالك بنجاح');
    }


    public function updateAccountantConsumption(Request $request, $storeId, $saleId)
    {
        $owner = Auth::user();
        if (!$owner) {
            abort(401);
        }

        $store = DB::table('stores')
            ->where('id', $storeId)
            ->where('user_id', $owner->id)
            ->first();

        if (!$store) {
            abort(403, 'غير مصرح لك بالوصول لهذا المتجر');
        }

        $sale = Sale::where('id', $saleId)
            ->where('store_id', $storeId)
            ->where('sale_type', 'internal_use')
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->firstOrFail();

        $saleItem = SaleItem::where('sale_id', $sale->id)->firstOrFail();

        $product = Product::where('id', $saleItem->product_id)
            ->where('store_id', $storeId)
            ->firstOrFail();

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'unit_type' => 'nullable|in:default,meters,roll,piece,kit',
            'internal_notes' => 'nullable|string|max:500',
        ]);

        $currentUnitType = $this->supportsSaleItemUnitType() ? ($saleItem->unit_type ?: 'default') : 'default';
        $unitType = $validated['unit_type'] ?? $currentUnitType;
        $newQuantity = (float) $validated['quantity'];

        $oldDeduction = $this->calculateActualDeduction($product, (float) $saleItem->quantity, $currentUnitType);
        $newDeduction = $this->calculateActualDeduction($product, $newQuantity, $unitType);

        $requiredExtra = $newDeduction - $oldDeduction;
        if ($requiredExtra > 0 && (float) $product->quantity < $requiredExtra) {
            return redirect()->back()->with('error', 'الكمية غير كافية في المخزون لتعديل الاستهلاك');
        }

        DB::beginTransaction();
        try {
            if ($requiredExtra > 0) {
                $product->decreaseStock(
                    $requiredExtra,
                    'تعديل استهلاك محاسب (زيادة الخصم) - عملية #' . $sale->id,
                    $owner->id,
                    'normalized'
                );
            } elseif ($requiredExtra < 0) {
                $product->increaseStock(
                    abs($requiredExtra),
                    'تعديل استهلاك محاسب (استرجاع مخزون) - عملية #' . $sale->id,
                    $owner->id
                );
            }

            $unitPrice = (float) $product->price;
            if ($product->is_splittable && $unitType === 'piece' && (float) $product->piece_price > 0) {
                $unitPrice = (float) $product->piece_price;
            }
            $newTotal = round($unitPrice * $newQuantity, 2);

            $saleItemPayload = [
                'quantity' => $newQuantity,
                'price' => $unitPrice,
                'total' => $newTotal,
            ];
            if ($this->supportsSaleItemUnitType()) {
                $saleItemPayload['unit_type'] = $unitType;
            }

            $saleItem->update($saleItemPayload);

            $notes = trim((string) ($validated['internal_notes'] ?? ''));
            $sale->update([
                'total' => $newTotal,
                'paid_amount' => $newTotal,
                'remaining_amount' => 0,
                'internal_notes' => $notes !== '' ? $notes : null,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'تم تعديل استهلاك المحاسب بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'تعذر تعديل استهلاك المحاسب: ' . $e->getMessage());
        }
    }

    public function destroyAccountantConsumption($storeId, $saleId)
    {
        $owner = Auth::user();
        if (!$owner) {
            abort(401);
        }

        $store = DB::table('stores')
            ->where('id', $storeId)
            ->where('user_id', $owner->id)
            ->first();

        if (!$store) {
            abort(403, 'غير مصرح لك بالوصول لهذا المتجر');
        }

        $sale = Sale::where('id', $saleId)
            ->where('store_id', $storeId)
            ->where('sale_type', 'internal_use')
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->firstOrFail();

        $saleItems = SaleItem::where('sale_id', $sale->id)->get();

        DB::beginTransaction();
        try {
            foreach ($saleItems as $item) {
                $product = Product::where('id', $item->product_id)
                    ->where('store_id', $storeId)
                    ->first();

                if ($product) {
                    $itemUnitType = $this->supportsSaleItemUnitType() ? ($item->unit_type ?: 'default') : 'default';
                    $deduction = $this->calculateActualDeduction($product, (float) $item->quantity, $itemUnitType);
                    $product->increaseStock(
                        $deduction,
                        'حذف استهلاك محاسب (استرجاع مخزون) - عملية #' . $sale->id,
                        $owner->id,
                        'normalized'
                    );
                }
            }

            SaleItem::where('sale_id', $sale->id)->delete();
            $sale->delete();

            DB::commit();
            return redirect()->back()->with('success', 'تم حذف استهلاك المحاسب واسترجاع المخزون بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'تعذر حذف استهلاك المحاسب: ' . $e->getMessage());
        }
    }

    /**
     * تقرير الاستهلاك الداخلي (للمالك - AJAX)
     */
    public function report(Request $request, $storeId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
        }

        $store = DB::table('stores')
            ->where('id', $storeId)
            ->where('user_id', $user->id)
            ->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
            $orderBy = $request->get('order_by', 'total_cost');
            $perPage = max(1, min(50, (int) $request->get('per_page', 8)));
            $page = max(1, (int) $request->get('page', 1));

            $totalStatsRaw = DB::table('sales')
                ->select(
                    DB::raw('COUNT(DISTINCT sales.id) as total_operations'),
                    DB::raw('IFNULL(SUM(sales.total), 0) as total_cost'),
                    DB::raw('IFNULL(AVG(sales.total), 0) as avg_per_operation'),
                    DB::raw('COUNT(DISTINCT si.product_id) as total_products')
                )
                ->leftJoin('sale_items as si', 'sales.id', '=', 'si.sale_id')
                ->where('sales.store_id', $storeId)
                ->where('sales.sale_type', 'internal_use')
                ->where(function ($query) {
                    $query->whereNull('sales.description')
                        ->orWhere('sales.description', '!=', 'manual_invoice_entry');
                })
                ->whereBetween('sales.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->first();

            $ownerPurchasesStatsRaw = DB::table('purchases')
                ->where('store_id', $storeId)
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->select(
                    DB::raw('COUNT(id) as total_owner_purchases'),
                    DB::raw('IFNULL(SUM(cost), 0) as owner_purchases_total')
                )
                ->first();

            $ownerPurchasesTotal = (float) ($ownerPurchasesStatsRaw->owner_purchases_total ?? 0);
            $ownerPurchasesCount = (int) ($ownerPurchasesStatsRaw->total_owner_purchases ?? 0);

            $totalStats = [
                'total_operations' => (int)($totalStatsRaw->total_operations ?? 0),
                'total_cost' => (float)($totalStatsRaw->total_cost ?? 0),
                'avg_per_operation' => (float)($totalStatsRaw->avg_per_operation ?? 0),
                'total_products' => (int)($totalStatsRaw->total_products ?? 0),
                'owner_purchases_total' => $ownerPurchasesTotal,
                'owner_purchases_count' => $ownerPurchasesCount,
                'grand_total_with_owner' => (float) (($totalStatsRaw->total_cost ?? 0) + $ownerPurchasesTotal),
            ];

            $query = DB::table('sales as s')
                ->join('sale_items as si', 's.id', '=', 'si.sale_id')
                ->join('products as p', 'si.product_id', '=', 'p.id')
                ->select(
                    'p.id as product_id',
                    'p.name',
                    'p.description',
                    'p.product_type',
                    'p.is_splittable',
                    'p.items_per_unit',
                    'p.roll_length',
                    DB::raw('SUM(si.quantity) as total_quantity'),
                    DB::raw('SUM(si.total) as total_cost'),
                    DB::raw('COUNT(DISTINCT s.id) as consumption_count')
                )
                ->where('s.store_id', $storeId)
                ->where('s.sale_type', 'internal_use')
                ->where(function ($query) {
                    $query->whereNull('s.description')
                        ->orWhere('s.description', '!=', 'manual_invoice_entry');
                })
                ->whereBetween('s.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->groupBy('p.id', 'p.name', 'p.description', 'p.product_type', 'p.is_splittable', 'p.items_per_unit', 'p.roll_length');

            if ($orderBy === 'total_quantity') {
                $query->orderByDesc('total_quantity');
            } elseif ($orderBy === 'name') {
                $query->orderBy('p.name');
            } else {
                $query->orderByDesc('total_cost');
            }

            $totalProducts = (clone $query)->count();
            $totalPages = max(1, (int) ceil($totalProducts / $perPage));

            $topProducts = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(function($item) use ($totalStats) {
                    $item->total_quantity = (float)$item->total_quantity;
                    $item->total_cost = (float)$item->total_cost;
                    $item->consumption_count = (int)$item->consumption_count;
                    $item->percentage = $totalStats['total_cost'] > 0
                        ? round(($item->total_cost / $totalStats['total_cost']) * 100, 1)
                        : 0;

                    if ($item->product_type === 'fractional') {
                        $item->unit = 'متر';
                        $item->unit_display = 'متر';
                        $item->display_text = number_format($item->total_quantity, 2) . ' متر';
                        if ($item->roll_length && $item->roll_length > 0) {
                            $totalRolls = $item->total_quantity / $item->roll_length;
                            $item->display_subtext = '≈ ' . number_format($totalRolls, 2) . ' رول';
                        }
                    } elseif ($item->is_splittable) {
                        $item->unit = 'طقم';
                        $item->unit_display = 'طقم/حبة';
                        $item->display_text = number_format($item->total_quantity, 2) . ' طقم';
                    } else {
                        $item->unit = 'قطعة';
                        $item->unit_display = 'قطعة';
                        $item->display_text = number_format($item->total_quantity, 0) . ' قطعة';
                    }

                    return $item;
                });

            return response()->json([
                'success' => true,
                'total_stats' => $totalStats,
                'top_products' => $topProducts,
                'total_pages' => $totalPages,
                'current_page' => $page,
            ]);
        } catch (\Exception $e) {
            \Log::error('Internal use report error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب بيانات التقرير',
            ], 500);
        }
    }

    /**
     * جلب تفاصيل منتج معين (للمالك - AJAX)
     */
    public function productDetails(Request $request, $storeId)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);

        $store = DB::table('stores')->where('id', $storeId)->where('user_id', $user->id)->first();
        if (!$store) return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $history = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.store_id', $storeId)
            ->where('sales.sale_type', 'internal_use')
            ->where(function ($query) {
                $query->whereNull('sales.description')
                    ->orWhere('sales.description', '!=', 'manual_invoice_entry');
            })
            ->where('sale_items.product_id', $request->product_id)
            ->whereBetween('sales.created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59'])
            ->select(
                'sale_items.id',
                'sale_items.quantity',
                'sale_items.total',
                'sale_items.unit_type',
                'sales.created_at',
                'sales.internal_notes',
                'products.product_type',
                'products.is_splittable',
                'products.items_per_unit',
                'products.roll_length'
            )
            ->orderByDesc('sales.created_at')
            ->get()
            ->map(function($item) {
                $item->date = date('Y-m-d H:i', strtotime($item->created_at));
                $item->notes = $item->internal_notes ?? '-';

                $unitType = $item->unit_type ?? null;

                if ($item->product_type === 'fractional') {
                    if ($unitType === 'roll') {
                        $item->unit_display = 'رول';
                        $item->details = number_format($item->quantity, 3) . ' رول';
                    } else {
                        $item->unit_display = 'متر';
                        $item->details = number_format($item->quantity, 2) . ' متر';
                    }
                } elseif ($item->is_splittable) {
                    if ($unitType === 'piece') {
                        $item->unit_display = 'حبة';
                        $item->details = number_format($item->quantity, 0) . ' حبة';
                    } else {
                        $item->unit_display = 'طقم';
                        $item->details = number_format($item->quantity, 0) . ' طقم';
                    }
                } else {
                    $item->unit_display = 'قطعة';
                    $item->details = number_format($item->quantity, 0) . ' قطعة';
                }

                return $item;
            });

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * تصدير تقرير الاستهلاك الداخلي بصيغة PDF (للمالك)
     */
    public function exportPDF(Request $request, $storeId)
    {
        $user = Auth::user();
        if (!$user) abort(401);

        $store = DB::table('stores')
            ->where('id', $storeId)
            ->where('user_id', $user->id)
            ->first();

        if (!$store) {
            abort(403, 'غير مصرح لك بالوصول لهذا المتجر');
        }

        $month = max(1, min(12, (int) $request->get('month', now()->month)));
        $year = max(2020, min(2100, (int) $request->get('year', now()->year)));

        $reportData = $this->buildMonthlyConsumptionData($storeId, $month, $year);

        $data = [
            'store' => $store,
            'month' => $month,
            'year' => $year,
            'reportData' => $reportData,
            'generatedAt' => now()->format('Y-m-d H:i'),
        ];

        $pdf = ArabicPdf::loadView('pdf.internal_use_pdf', $data);

        return $pdf->setOption('encoding', 'utf-8')
            ->setOption('margin-bottom', 10)
            ->download("Consumption_Report_{$year}_{$month}.pdf");
    }

    private function buildMonthlyConsumptionData(int $storeId, int $month, int $year): array
    {
        $period = now()->setYear($year)->setMonth($month);
        $startDate = $period->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $period->copy()->endOfMonth()->format('Y-m-d');

        $internalUseSales = DB::table('sales as s')
            ->leftJoin('sale_items as si', 's.id', '=', 'si.sale_id')
            ->leftJoin('products as p', 'si.product_id', '=', 'p.id')
            ->where('s.store_id', $storeId)
            ->where('s.sale_type', 'internal_use')
            ->where(function ($query) {
                $query->whereNull('s.description')
                    ->orWhere('s.description', '!=', 'manual_invoice_entry');
            })
            ->whereBetween('s.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                's.id',
                's.total as amount',
                's.internal_notes as description',
                's.created_at',
                's.accountant_id',
                DB::raw('MIN(p.name) as product_name'),
                DB::raw('MIN(si.quantity) as raw_quantity'),
                $this->supportsSaleItemUnitType() ? DB::raw('MIN(si.unit_type) as raw_unit_type') : DB::raw("'default' as raw_unit_type")
            )
            ->groupBy('s.id', 's.total', 's.internal_notes', 's.created_at', 's.accountant_id')
            ->get()
            ->map(function ($item) {
                return [
                    'source' => $item->accountant_id
                        ? 'استهلاك المحاسب (خصم من المخزون)'
                        : 'استهلاك داخلي (سجل قديم/غير محدد)',
                    'type' => $item->product_name ?: 'استهلاك داخلي',
                    'description' => $item->description ?: '-',
                    'amount' => (float) $item->amount,
                    'created_at' => $item->created_at,
                    'entry_type' => 'accountant_internal_use',
                    'entry_id' => (int) $item->id,
                    'raw_quantity' => (float) ($item->raw_quantity ?? 0),
                    'raw_unit_type' => $item->raw_unit_type ?: 'default',
                ];
            });

        $ownerPurchases = DB::table('purchases')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('id', 'purchase_name', 'description', 'cost', 'created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'source' => 'مشتريات المالك للاستهلاك',
                    'type' => $item->purchase_name ?: 'مشتريات استهلاك',
                    'description' => $item->description ?: '-',
                    'amount' => (float) $item->cost,
                    'created_at' => $item->created_at,
                    'entry_type' => 'owner_purchase',
                    'entry_id' => (int) $item->id,
                ];
            });

        $records = $internalUseSales
            ->concat($ownerPurchases)
            ->sortByDesc('created_at')
            ->values();

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'records' => $records,
            'summary' => [
                'accountant_total' => (float) $internalUseSales->filter(fn ($item) => $item['source'] === 'استهلاك المحاسب (خصم من المخزون)')->sum('amount'),
                'owner_total' => (float) $ownerPurchases->sum('amount'),
                'grand_total' => (float) $records->sum('amount'),
                'count' => (int) $records->count(),
            ],
        ];
    }


    private function supportsSaleItemUnitType(): bool
    {
        static $hasUnitType = null;

        if ($hasUnitType === null) {
            $hasUnitType = Schema::hasColumn('sale_items', 'unit_type');
        }

        return $hasUnitType;
    }

    /**
     * حساب الكمية الفعلية للخصم من المخزون
     */
    private function calculateActualDeduction($product, $quantity, $unitType)
    {
        // تحويل موحد عبر الموديل نفسه حتى يبقى منطق:
        // متر/رول/حبة/طقم متطابقاً مع مسارات التوريد وإدارة المخزون.
        return $product->normalizeQuantityByUnit($quantity, $unitType);
    }

    /**
     * الحصول على اسم الوحدة للعرض
     */
    private function getUnitName($unitType)
    {
        return match($unitType) {
            'meters' => 'متر',
            'roll' => 'رول',
            'piece' => 'حبة',
            'kit' => 'طقم',
            default => 'وحدة',
        };
    }

    private function buildOwnerPurchaseGroupsFromRecords($records)
    {
        return collect($records)
            ->filter(fn ($row) => ($row['source'] ?? '') === 'مشتريات المالك للاستهلاك')
            ->groupBy(function ($row) {
                return $this->normalizeOwnerPurchaseType((string) ($row['type'] ?? ''));
            })
            ->map(function ($group, $name) {
                return [
                    'name' => $name,
                    'count' => $group->count(),
                    'total' => (float) $group->sum('amount'),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    private function normalizeOwnerPurchaseType(string $type): string
    {
        $value = trim(mb_strtolower($type));

        if ($value === '') {
            return 'مشتريات أخرى';
        }

        foreach ($this->ownerPurchaseGroupingMap() as $keywords => $label) {
            foreach (explode('|', $keywords) as $keyword) {
                if ($keyword !== '' && mb_strpos($value, $keyword) !== false) {
                    return $label;
                }
            }
        }

        return $type;
    }

    private function ownerPurchaseGroupingMap(): array
    {
        return [
            'امواس|مواس' => 'أمواس',
            'ربل|روبل|رابر' => 'ربل',
            'تضليل|تظليل' => 'تظليل',
            'تجليد|تغليف' => 'تجليد',
            'مرشات|مرش' => 'مرشات',
            'حماية انوار|حمايه انوار|حماية أنوار|انوار' => 'حماية أنوار',
            'شطرطون|تيب|لاصق' => 'شطرطون / لواصق',
            'فيوز|فيوزات' => 'فيوز',
        ];
    }

    /**
     * البحث عن المنتجات (للمحاسب فقط)
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q');
        $accountant = Auth::guard('accountant')->user();
        $webUser = Auth::guard('web')->user();

        $storeId = null;

        if ($accountant) {
            $storeId = $accountant->store_id;
        } elseif ($webUser) {
            $routeStore = $request->route('store');
            $storeId = is_object($routeStore) ? $routeStore->id : (int) $routeStore;

            if (!$storeId || !$webUser->stores()->whereKey($storeId)->exists()) {
                return response()->json([]);
            }
        } else {
            return response()->json([]);
        }

        $products = Product::where('store_id', $storeId)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('barcode', 'LIKE', "%{$query}%");
            })
            ->select(
                'id',
                'name',
                'cost_price',
                'barcode',
                'description',
                'product_type',
                'is_splittable',
                'items_per_unit',
                'roll_length',
                'piece_price'
            )
            ->get();

        return response()->json($products);
    }
}
