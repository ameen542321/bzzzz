<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * عرض الأقسام الخاصة بمتجر معين
     */
    public function index(Store $store)
    {
        $categories = Category::where('store_id', $store->id)->get();
        $trashCount = Category::onlyTrashed()
            ->where('store_id', $store->id)
            ->count();

        return view('user.stores.categories.index', compact('store', 'trashCount', 'categories'));
    }

    /**
     * صفحة إضافة قسم أو نشاط
     */
    public function create(Store $store, Request $request)
    {
        $is_main_category = $request->get('is_main_category', 0);
        return view('user.stores.categories.create', compact('store', 'is_main_category'));
    }

    /**
     * حفظ قسم جديد أو نشاط جديد
     */
    public function store(Request $request, Store $store)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'required|in:active,inactive',
            'is_main_category' => 'required|boolean',
        ]);

        Category::create([
            'name'             => $request->name,
            'description'      => $request->description,
            'status'           => $request->status,
            'store_id'         => $store->id,
            'user_id'          => auth()->id(),
            'is_main_category' => $request->is_main_category,
        ]);

        return redirect()
            ->route('user.stores.categories.index', $store->id)
            ->with('success', 'تم إضافة القسم بنجاح');
    }

    /**
     * صفحة تعديل قسم أو نشاط (تشمل خيار النقل)
     */
    public function edit(Store $store, Category $category)
    {
        if ($category->store_id != $store->id) {
            abort(403);
        }

        $is_main_category = $category->is_main_category;

        return view('user.stores.categories.edit', compact('store', 'category', 'is_main_category'));
    }

    /**
     * تحديث القسم أو النشاط + منطق النقل لمتجر آخر
     */
    public function update(Request $request, Store $store, Category $category)
    {
        if ($category->store_id != $store->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'target_store_id' => [
                'nullable',
                Rule::exists('stores', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())),
                Rule::notIn([$store->id]),
            ],
            'move_products' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $category, $validated) {
            $category->update([
                'name' => $validated['name'],
                'description' => $request->description,
                'status' => $validated['status'],
                'is_main_category' => $request->is_main_category ?? $category->is_main_category,
            ]);

            if (!empty($validated['target_store_id'])) {
                $targetStoreId = (int) $validated['target_store_id'];

                DB::table('categories')
                    ->where('id', $category->id)
                    ->update(['store_id' => $targetStoreId]);

                if ($request->boolean('move_products')) {
                    DB::table('products')
                        ->where('category_id', $category->id)
                        ->update(['store_id' => $targetStoreId]);
                }
            }
        });

        if (!empty($validated['target_store_id'])) {
            $transferMessage = $request->boolean('move_products')
                ? 'تم نقل القسم والمنتجات إلى المتجر الجديد بنجاح'
                : 'تم نقل القسم إلى المتجر الجديد بنجاح';

            return redirect()
                ->route('user.stores.categories.index', $validated['target_store_id'])
                ->with('success', $transferMessage);
        }

        return redirect()
            ->route('user.stores.categories.index', $store->id)
            ->with('success', 'تم تحديث بيانات القسم بنجاح');
    }

    /**
     * عرض الأقسام المحذوفة
     */
    public function trash(Store $store)
    {
        $categories = Category::onlyTrashed()
            ->where('store_id', $store->id)
            ->get();

        return view('user.stores.categories.trash', compact('store', 'categories'));
    }

    /**
     * استرجاع قسم محذوف
     */
    public function restore(Store $store, $id)
    {
        $category = Category::onlyTrashed()
            ->where('store_id', $store->id)
            ->where('id', $id)
            ->firstOrFail();

        $category->restore();

        return redirect()
            ->route('user.stores.categories.trash', $store->id)
            ->with('success', 'تم استرجاع القسم بنجاح');
    }

    /**
     * حذف نهائي
     */
    public function forceDelete(Store $store, $id)
    {
        $category = Category::onlyTrashed()
            ->where('store_id', $store->id)
            ->where('id', $id)
            ->firstOrFail();

        DB::transaction(function () use ($store, $category) {
            // عند الحذف النهائي للقسم: نحذف جميع المنتجات التابعة له نهائيًا كذلك
            $products = Product::withTrashed()
                ->where('store_id', $store->id)
                ->where('category_id', $category->id)
                ->get();

            foreach ($products as $product) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $product->fractions()->delete();
                $product->forceDelete();
            }

            $category->forceDelete();
        });

        return redirect()
            ->route('user.stores.categories.trash', $store->id)
            ->with('success', 'تم حذف القسم نهائيًا مع جميع المنتجات التابعة له');
    }

    /**
     * تفعيل/تعطيل القسم
     */
    public function toggleStatus(Store $store, Category $category)
    {
        if ($category->store_id != $store->id) {
            abort(403);
        }

        $newStatus = $category->status === 'active' ? 'inactive' : 'active';
        $category->update(['status' => $newStatus]);

        if ($newStatus === 'inactive') {
            $category->products()->update(['status' => 'inactive']);
        }

        return redirect()
            ->route('user.stores.categories.index', $store->id)
            ->with('success', 'تم تحديث حالة القسم');
    }

    /**
     * حذف القسم (Soft Delete)
     */
    public function destroy(Store $store, Category $category)
    {
        if ($category->store_id != $store->id) {
            abort(403);
        }

        DB::transaction(function () use ($store, $category) {
            // حذف أولي: نقل القسم والمنتجات التابعة له إلى سلة المحذوفات
            Product::where('store_id', $store->id)
                ->where('category_id', $category->id)
                ->delete();

            $category->delete();
        });

        return redirect()
            ->route('user.stores.categories.index', $store->id)
            ->with('success', 'تم حذف القسم ونقل منتجاته إلى سلة المحذوفات');
    }
}
