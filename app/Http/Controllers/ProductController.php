<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Models\Accountant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Store $store, Request $request)
    {
        $query = $store->products()->with('category:id,name');

        // بحث بالاسم والوصف فقط
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('barcode', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // فلترة حسب القسم
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ترتيب المنتجات بحيث تظهر المنتجات منخفضة المخزون أولاً
        $query->orderByRaw("CASE WHEN product_type = 'fractional' AND roll_length > 0 THEN ((quantity / roll_length) <= min_stock) ELSE (quantity <= min_stock) END DESC")
              ->orderBy('quantity', 'asc');

        // Pagination
        $products = $query->paginate(20)->withQueryString();

        // إحصائيات سريعة (بعد نفس الفلاتر المطبقة)
        $statsQuery = Product::where('store_id', $store->id);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $statsQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('barcode', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('category_id')) {
            $statsQuery->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $statsQuery->where('status', $request->status);
        }

        $stats = $statsQuery->selectRaw('
            COUNT(*) as total_count,
            SUM(
                CASE
                    WHEN product_type = "fractional" AND roll_length > 0 THEN (quantity / roll_length) * COALESCE(cost_price, 0)
                    ELSE quantity * COALESCE(cost_price, 0)
                END
            ) as total_cost,
            SUM(
                CASE
                    WHEN product_type = "fractional" AND roll_length > 0 THEN (quantity / roll_length) * price
                    ELSE quantity * price
                END
            ) as total_value
        ')->first();

        $stats->low_stock_count = (clone $statsQuery)
            ->lowStock()
            ->count();

        // عدد المحذوفات
        $trashedCount = Product::onlyTrashed()
            ->where('store_id', $store->id)
            ->count();

        // الأقسام
        $categories = Category::where('store_id', $store->id)->get();

        return view('user.stores.products.index', compact(
            'store',
            'products',
            'categories',
            'trashedCount',
            'stats',
        ));
    }
// ]دالة البحث في صفحة المنتجتات للمحاسب
    public function indexPos(Request $request)
{
    // الحصول على المحاسب المسجل دخوله
    $accountant = Auth::guard('accountant')->user();

    // جلب المتجر المرتبط بالمحاسب
    $store = $accountant->store;

    // التحقق من وجود متجر مرتبط
    if (!$store) {
        abort(404, 'لم يتم تعيين متجر لهذا المحاسب');
    }

    // ✅ جلب المنتجات مع جميع الحقول المهمة والعلاقات
    $query = $store->products()
        ->with([
            'category' => function($q) {
                $q->select('id', 'name', 'is_main_category');
            },
            'fractions' => function($q) {
                $q->select('id', 'product_id', 'option_label', 'price', 'deduction_value');
            }
        ])
        ->select([
            'id', 'name', 'description', 'barcode', 'price', 'cost_price', 'piece_price',
            'quantity', 'min_stock', 'status', 'category_id', 'image', 'created_at',
            'product_type', 'is_splittable', 'items_per_unit', 'roll_length', 'waste_percentage'
        ]);

    // بحث بالاسم والوصف فقط
    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
        });
    }

    // فلترة حسب القسم
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    // فلترة حسب الحالة
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('low_stock') && $request->low_stock == '1') {
        $query->lowStock();
    }

    // فلترة حسب توفر الصورة
    if ($request->filled('has_image') && $request->has_image == '1') {
        $query->whereNotNull('image');
    }

    // ترتيب بحيث تظهر المنتجات منخفضة المخزون أولاً
    $query->orderByRaw("CASE WHEN product_type = 'fractional' AND roll_length > 0 THEN ((quantity / roll_length) <= min_stock) ELSE (quantity <= min_stock) END DESC")
          ->orderBy('quantity', 'asc')
          ->orderBy('name', 'asc');

    // الترقيم
    $perPage = max(1, min((int) $request->get('per_page', 15), 100));
    $products = $query->paginate($perPage)->withQueryString();

    // ✅✅✅ معالجة البيانات بعد الجلب - بشكل صحيح وكامل
    foreach ($products as $product) {
        // تحويل الأسعار إلى أرقام عشرية
        $product->price = (float) $product->price;
        $product->cost_price = (float) ($product->cost_price ?? 0);
        $product->piece_price = (float) ($product->piece_price ?? 0);

        // ✅✅✅ تحديد نوع المنتج بشكل صحيح جداً
        $product->is_fractional = ($product->product_type === 'fractional');
        $product->is_set = ($product->is_splittable == 1 && $product->items_per_unit > 0);
        $product->is_normal = (!$product->is_fractional && !$product->is_set);

        // ✅ حساب الكمية المعروضة حسب نوع المنتج
        if ($product->is_fractional) {
            // منتج رول
            if ($product->roll_length > 0) {
                $product->display_rolls = $product->quantity / $product->roll_length;
                $product->display_quantity = number_format($product->display_rolls, 2);
                $product->display_unit = 'رول';
                $product->display_min_stock = number_format($product->min_stock, 2);
                $product->total_meters = number_format($product->quantity, 2);
                $product->low_stock = $product->display_rolls <= $product->min_stock;
                $product->meter_price = number_format($product->price / $product->roll_length, 2);
            } else {
                $product->display_quantity = number_format($product->quantity, 2);
                $product->display_unit = 'متر';
                $product->display_min_stock = number_format($product->min_stock, 2);
                $product->total_meters = $product->display_quantity;
                $product->low_stock = $product->quantity <= $product->min_stock;
                $product->meter_price = number_format($product->price, 2);
            }
        } elseif ($product->is_set) {
            // منتج طقم ✅✅✅ هذا هو المهم لموضوعك
            $itemsPerUnit = $product->items_per_unit ?: 1;
            $product->total_sets = $product->quantity;
            $product->total_pieces = $product->total_sets * $itemsPerUnit;
            $product->display_quantity = number_format($product->total_sets, 0);
            $product->display_unit = 'طقم';
            $product->display_min_stock = number_format($product->min_stock, 0);
            $product->low_stock = $product->total_sets <= $product->min_stock;

            // ✅ سعر الحبة المفردة - مهم جداً
            $product->piece_price_display = number_format($product->piece_price, 0);

            // ✅ سعر الطقم كاملاً
            $product->set_price_display = number_format($product->price, 0);
        } else {
            // منتج عادي
            $product->display_quantity = number_format($product->quantity, 0);
            $product->display_unit = 'قطعة';
            $product->display_min_stock = number_format($product->min_stock, 0);
            $product->low_stock = $product->quantity <= $product->min_stock;
        }

        // ✅ حساب القيم الإجمالية بشكل متوافق مع عرض المنتجات الكسرية
        $valueQuantity = $product->quantity;

        if ($product->product_type === 'fractional' && $product->roll_length > 0) {
            $valueQuantity = $product->quantity / $product->roll_length;
        }

        $product->total_cost = $product->cost_price * $valueQuantity;
        $product->total_value = $product->price * $valueQuantity;

        // معالجة fractions إذا وجدت
        if ($product->fractions && $product->fractions->count() > 0) {
            $product->fractions->map(function($f) {
                $f->price = (float) $f->price;
                $f->deduction_value = (float) $f->deduction_value;
                return $f;
            });
        }
    }

    // جلب الأقسام الخاصة بالمتجر
    $categories = $store->categories()
        ->select('id', 'name', 'is_main_category')
        ->orderBy('is_main_category', 'desc')
        ->orderBy('name', 'asc')
        ->get();

    return view('accountants.pos.searchProduct', compact(
        'store',
        'products',
        'categories',
        'accountant'
    ));
}
    private function showProducts(Store $store, Request $request, $accountant)
    {
        // بدء الاستعلام
        $query = Product::where('store_id', $store->id)
            ->with(['category' => function($q) use ($store) {
                $q->select('id', 'name', 'is_main_category');
            }])
            ->select([
                'id', 'name', 'description', 'price', 'cost_price',
                'quantity', 'min_stock', 'barcode', 'image',
                'status', 'category_id', 'created_at'
            ]);

        // بحث بالاسم والوصف فقط
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('description', 'LIKE', '%' . $search . '%');
            });
        }

        // فلترة حسب القسم
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فلترة حسب حالة المخزون
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'good':
                    $query->where(function ($q) {
                        $q->where(function ($fractional) {
                            $fractional->where('product_type', 'fractional')
                                ->where('roll_length', '>', 0)
                                ->whereRaw('(quantity / roll_length) > min_stock');
                        })->orWhere(function ($normal) {
                            $normal->where(function ($inner) {
                                $inner->where('product_type', '!=', 'fractional')
                                    ->orWhere('roll_length', '<=', 0)
                                    ->orWhereNull('roll_length');
                            })->whereRaw('quantity > min_stock');
                        });
                    });
                    break;
                case 'low':
                    $query->where('quantity', '>', 0)
                          ->lowStock();
                    break;
                case 'critical':
                    $query->where('quantity', '<=', 0);
                    break;
            }
        }

        // الترتيب
        $query->orderByRaw("
            CASE
                WHEN quantity <= 0 THEN 1
                WHEN product_type = 'fractional' AND roll_length > 0 AND ((quantity / roll_length) <= min_stock) THEN 2
                WHEN (product_type != 'fractional' OR roll_length <= 0 OR roll_length IS NULL) AND quantity <= min_stock THEN 2
                ELSE 3
            END ASC
        ")
        ->orderBy('name', 'asc');

        // Pagination
        $perPage = max(1, min((int) $request->get('per_page', 15), 100));
        $products = $query->paginate($perPage)->withQueryString();

        // الحصول على الأقسام
        $categories = Category::where('store_id', $store->id)
            ->select('id', 'name', 'is_main_category')
            ->orderBy('is_main_category', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return view('accountant.pos.products.index', compact(
            'store',
            'products',
            'categories',
            'accountant'
        ));
    }

    public function exportCsv(Store $store)
    {
        $filename = 'products-store-' . $store->id . '-' . now()->format('Ymd_His') . '.csv';

        $products = $store->products()
            ->with(['category:id,name', 'fractions:id,product_id,option_label,deduction_value,price'])
            ->orderBy('name')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->streamDownload(function () use ($products) {
            $out = fopen('php://output', 'w');
            fwrite($out, "ï»¿");

            fputcsv($out, [
                'category_name',
                'product_name',
                'barcode',
                'description',
                'status',
                'min_stock',
                'sale_price',
                'cost_price',
                'quantity',
                'product_type',
                'is_splittable',
                'items_per_unit',
                'piece_price',
                'roll_length',
                'waste_percentage',
                'fractions_json',
            ]);

            foreach ($products as $product) {
                [$salePrice, $costPrice] = $this->normalizeTransferPrices($product->price, $product->cost_price);

                $fractionsJson = $product->fractions->isEmpty()
                    ? ''
                    : json_encode($product->fractions->map(fn ($fraction) => [
                        'option_label' => $fraction->option_label,
                        'deduction_value' => (float) $fraction->deduction_value,
                        'price' => (float) $fraction->price,
                    ])->values()->all(), JSON_UNESCAPED_UNICODE);

                fputcsv($out, [
                    $product->category->name ?? 'بدون قسم',
                    $product->name,
                    $product->barcode,
                    $product->description,
                    $product->status,
                    (float) ($product->min_stock ?? 0),
                    $salePrice,
                    $costPrice,
                    0, // شرط النقل: الكمية دائماً صفر
                    $product->product_type ?? 'standard',
                    $product->is_splittable ? 1 : 0,
                    (int) ($product->items_per_unit ?? 1),
                    (float) ($product->piece_price ?? 0),
                    (float) ($product->roll_length ?? 0),
                    (float) ($product->waste_percentage ?? 0),
                    $fractionsJson,
                ]);
            }

            fclose($out);
        }, $filename, $headers);
    }

    public function importCsv(Request $request, Store $store)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (! $handle) {
            return back()->with('error', 'تعذر قراءة ملف CSV.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return back()->with('error', 'الملف فارغ أو غير صالح.');
        }

        $header = array_map(function ($h) {
            $key = trim((string) $h);
            // معالجة حالة BOM في أول عمود حتى لا يفشل التحقق من category_name
            $key = preg_replace('/^\xEF\xBB\xBF/u', '', $key);
            return strtolower($key);
        }, $header);

        $requiredColumns = ['category_name', 'product_name', 'sale_price', 'cost_price'];
        foreach ($requiredColumns as $column) {
            if (! in_array($column, $header, true)) {
                fclose($handle);
                return back()->with('error', 'الملف لا يحتوي على العمود الإلزامي: ' . $column);
            }
        }

        $col = array_flip($header);
        $created = 0;
        $createdCategories = 0;
        $skipped = 0;
        $duplicates = 0;

        DB::transaction(function () use ($handle, $col, $store, &$created, &$createdCategories, &$skipped, &$duplicates) {
            while (($row = fgetcsv($handle)) !== false) {
                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                $name = trim((string) $this->csvValue($row, $col, 'product_name'));
                $categoryName = trim((string) $this->csvValue($row, $col, 'category_name'));

                if ($name === '' || $categoryName === '') {
                    $skipped++;
                    continue;
                }

                [$salePrice, $costPrice] = $this->normalizeTransferPrices(
                    $this->toNullableNumber($this->csvValue($row, $col, 'sale_price')),
                    $this->toNullableNumber($this->csvValue($row, $col, 'cost_price'))
                );

                $category = Category::firstOrCreate(
                    [
                        'store_id' => $store->id,
                        'name' => $categoryName,
                    ],
                    [
                        'user_id' => auth()->id(),
                        'slug' => $this->generateImportCategorySlug($store, $categoryName),
                        'status' => 'active',
                        'description' => null,
                        'is_main_category' => false,
                    ]
                );

                if ($category->wasRecentlyCreated) {
                    $createdCategories++;
                }

                $barcode = trim((string) $this->csvValue($row, $col, 'barcode')) ?: null;
                $product = $this->findProductForImport($store, $name, $barcode, $category->id);

                $productType = trim((string) $this->csvValue($row, $col, 'product_type'));
                $productType = in_array($productType, ['standard', 'fractional'], true) ? $productType : 'standard';

                $isSplittable = (int) $this->toNullableNumber($this->csvValue($row, $col, 'is_splittable')) === 1;
                $itemsPerUnit = max(1, (int) ($this->toNullableNumber($this->csvValue($row, $col, 'items_per_unit')) ?? 1));
                $piecePrice = (float) ($this->toNullableNumber($this->csvValue($row, $col, 'piece_price')) ?? 0);
                $rollLength = (float) ($this->toNullableNumber($this->csvValue($row, $col, 'roll_length')) ?? 0);
                $wastePercentage = (float) ($this->toNullableNumber($this->csvValue($row, $col, 'waste_percentage')) ?? 0);
                $minStock = (float) ($this->toNullableNumber($this->csvValue($row, $col, 'min_stock')) ?? 1);

                $payload = [
                    'store_id' => $store->id,
                    'user_id' => auth()->id(),
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => $product ? $product->slug : $this->generateImportProductSlug($store, $name),
                    'barcode' => $barcode,
                    'description' => $this->csvValue($row, $col, 'description'),
                    'status' => in_array($this->csvValue($row, $col, 'status'), ['active', 'inactive'], true)
                        ? $this->csvValue($row, $col, 'status')
                        : 'active',
                    'price' => $salePrice,
                    'cost_price' => $costPrice,
                    'quantity' => 0,
                    'min_stock' => $minStock,
                    'product_type' => $productType,
                    'is_splittable' => $productType === 'standard' ? $isSplittable : false,
                    'items_per_unit' => $productType === 'standard' && $isSplittable ? $itemsPerUnit : 1,
                    'piece_price' => $productType === 'standard' ? $piecePrice : 0,
                    'roll_length' => $productType === 'fractional' ? $rollLength : 0,
                    'waste_percentage' => $wastePercentage,
                ];

                if ($product) {
                    // حسب الطلب: المنتج الموجود مسبقاً لا يتم استيراده مرة أخرى
                    $duplicates++;
                    continue;
                }

                $product = Product::create($payload);
                $created++;

                // نقل خيارات المتر/القص إن وجدت للمنتجات الجديدة فقط
                $fractionsJson = $this->csvValue($row, $col, 'fractions_json');
                $fractions = $this->decodeFractions($fractionsJson);
                if ($productType === 'fractional' && ! empty($fractions)) {
                    foreach ($fractions as $fraction) {
                        $product->fractions()->create([
                            'option_label' => (string) ($fraction['option_label'] ?? ''),
                            'deduction_value' => (float) ($fraction['deduction_value'] ?? 0),
                            'price' => (float) ($fraction['price'] ?? 0),
                        ]);
                    }
                }
            }
        });

        fclose($handle);

        return redirect()->route('user.stores.products.index', $store->id)
            ->with('success', "تم استيراد CSV بنجاح: {$created} جديد، {$duplicates} مكرر تم تجاهله، {$createdCategories} أقسام جديدة، {$skipped} صفوف متجاوزة.");
    }

    public function create(Store $store, Request $request)
    {
        $categories = Category::where('store_id', $store->id)->get();

        // القسم المختار تلقائيًا
        $selectedCategory = $request->category_id;
        // أنواع المنتجات المتاحة
        $productTypes = [
            'standard' => 'منتج عادي (بالحبة)',
            'fractional' => 'منتج قابل للتجزئة (رول/قص)'
        ];

        return view('user.stores.products.create', compact(
            'store',
            'categories',
            'selectedCategory',
            'productTypes'
        ));
    }

    public function store(Request $request, Store $store)
    {
        // 1. التعديل في الـ Validation
        $request->validate([
            'name'             => 'required|string|max:255',
            'category_id'      => ['required', Rule::exists('categories', 'id')->where('store_id', $store->id)],
            'price'            => 'required|numeric|min:0',
            'cost_price'       => 'nullable|numeric|min:0',
            'quantity'         => 'required_if:product_type,standard|nullable|numeric|min:0',
            'min_stock'        => 'nullable|numeric|min:0',
            'description'      => 'nullable|string',
            'status'           => 'required|in:active,inactive',
            'image'            => 'nullable|image|max:2048',

            // الحقول الجديدة
            'product_type'     => 'required|in:standard,fractional',
            'waste_percentage' => 'nullable|numeric|min:0|max:100',
            'num_rolls'        => 'required_if:product_type,fractional|nullable|numeric|min:0',
            'roll_length'      => 'required_if:product_type,fractional|nullable|numeric|min:0',

            // حقول الأطقم
            'is_splittable'    => 'nullable|boolean',
            'items_per_unit'   => 'required_if:is_splittable,1|nullable|integer|min:1',
            'piece_price'      => 'nullable|numeric|min:0',
            'quick_sale_default_unit' => 'nullable|in:unit,piece',

            // التحقق من خيارات التجزئة
            'fractions'        => 'required_if:product_type,fractional|array',
            'fractions.*.option_label'    => 'required|string|max:255',
            'fractions.*.deduction_value' => 'required|numeric|min:0',
            'fractions.*.price'           => 'required|numeric|min:0',
        ]);

        $slug = $this->buildStoreScopedSlug($request->name, $store->id);

        // التفرد مطلوب داخل نفس المتجر فقط.
        // الـ slug نفسه يحمل معرف المتجر لتفادي التعارض مع القيد العالمي الحالي.
        if (Product::withTrashed()->where('store_id', $store->id)->where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'اسم المنتج موجود مسبقاً في هذا المتجر، يرجى اختيار اسم آخر'])->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // 2. حساب الكمية النهائية بناءً على النوع
        if ($request->product_type === 'fractional') {
            $finalQuantity = (float)$request->num_rolls * (float)$request->roll_length;
            $rollLength = $request->roll_length;
            $isSplittable = false;
        } else {
            $finalQuantity = $request->quantity;
            $rollLength = 0;
            $isSplittable = $request->has('is_splittable');
        }

        // 3. إنشاء المنتج الأساسي
        try {
            $product = Product::create([
                'store_id'         => $store->id,
                'user_id'          => auth()->id(),
                'category_id'      => $request->category_id,
                'name'             => $request->name,
                'slug'             => $slug,
                'description'      => $request->description,
                'price'            => $request->price,
                'cost_price'       => $request->cost_price,
                'quantity'         => $finalQuantity,
                'roll_length'      => $rollLength,
                'min_stock'        => $request->min_stock ?? 1,
                'status'           => $request->status,
                'image'            => $imagePath,
                'product_type'     => $request->product_type,
                'waste_percentage' => $request->waste_percentage ?? 0,
                // حقول الأطقم
                'is_splittable'    => $isSplittable,
                'items_per_unit'   => $isSplittable ? $request->items_per_unit : 1,
                'piece_price'      => $request->piece_price,
                'quick_sale_default_unit' => $request->has('is_splittable') ? ($request->quick_sale_default_unit ?? 'unit') : 'unit',
            ]);
        } catch (QueryException $e) {
            if ($this->isProductsSlugUniqueViolation($e)) {
                return back()->withErrors([
                    'name' => 'تعذر حفظ المنتج بسبب تكرار الاسم، فضلاً اختر اسمًا مختلفًا داخل المتجر.'
                ])->withInput();
            }

            throw $e;
        }

        // 4. حفظ خيارات التجزئة إذا كان المنتج fractional
        if ($request->product_type === 'fractional' && $request->has('fractions')) {
            foreach ($request->fractions as $fraction) {
                $product->fractions()->create([
                    'option_label'    => $fraction['option_label'],
                    'deduction_value' => $fraction['deduction_value'],
                    'price'           => $fraction['price'],
                ]);
            }
        }

        if ($request->has('stay_here')) {
            return redirect()
                ->route('user.stores.products.create', $store->id)
                ->with('success', 'تم إضافة المنتج بنجاح، يمكنك إضافة منتج آخر');
        }

        return redirect()
            ->route('user.stores.products.index', $store->id)
            ->with('success', 'تم إضافة المنتج بنجاح');
    }

    public function edit(Store $store, Product $product)
    {
        $this->ensureProductBelongsToStore($store, $product);

        $categories = Category::where('store_id', $store->id)->get();
        // جلب المنتج مع خيارات التجزئة المرتبطة به
        $product->load('fractions');
        return view('user.stores.products.edit', compact(
            'store',
            'product',
            'categories'
        ));
    }

    public function update(Request $request, Store $store, Product $product)
    {
        $this->ensureProductBelongsToStore($store, $product);

        $request->validate([
            'name'             => 'required|string|max:255',
            'category_id'      => ['required', Rule::exists('categories', 'id')->where('store_id', $store->id)],
            'price'            => 'required|numeric|min:0',
            'cost_price'       => 'nullable|numeric|min:0',
            'min_stock'        => 'nullable|integer|min:0',
            'status'           => 'required|in:active,inactive',
            'image'            => 'nullable|image|max:2048',
            'product_type'     => 'required|in:standard,fractional',
            'waste_percentage' => 'nullable|numeric|min:0|max:100',
            'roll_length'      => 'required_if:product_type,fractional|nullable|numeric|min:0',

            'is_splittable'    => 'nullable|boolean',
            'items_per_unit'   => 'required_if:is_splittable,1|nullable|integer|min:1',
            'piece_price'      => 'nullable|numeric|min:0',
            'quick_sale_default_unit' => 'nullable|in:unit,piece',

            'fractions'        => 'required_if:product_type,fractional|array',
            'fractions.*.option_label'    => 'required|string',
            'fractions.*.deduction_value' => 'required|numeric',
            'fractions.*.price'           => 'required|numeric',
        ]);
        // ملاحظة مهمة:
        // واجهة التعديل تعرض roll_length للمنتج الكَسري، لذلك يجب التحقق منه
        // وحفظه هنا فعلياً حتى لا تبقى الواجهة تعرض قيمة لا تنعكس في قاعدة البيانات.

        /*
         * عند تعديل التكلفة أو أي حقل آخر دون تغيير الاسم يجب إبقاء slug الحالي.
         * إعادة بنائه في كل حفظ كانت تسبب تعارضاً وهمياً لبعض المنتجات القديمة
         * التي تملك slug مختلفاً عن الصيغة الحالية المرتبطة بالمتجر.
         */
        $nameChanged = $product->name !== $request->name;
        $slug = $this->resolveProductSlugForUpdate($product, $request->name, $store->id);

        // لا نحتاج إلى فحص حجز الاسم إلا إذا غيّر المستخدم الاسم فعلياً.
        if ($nameChanged) {
            $exists = Product::withTrashed()
                ->where('store_id', $store->id)
                ->where('slug', $slug)
                ->where('id', '!=', $product->id)
                ->exists();

            if ($exists) {
                return back()
                    ->withErrors(['name' => "عذراً، اسم المنتج \"{$request->name}\" محجوز مسبقاً في هذا المتجر، يرجى اختيار اسم آخر."])
                    ->withInput();
            }
        }

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        try {
            $product->update([
                'category_id'      => $request->category_id,
                'name'             => $request->name,
                'slug'             => $slug,
                'description'      => $request->description,
                'price'            => $request->price,
                'cost_price'       => $request->cost_price,
                'min_stock'        => $request->min_stock ?? 1,
                'status'           => $request->status,
                'image'            => $imagePath,
                'product_type'     => $request->product_type,
                'waste_percentage' => $request->waste_percentage ?? 0,
                // نخزن طول الرول فقط للمنتجات الكَسرية.
                // أما إذا تغير النوع إلى standard فنصفر القيمة لتفادي بقاء بيانات fractional قديمة
                // تؤثر على حسابات العرض أو الربحية أو التوريد لاحقاً.
                'roll_length'      => $request->product_type === 'fractional'
                    ? (float) ($request->roll_length ?? 0)
                    : 0,

                'is_splittable'    => $request->has('is_splittable'),
                'items_per_unit'   => $request->has('is_splittable') ? $request->items_per_unit : 1,
                'piece_price'      => $request->piece_price,
                'quick_sale_default_unit' => $request->has('is_splittable') ? ($request->quick_sale_default_unit ?? 'unit') : 'unit',
            ]);
        } catch (QueryException $e) {
            if ($this->isProductsSlugUniqueViolation($e)) {
                return back()->withErrors([
                    'name' => 'تعذر تحديث المنتج بسبب تكرار الاسم، فضلاً اختر اسمًا مختلفًا داخل المتجر.'
                ])->withInput();
            }

            throw $e;
        }

        // معالجة الكسور (Fractions)
        if ($request->product_type === 'fractional') {
            $product->fractions()->delete();
            foreach ($request->fractions as $fraction) {
                $product->fractions()->create([
                    'option_label'    => $fraction['option_label'],
                    'deduction_value' => $fraction['deduction_value'],
                    'price'           => $fraction['price'],
                ]);
            }
        } else {
            $product->fractions()->delete();
        }

        return redirect()->route('user.stores.products.index', $store->id)
                         ->with('success', 'تم تحديث المنتج بنجاح');
    }

    public function destroy(Store $store, Product $product)
    {
        $this->ensureProductBelongsToStore($store, $product);

        $product->delete();

        return redirect()->route('user.stores.products.index', $store->id)
                         ->with('success', 'تم حذف المنتج');
    }

    public function trash(Store $store)
    {
        $products = Product::onlyTrashed()->where('store_id', $store->id)->get();
        return view('user.stores.products.trash', compact('store', 'products'));
    }

    public function restore(Store $store, $id)
    {
        $product = Product::onlyTrashed()
            ->where('store_id', $store->id)
            ->where('id', $id)
            ->firstOrFail();

        $product->restore();

        return redirect()->route('user.stores.products.trash', $store->id)
                         ->with('success', 'تم استرجاع المنتج');
    }

    public function forceDelete(Store $store, $id)
    {
        // جلب المنتج من المحذوفات
        $product = Product::onlyTrashed()
            ->where('store_id', $store->id)
            ->where('id', $id)
            ->firstOrFail();

        // 1. حذف الصورة من التخزين الفيزيائي قبل حذف السجل
        if ($product->image && \Storage::disk('public')->exists($product->image)) {
            \Storage::disk('public')->delete($product->image);
        }

        // 2. حذف خيارات التجزئة يدوياً
        $product->fractions()->delete();

        // 3. الحذف النهائي من جدول المنتجات
        $product->forceDelete();

        return redirect()->route('user.stores.products.trash', $store->id)
                         ->with('success', 'تم حذف المنتج وكافة خياراته نهائياً');
    }


    private function csvValue(array $row, array $col, string $key): ?string
    {
        if (! array_key_exists($key, $col)) {
            return null;
        }

        $index = $col[$key];
        return isset($row[$index]) ? trim((string) $row[$index]) : null;
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function toNullableNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function normalizeTransferPrices(?float $salePrice, ?float $costPrice): array
    {
        $hasSale = $salePrice !== null && $salePrice > 0;
        $hasCost = $costPrice !== null && $costPrice > 0;

        if ($hasSale && $hasCost) {
            return [$salePrice, $costPrice];
        }

        return [0, 0];
    }

    private function findProductForImport(Store $store, string $name, ?string $barcode, ?int $categoryId = null): ?Product
    {
        if ($barcode) {
            $byBarcode = Product::where('store_id', $store->id)->where('barcode', $barcode)->first();
            if ($byBarcode) {
                return $byBarcode;
            }
        }

        // حسب الطلب: الاعتماد على اسم المنتج كما هو بالضبط داخل نفس المتجر
        $byExactName = Product::where('store_id', $store->id)
            ->where('name', $name)
            ->first();

        if ($byExactName) {
            return $byExactName;
        }

        $candidateSlug = Str::slug($name);
        $candidateSlug = $candidateSlug !== '' ? $candidateSlug : str_replace(' ', '-', trim($name));

        return Product::where('store_id', $store->id)
            ->where('slug', $candidateSlug)
            ->first();
    }

    private function generateImportCategorySlug(Store $store, string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : str_replace(' ', '-', trim($name));
        $slug = $base;
        $counter = 1;

        while (Category::where('store_id', $store->id)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * يحافظ على slug المنتج عند تعديل بيانات لا تشمل الاسم، ويولّد قيمة جديدة
     * مرتبطة بالمتجر فقط عندما يتغير الاسم فعلياً.
     */
    protected function resolveProductSlugForUpdate(Product $product, string $name, int $storeId): string
    {
        if ($product->name === $name) {
            return (string) $product->slug;
        }

        return $this->buildStoreScopedSlug($name, $storeId);
    }

    /**
     * توليد slug مرتبط بالمتجر لتفادي الاصطدام مع القيد العالمي الحالي على عمود slug
     * مع الحفاظ على منطق التفرد داخل المتجر فقط.
     */
    private function buildStoreScopedSlug(string $name, int $storeId): string
    {
        $normalized = preg_replace('/\s+/u', '-', trim($name));
        $base = trim((string) $normalized, '-');

        if ($base === '') {
            $base = 'product';
        }

        return "{$base}-s{$storeId}";
    }

    private function isProductsSlugUniqueViolation(QueryException $e): bool
    {
        $errorInfo = $e->errorInfo ?? [];
        $sqlState = $errorInfo[0] ?? null;
        $driverCode = (string) ($errorInfo[1] ?? '');
        $message = (string) ($errorInfo[2] ?? $e->getMessage());

        return $sqlState === '23000'
            && $driverCode === '1062'
            && str_contains($message, 'products_slug_unique');
    }

    private function generateImportProductSlug(Store $store, string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : str_replace(' ', '-', trim($name));
        $slug = $base;
        $counter = 1;

        // products.slug فريد على مستوى الجدول بالكامل وليس على مستوى المتجر فقط
        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function decodeFractions(?string $json): array
    {
        if (! $json) {
            return [];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, function ($item) {
            return is_array($item)
                && ! empty($item['option_label'])
                && isset($item['deduction_value'], $item['price']);
        }));
    }

    private function ensureProductBelongsToStore(Store $store, Product $product): void
    {
        if ((int) $product->store_id !== (int) $store->id) {
            abort(403);
        }
    }

    public function toggleStatus(Store $store, Product $product)
    {
        $this->ensureProductBelongsToStore($store, $product);

        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();

        return redirect()->route('user.stores.products.index', $store->id)
                         ->with('success', 'تم تحديث حالة المنتج');
    }
}
