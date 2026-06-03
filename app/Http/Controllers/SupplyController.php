<?php
namespace App\Http\Controllers;
use App\Models\{Product, Store};

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\StockMovement;
use Throwable;

class SupplyController extends Controller {
    public function index(Store $store, Request $request) {
        $type = $request->get('type', 'all');

        $productsQuery = Product::where('store_id', $store->id)
            ->addSelect([
                'last_supply_note' => StockMovement::query()
                    ->select('note')
                    ->whereColumn('product_id', 'products.id')
                    ->where('type', 'increase')
                    ->latest('id')
                    ->limit(1),
            ]);

        if ($type === 'fractional') {
            $productsQuery->where('product_type', 'fractional');
        } elseif ($type === 'splittable') {
            $productsQuery->where('is_splittable', 1);
        } elseif ($type === 'normal') {
            $productsQuery->where(function ($query) {
                $query->where('product_type', '!=', 'fractional')
                    ->where(function ($inner) {
                        $inner->where('is_splittable', 0)
                            ->orWhereNull('is_splittable');
                    });
            });
        }

        // التحقق من وجود البحث
        if ($request->has('search') && !empty($request->search)) {
            // إذا كان هناك بحث - نجلب جميع المنتجات المطابقة للاسم فقط
            $products = $productsQuery
                ->where('name', 'LIKE', '%' . $request->search . '%')
                ->orderBy('quantity', 'asc')
                ->get();
        } else {
            // إذا لا يوجد بحث - نجلب المنتجات المنخفضة عبر scope موحد لتسهيل توحيد المنطق لاحقاً
            $products = $productsQuery
                ->lowStock()
                ->orderBy('quantity', 'asc')
                ->get();
        }

        return view('user.stores.supply.index', compact('store', 'products'));
    }

    public function showModal(Store $store, Product $product) {
        $this->ensureProductBelongsToStore($store, $product);

        return response()->json(['success' => true, 'html' => view('user.stores.supply.modal', compact('store', 'product'))->render()]);
    }

    public function store(Request $request, Store $store, Product $product) {
        $this->ensureProductBelongsToStore($store, $product);

        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'purchase_price' => 'nullable|numeric|min:0',
            'unit_type' => 'nullable|in:unit,roll,meter,piece',
            'notes' => 'nullable|string|max:1000',
        ]);

        $unitType = (string) ($request->unit_type ?: 'unit');
        $priceProvided = $request->filled('purchase_price');
        $newPrice = $priceProvided
            ? $this->normalizeSupplyCostPrice($product, (float) $request->purchase_price, $unitType)
            : (float) $product->cost_price;

        try {
            if (!$priceProvided || abs($newPrice - (float) $product->cost_price) <= 0.01) {
                $this->applySupply($product, (float)$request->quantity, $newPrice, $unitType, $request->notes);
                return response()->json([
                    'success' => true,
                    'product' => $this->buildProductPayload($product),
                ]);
            }
        } catch (QueryException $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $this->formatSupplyQueryException($e, 'حفظ التوريد'),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ داخلي أثناء حفظ التوريد. حاول مرة أخرى، وإذا استمر الخطأ فراجع سجل النظام.',
            ], 500);
        }

        return response()->json(['success' => true, 'needs_confirmation' => true, 'current_price' => $product->cost_price, 'new_price' => $newPrice, 'data' => $request->only(['quantity', 'purchase_price', 'unit_type', 'notes'])]);
    }

    public function confirmSupply(Request $request, Store $store, Product $product) {
        $this->ensureProductBelongsToStore($store, $product);

        $request->validate([
            'action' => 'required|in:approve,cancel',
            'quantity' => 'required|numeric|min:0.01',
            'purchase_price' => 'nullable|numeric|min:0',
            'unit_type' => 'nullable|in:unit,roll,meter,piece',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            if ($request->action === 'approve') {
                $unitType = (string) ($request->unit_type ?: 'unit');
                $approvedPrice = $request->filled('purchase_price')
                    ? $this->normalizeSupplyCostPrice($product, (float) $request->purchase_price, $unitType)
                    : (float) $product->cost_price;
                $this->applySupply($product, (float)$request->quantity, $approvedPrice, $unitType, $request->notes);
            }
        } catch (QueryException $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $this->formatSupplyQueryException($e, 'تأكيد التوريد'),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ داخلي أثناء تأكيد التوريد. حاول مرة أخرى، وإذا استمر الخطأ فراجع سجل النظام.',
            ], 500);
        }

        if ($request->action === 'cancel') {
            return response()->json([
                'success' => true,
                'updated' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'updated' => true,
            'product' => $this->buildProductPayload($product),
        ]);
    }

    private function ensureProductBelongsToStore(Store $store, Product $product): void
    {
        if ($product->store_id !== $store->id) {
            abort(404);
        }
    }

    private function formatSupplyQueryException(QueryException $e, string $action): string
    {
        $driverMessage = trim((string) ($e->errorInfo[2] ?? $e->getMessage()));
        $driverMessageLower = strtolower($driverMessage);

        if ($driverMessage === '') {
            return "تعذر {$action} بسبب خطأ في قاعدة البيانات.";
        }

        if (str_contains($driverMessageLower, 'data too long for column')) {
            if (preg_match("/data too long for column '([^']+)'/i", $driverMessage, $matches)) {
                return "تعذر {$action} لأن قيمة الحقل '{$matches[1]}' أطول من المسموح في الجدول.";
            }

            return "تعذر {$action} لأن إحدى القيم المدخلة أطول من المسموح في الجدول.";
        }

        if (str_contains($driverMessageLower, 'cannot be null')) {
            if (preg_match("/column '([^']+)' cannot be null/i", $driverMessage, $matches)) {
                return "تعذر {$action} لأن الحقل '{$matches[1]}' مطلوب في قاعدة البيانات لكنه أُرسل فارغًا.";
            }

            return "تعذر {$action} لأن أحد الحقول المطلوبة في قاعدة البيانات أُرسل فارغًا.";
        }

        if (str_contains($driverMessageLower, 'out of range value for column')) {
            if (preg_match("/out of range value for column '([^']+)'/i", $driverMessage, $matches)) {
                return "تعذر {$action} لأن قيمة الحقل '{$matches[1]}' خارج النطاق المسموح في الجدول.";
            }

            return "تعذر {$action} لأن إحدى القيم خارج النطاق المسموح في الجدول.";
        }

        if (str_contains($driverMessageLower, 'incorrect decimal value')) {
            if (preg_match("/incorrect decimal value: .* for column '([^']+)'/i", $driverMessage, $matches)) {
                return "تعذر {$action} لأن الحقل '{$matches[1]}' يحتاج رقمًا عشريًا صحيحًا.";
            }

            return "تعذر {$action} لأن إحدى القيم الرقمية المرسلة غير صالحة.";
        }

        if (str_contains($driverMessageLower, 'foreign key constraint fails')) {
            return "تعذر {$action} بسبب تعارض مرجعي في قاعدة البيانات بين المنتج أو المتجر أو المستخدم وسجل الحركة.";
        }

        if (str_contains($driverMessageLower, 'duplicate entry')) {
            return "تعذر {$action} لأن هناك قيمة مكررة تصطدم بقيد فريد في قاعدة البيانات.";
        }

        return "تعذر {$action}: {$driverMessage}";
    }


    private function normalizeSupplyCostPrice($product, float $inputPrice, string $unitType): float
    {
        if ($product->product_type === 'fractional' && $unitType === 'meter' && (float) $product->roll_length > 0) {
            // عند التوريد بالمتر، السعر المدخل هو سعر المتر ونحفظ cost_price دائماً كسعر الرول الكامل.
            return $inputPrice * (float) $product->roll_length;
        }

        return $inputPrice;
    }

    private function applySupply($product, $qty, $price, string $unitType = 'unit', ?string $notes = null) {
        DB::transaction(function () use ($product, $qty, $price, $unitType, $notes) {
            // نمرر الكمية الخام ونوع الوحدة كما أُدخلا من الواجهة،
            // والموديل يتولى تحويلها لوحدة المخزون الأساسية بشكل موحد.
            $supplyNote = trim((string) $notes) !== ''
                ? 'بيان الموزع: ' . trim((string) $notes)
                : 'إضافة مخزون';

            $product->increaseStock((float) $qty, $supplyNote, auth()->id(), $unitType);

            // تحديث سعر التكلفة مباشرة على العمود المطلوب فقط بدون تشغيل events
            // أو أي سلوك عام في موديل المنتج مثل التعامل مع slug.
            Product::query()
                ->whereKey($product->getKey())
                ->update([
                    'cost_price' => $price,
                    'updated_at' => now(),
                ]);

            $product->cost_price = $price;
        });
    }

    private function buildProductPayload(Product $product): array
    {
        $product->refresh();

        return [
            'id' => $product->id,
            'quantity' => (float) $product->quantity,
            'cost_price' => (float) $product->cost_price,
            'last_supply_note' => (string) (StockMovement::query()
                ->where('product_id', $product->id)
                ->where('type', 'increase')
                ->latest('id')
                ->value('note') ?? ''),
        ];
    }
}
