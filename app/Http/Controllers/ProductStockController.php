<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductStockController extends Controller
{
    /**
     * صفحة إدارة المخزون
     */
    public function index(Store $store, Product $product)
    {
        $this->ensureProductBelongsToStore($store, $product);

        $movements = $product->stockMovements()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.stores.products.stock.index', compact('store', 'product', 'movements'));
    }

    /**
     * زيادة المخزون
     */
    public function increase(Request $request, Store $store, Product $product)
    {
        $this->ensureProductBelongsToStore($store, $product);

        $request->validate([
            'quantity'  => 'required|numeric|min:0.001',
            'unit_type' => 'nullable|in:unit,piece', // إضافة نوع الوحدة (طقم أم حبة)
            'note'      => 'nullable|string|max:255',
        ]);

        // نجعل الموديل هو المرجع الوحيد لتحويل الوحدة أثناء الزيادة
        $product->increaseStock(
            (float) $request->quantity,
            $request->note,
            auth()->id(),
            (string) ($request->unit_type ?: 'unit')
        );

        return back()->with('success', 'تمت زيادة المخزون بنجاح');
    }

    /**
     * خصم المخزون
     */
    public function decrease(Request $request, Store $store, Product $product)
    {
        $this->ensureProductBelongsToStore($store, $product);

        $request->validate([
            'quantity'  => 'required|numeric|min:0.01',
            'unit_type' => 'nullable|in:unit,piece', // إضافة نوع الوحدة للخصم
            'note'      => 'nullable|string|max:255',
        ]);

        $rawQuantity = (float) $request->quantity;
        $unitType = (string) ($request->unit_type ?: 'unit');

        // نستخدم نفس دالة التحويل المركزية الموجودة في الموديل
        // حتى لا يختلف التحقق المسبق عن الخصم الفعلي لاحقاً.
        $actualAmountToDeduct = $product->normalizeQuantityByUnit($rawQuantity, $unitType);

        if ($actualAmountToDeduct > (float) $product->getRawOriginal('quantity')) {
             return back()->withErrors(['quantity' => 'الكمية المتوفرة لا تكفي']);
        }

        $product->decreaseStock($rawQuantity, $request->note, auth()->id(), $unitType);

        return back()->with('success', 'تم خصم الكمية من المخزن بنجاح');
    }


    private function ensureProductBelongsToStore(Store $store, Product $product): void
    {
        if ((int) $product->store_id !== (int) $store->id) {
            abort(404);
        }
    }

}
