<?php

namespace App\Http\Controllers\Cashier;

use App\Helpers\LogHelper;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Employee;
use App\Models\SaleItem;
use App\Models\CreditSale;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class QuickSaleController extends Controller
{
    public function index()
    {
        return view('cashier.quick-sale.index');
    }

    public function creditPersons()
    {
        $storeId = auth('accountant')->user()->store_id;

        return \App\Models\Employee::where('store_id', $storeId)
            ->select('id', 'name')
            ->get();
    }

    public function submit(Request $request)
{
    // ✅ تسجيل بداية العملية
    Log::info('🚀 بداية عملية بيع جديدة', [
        'sale_type' => $request->sale_type,
        'labor_total' => $request->labor_total,
        'paid_amount' => $request->paid_amount,
        'has_partial_credit' => $request->has_partial_credit,
        'mixed_cash' => $request->mixed_cash,
        'mixed_card' => $request->mixed_card,
        'employee_id' => $request->employee_id,
        'operation_date' => $request->operation_date ?? $request->fixed_operation_date ?? $request->sale_date ?? $request->business_date ?? $request->date,
    ]);

    $validated = $request->validate([
        'items'         => 'required|json',
        'labor_total'   => 'required|numeric|min:0',
        'tax_rate'      => 'required|integer|in:0,15',
        'paid_amount'   => 'required|numeric|min:0',
        'sale_type'     => 'required|in:cash,card,credit,mixed',
        'employee_id'   => 'nullable|exists:employees,id',
        'description'   => 'nullable|string|max:500',
        'has_invoice'   => 'nullable|in:0,1',
        'has_partial_credit' => 'nullable|in:0,1',
        'mixed_cash'    => 'nullable|numeric|min:0',
        'mixed_card'    => 'nullable|numeric|min:0',
        'debt_amount'   => 'nullable|numeric|min:0',
        'operation_date' => 'nullable|date|before_or_equal:today',
        'fixed_operation_date' => 'nullable|date|before_or_equal:today',
        'sale_date' => 'nullable|date|before_or_equal:today',
        'business_date' => 'nullable|date|before_or_equal:today',
        'date' => 'nullable|date|before_or_equal:today',
    ]);

    DB::beginTransaction();

    try {
        $accountant = auth('accountant')->user();
        $storeId = $accountant->store_id;
        $userId = $accountant->user_id;
        $operationDate = $request->operation_date
            ?? $request->fixed_operation_date
            ?? $request->sale_date
            ?? $request->business_date
            ?? $request->date;
        $operationTimestamp = $operationDate
            ? Carbon::parse($operationDate)->setTimeFrom(now())
            : now();

        $items = json_decode($request->items, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('❌ خطأ في JSON', ['error' => json_last_error_msg()]);
            throw new \Exception('تنسيق بيانات المنتجات غير صحيح');
        }

        Log::info('📦 المنتجات المرسلة', ['items_count' => count($items)]);

        // استخراج تفاصيل الدفع المختلط إذا وجدت
        $mixedCash = (float) ($request->mixed_cash ?? 0);
        $mixedCard = (float) ($request->mixed_card ?? 0);

        if ($request->sale_type === 'mixed') {
            // fallback للتوافق الخلفي: إذا لم تصل القيم المباشرة نقرأها من العنصر المؤقت
            if ($mixedCash <= 0 && $mixedCard <= 0) {
                $lastItem = end($items);
                if (isset($lastItem['_temp']) && isset($lastItem['payment_details'])) {
                    $mixedCash = (float) ($lastItem['payment_details']['cash'] ?? 0);
                    $mixedCard = (float) ($lastItem['payment_details']['card'] ?? 0);
                }
            }

            Log::info('💳 تفاصيل الدفع المختلط', [
                'mixed_cash' => $mixedCash,
                'mixed_card' => $mixedCard
            ]);

            // إزالة عنصر الدفع المؤقت
            $items = array_filter($items, function($item) {
                return !isset($item['_temp']);
            });

            // إعادة ترتيب المصفوفة
            $items = array_values($items);
        }

        $totalProfit = 0;
        $productsTotal = 0;
        $productErrors = [];
        $itemsWithDeductions = [];

        foreach ($items as $index => $item) {
            $product = Product::where('id', $item['product_id'])->where('store_id', $storeId)->first();
            if (!$product) {
                Log::warning('⚠️ منتج غير موجود', ['product_id' => $item['product_id']]);
                continue;
            }

            $fractionId = $item['fraction_id'] ?? null;
            $saleUnit = $item['sale_unit'] ?? 'unit';
            $isCustom = ($fractionId === 'custom');
            $quantityToDecrement = 0;
            $customMeters = null;

            // --- منطق الخصم المطور (أمتار + أطقم + عادي) ---

            // 1. إذا كان منتج رول (مجزأ)
            if ($product->product_type === 'fractional') {
                if ($isCustom) {
                    $customMeters = abs((float) ($item['custom_consumption'] ?? 0));
                    $quantityToDecrement = $product->calculateFinalDeduction($customMeters, 'custom');
                } elseif ($fractionId && $fractionId !== '0') {
                    $fraction = $product->fractions()->find($fractionId);
                    if ($fraction) {
                        // خيارات التجزئة في الواجهة تُدخل كاستهلاك فعلي بالمتر، وليست كنسبة من طول الرول.
                        $quantityToDecrement = $product->calculateFinalDeduction($fraction->deduction_value, 'meter');
                    }
                } else {
                    $quantityToDecrement = (float) $item['quantity'];
                }
            }
            // 2. إذا كان نظام أطقم وتم البيع "بالحبة"
            elseif ($product->is_splittable && $saleUnit === 'piece') {
                $quantityToDecrement = (float) $item['quantity'] / ($product->items_per_unit ?: 1);
            }
            // 3. المنتج العادي أو الطقم الكامل
            else {
                $quantityToDecrement = (float) $item['quantity'];
            }

            // التحقق من المخزون
            if (round($product->quantity, 4) < round($quantityToDecrement, 4)) {
                $productErrors[] = "{$product->name}: المخزون غير كافٍ. المتوفر: " . number_format($product->quantity, 3);
                Log::warning('⚠️ مخزون غير كاف', [
                    'product' => $product->name,
                    'available' => $product->quantity,
                    'required' => $quantityToDecrement
                ]);
                continue;
            }

            $productsTotal += (float) $item['total'];
            $costPerBaseUnit = (float) ($product->cost_price ?? 0);
            $itemCostTotal = $costPerBaseUnit * $quantityToDecrement;
            $totalProfit += ($item['total'] - $itemCostTotal);

            $itemsWithDeductions[] = [
                'product' => $product,
                'quantity_to_subtract' => $quantityToDecrement,
                'note' => ($product->is_splittable && $saleUnit === 'piece') ? "بيع حبة من طقم (عدد {$item['quantity']})" : ($isCustom ? "قص مخصص: $customMeters متر" : "بيع عادي"),
                'row_data' => [
                    'product_id'          => $item['product_id'],
                    'fraction_id'         => ($isCustom || !$fractionId) ? null : $fractionId,
                    'is_custom'           => $isCustom,
                    'custom_name'         => $isCustom ? ($item['custom_name'] ?? 'مخصص') : null,
                    'custom_consumption'  => $quantityToDecrement,
                    'custom_meters'       => $customMeters,
                    'roll_length_at_sale' => $product->roll_length,
                    'quantity'            => $item['quantity'],
                    'price'               => $item['price'],
                    'total'               => $item['total'],
                ]
            ];
        }

        if (!empty($productErrors)) {
            DB::rollBack();
            Log::warning('⛔ عملية ملغاة بسبب نقص المخزون', ['errors' => $productErrors]);
            return redirect()->back()->with('error', implode('<br>', $productErrors))->withInput();
        }

        $taxValue = ($productsTotal * $request->tax_rate) / 100;
        $finalTotal = round(($productsTotal + $taxValue) + $request->labor_total);

        // تحديد المبلغ المدفوع حسب نوع البيع
        $paidAmount = round($request->paid_amount);
        $cashAmount = 0;
        $cardAmount = 0;

        if ($request->sale_type === 'mixed') {
            $cashAmount = round($mixedCash);
            $cardAmount = round($mixedCard);
            $paidAmount = $cashAmount + $cardAmount;
        } elseif ($request->sale_type === 'cash') {
            $cashAmount = $paidAmount;
        } elseif ($request->sale_type === 'card') {
            $cardAmount = $paidAmount;
        }

        $remaining = max(0, $finalTotal - $paidAmount);

        // التحقق من صحة الدفع
        $hasPartialCredit = $request->has_partial_credit == 1;

        // قيمة المديونية اليدوية (للآجل الجزئي) مستقلة عن إجمالي الفاتورة والمبلغ المستلم.
        $manualDebtAmount = round((float) ($request->debt_amount ?? 0));

        Log::info('💰 تفاصيل المبالغ', [
            'products_total' => $productsTotal,
            'tax_value' => $taxValue,
            'labor_total' => $request->labor_total,
            'final_total' => $finalTotal,
            'paid_amount' => $paidAmount,
            'cash_amount' => $cashAmount,
            'card_amount' => $cardAmount,
            'remaining' => $remaining,
            'has_partial_credit' => $hasPartialCredit
        ]);

        if (!$hasPartialCredit && $paidAmount < $finalTotal) {
            DB::rollBack();
            $typeName = $this->getSaleTypeName($request->sale_type);
            Log::warning('⛔ عملية ملغاة: مبلغ مدفوع أقل من الإجمالي', [
                'paid' => $paidAmount,
                'final' => $finalTotal
            ]);
            return redirect()->back()
                ->with('error', "خطأ: المبلغ المدفوع ($paidAmount ريال) أقل من قيمة الفاتورة ($finalTotal ريال) في البيع $typeName")
                ->withInput();
        }

        if ($hasPartialCredit && !$request->employee_id) {
            DB::rollBack();
            Log::warning('⛔ عملية ملغاة: آجل جزئي بدون موظف');
            return redirect()->back()
                ->with('error', 'يجب اختيار الموظف لتسجيل المبلغ المتبقي كآجل')
                ->withInput();
        }

        if ($hasPartialCredit && in_array($request->sale_type, ['cash', 'card']) && $manualDebtAmount <= 0) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'يرجى إدخال قيمة المديونية للآجل الجزئي')
                ->withInput();
        }

        if ($hasPartialCredit && in_array($request->sale_type, ['cash', 'card']) && ($paidAmount + $manualDebtAmount) < $finalTotal) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', "خطأ محاسبي: مجموع المستلم ({$paidAmount}) + المديونية ({$manualDebtAmount}) أقل من إجمالي الفاتورة ({$finalTotal})")
                ->withInput();
        }

        $totalProfit += $request->labor_total;

        // إنشاء سجل البيع مع احترام تاريخ العملية المثبت في واجهة البيع السريع.
        $sale = new Sale([
            'store_id'         => $storeId,
            'accountant_id'    => $accountant->id,
            'employee_id'      => $hasPartialCredit ? $request->employee_id : ($request->sale_type === 'credit' ? $request->employee_id : null),
            'products_total'   => $productsTotal,
            'tax_rate'         => $request->tax_rate,
            'labor_total'      => $request->labor_total,
            'final_total'      => $finalTotal,
            'total'            => $finalTotal,
            'paid_amount'      => $paidAmount,
            'cash_amount'      => $cashAmount,
            'card_amount'      => $cardAmount,
            'remaining_amount' => $remaining,
            'sale_type'        => $request->sale_type,
            'has_partial_credit' => $hasPartialCredit,
            'has_invoice'      => $request->has_invoice == 1,
            'description'      => trim($request->description),
            'profit'           => $totalProfit,
        ]);
        $sale->created_at = $operationTimestamp;
        $sale->updated_at = $operationTimestamp;
        $sale->save();

        Log::info('✅ تم إنشاء البيع', ['sale_id' => $sale->id]);

        // تسجيل المديونية إذا وجدت
        $debtToRecord = ($hasPartialCredit && in_array($request->sale_type, ['cash', 'card']) && $manualDebtAmount > 0)
            ? $manualDebtAmount
            : $remaining;

        if ($debtToRecord > 0 && $request->employee_id) {
            $credit = CreditSale::create([
                'person_id'        => $request->employee_id,
                'person_type'      => 'App\\Models\\Employee',
                'store_id'         => $storeId,
                // 'sale_id'          => $sale->id,
                'amount'           => $debtToRecord,
                'remaining_amount' => $debtToRecord,
                'description'      => "مديونية من فاتورة رقم #" . $sale->id . ($hasPartialCredit ? " (آجل جزئي)" : ""),
                'date'             => $operationTimestamp->format('Y-m-d'),
                'status'           => 'pending',
                'month'            => $operationTimestamp->format('m-Y'),
                'added_by'         => $accountant->id,
            ]);

            Log::info('📝 تم تسجيل المديونية', [
                'credit_id' => $credit->id,
                'amount' => $debtToRecord,
                'employee_id' => $request->employee_id
            ]);

            $employee = Employee::find($request->employee_id);
            if ($employee) {
                LogHelper::add(
                    'employee_debt',
                    "قام المحاسب {$accountant->name} بإضافة مديونية بقيمة {$debtToRecord} ريال على الموظف {$employee->name} من فاتورة #{$sale->id}",
                    $storeId
                );
            }
        }

        // تنفيذ الخصم الفعلي وتسجيل الحركة
        foreach ($itemsWithDeductions as $itemData) {
            $saleItem = $sale->items()->create($itemData['row_data']);

            $itemData['product']->decrement('quantity', $itemData['quantity_to_subtract']);

            $itemData['product']->stockMovements()->create([
                'store_id'   => $storeId,
                'user_id'    => $userId,
                'product_id' => $itemData['product']->id,
                'type'       => 'decrease',
                'quantity'   => $itemData['quantity_to_subtract'],
                'note'       => $itemData['note'] . " (مبيعات POS #{$sale->id})",
            ]);

            Log::info('📦 تم خصم المخزون', [
                'product' => $itemData['product']->name,
                'quantity' => $itemData['quantity_to_subtract']
            ]);
        }

        DB::commit();

        // رسالة النجاح المناسبة
        $successMessage = $this->getSuccessMessage($request, $finalTotal, $paidAmount, $remaining, $hasPartialCredit, $cashAmount, $cardAmount, $debtToRecord);

        Log::info('🎉 تمت العملية بنجاح', ['sale_id' => $sale->id]);

        return ($request->has_invoice == 1)
            ? redirect()->route('accountant.quick-sale.invoice.create', ['sale' => $sale->id])->with('success', $successMessage)
            : redirect()->back()->with('success', $successMessage);

    } catch (\Exception $e) {
        DB::rollBack();

        // ✅ تسجيل تفاصيل الخطأ كاملة
        Log::error('🔥 خطأ في عملية البيع', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'request_data' => [
                'sale_type' => $request->sale_type,
                'labor_total' => $request->labor_total,
                'paid_amount' => $request->paid_amount,
                'has_partial_credit' => $request->has_partial_credit,
                'mixed_cash' => $request->mixed_cash,
                'mixed_card' => $request->mixed_card,
                'employee_id' => $request->employee_id,
                'debt_amount' => $request->debt_amount,
            ]
        ]);

        return redirect()->back()->with('error', 'حدث خطأ تقني: ' . $e->getMessage())->withInput();
    }
}

    private function getSuccessMessage($request, $finalTotal, $paidAmount, $remaining, $hasPartialCredit, $cashAmount, $cardAmount, $debtToRecord = 0)
    {
        $saleTypeName = $this->getSaleTypeName($request->sale_type);

        // حالة الدفع المختلط
        if ($request->sale_type === 'mixed') {
            $message = "✅ تم البيع المختلط بنجاح. نقداً: $cashAmount ريال، شبكة: $cardAmount ريال";
            if ($hasPartialCredit && $remaining > 0) {
                $employeeName = 'غير محدد';
                if ($request->employee_id) {
                    $employee = Employee::find($request->employee_id);
                    $employeeName = $employee ? $employee->name : 'غير محدد';
                }
                $message .= "، والمتبقي ($remaining ريال) كآجل على الموظف: $employeeName";
            }
            return $message;
        }

        // حالة الدفع النقدي أو الشبكة مع آجل جزئي
        if ($hasPartialCredit && ($remaining > 0 || $debtToRecord > 0)) {
            $employeeName = 'غير محدد';
            if ($request->employee_id) {
                $employee = Employee::find($request->employee_id);
                $employeeName = $employee ? $employee->name : 'غير محدد';
            }
            $debtAmount = $debtToRecord > 0 ? $debtToRecord : $remaining;
            return "✅ تم البيع {$saleTypeName} بنجاح. المبلغ المدفوع: {$paidAmount} ريال، والمديونية المسجلة ({$debtAmount} ريال) على الموظف: {$employeeName}";
        }

        // حالة البيع الآجل الكامل
        if ($request->sale_type === 'credit') {
            $employeeName = 'غير محدد';
            if ($request->employee_id) {
                $employee = Employee::find($request->employee_id);
                $employeeName = $employee ? $employee->name : 'غير محدد';
            }
            $debtAmount = $remaining > 0 ? $remaining : $finalTotal;
            return "✅ تم البيع الآجل بنجاح وتسجيل مديونية بمبلغ ({$debtAmount} ريال) على الموظف: {$employeeName}";
        }

        // حالة الدفع الكامل (كاش أو شبكة)
        return "✅ تمت عملية البيع {$saleTypeName} بنجاح وتحديث المخزون.";
    }

    private function getSaleTypeName($type)
    {
        $types = ['cash' => 'نقدي', 'card' => 'شبكة', 'credit' => 'آجل', 'mixed' => 'مختلط'];
        return $types[$type] ?? $type;
    }
}
