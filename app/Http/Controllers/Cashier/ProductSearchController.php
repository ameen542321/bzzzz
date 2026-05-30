<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductSearchController extends Controller
{
  public function search(Request $request)
{
    $query = $request->query('query');

    // البحث عن المستخدم (محاسب أو مدير)
    $user = Auth::guard('accountant')->user() ?: Auth::guard('web')->user();

    if (!$user || !$user->store_id) {
        return response()->json([]);
    }

    // بداية الاستعلام
    $productsQuery = Product::with(['fractions' => function($q) {
            $q->select('id', 'product_id', 'option_label', 'price', 'deduction_value');
        }])
        ->where('store_id', $user->store_id)
        ->where('status', 'active');

    $searchTerm = null;
    $normalizedSearchTerm = null;

    // إذا كان المتغير موجود في الطلب
    if ($request->has('query')) {
        // إذا كانت القيمة مسافة أو فارغة أو نص بعد إزالة المسافات
        $trimmedQuery = trim($query);

        if ($trimmedQuery !== '') {
            // يوجد نص حقيقي للبحث
            $searchTerm = $trimmedQuery;
            $normalizedSearchTerm = $this->normalizeArabicSearchTerm($searchTerm);
            $normalizedNameSql = $this->normalizedArabicSql('name');
            $normalizedDescriptionSql = $this->normalizedArabicSql('description');

            $productsQuery->where(function ($q) use ($searchTerm, $normalizedSearchTerm, $normalizedNameSql, $normalizedDescriptionSql) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhereRaw("$normalizedNameSql LIKE ?", ['%' . $normalizedSearchTerm . '%'])
                  ->orWhereRaw("$normalizedDescriptionSql LIKE ?", ['%' . $normalizedSearchTerm . '%']);
            });
        }
    }

    // الترتيب: صلة الاسم أولاً عند البحث، أو الأكثر بيعاً عند العرض الافتراضي
    if ($searchTerm !== null) {
        $normalizedNameSql = $this->normalizedArabicSql('name');
        $normalizedDescriptionSql = $this->normalizedArabicSql('description');

        $productsQuery
            ->orderByRaw("CASE
                WHEN LOWER(name) = LOWER(?) THEN 0
                WHEN LOWER(name) LIKE LOWER(?) THEN 1
                WHEN LOWER(name) LIKE LOWER(?) THEN 2
                WHEN $normalizedNameSql = ? THEN 3
                WHEN $normalizedNameSql LIKE ? THEN 4
                WHEN $normalizedNameSql LIKE ? THEN 5
                WHEN $normalizedDescriptionSql LIKE ? THEN 6
                WHEN LOWER(description) LIKE LOWER(?) THEN 7
                ELSE 8
            END", [
                $searchTerm,
                $searchTerm . '%',
                '%' . $searchTerm . '%',
                $normalizedSearchTerm,
                $normalizedSearchTerm . '%',
                '%' . $normalizedSearchTerm . '%',
                '%' . $normalizedSearchTerm . '%',
                '%' . $searchTerm . '%',
            ]);
    } else {
        $productsQuery->withSum('saleItems as total_sold', 'quantity')
            ->orderByDesc('total_sold');
    }

    $limit = $searchTerm !== null ? 30 : 4;

    $products = $productsQuery
        ->orderByRaw("CASE WHEN product_type = 'fractional' AND roll_length > 0 THEN ((quantity / roll_length) <= 0) ELSE (quantity <= 0) END ASC")
        ->orderByRaw("CASE WHEN product_type = 'fractional' AND roll_length > 0 THEN ((quantity / roll_length) <= min_stock) ELSE (quantity <= min_stock) END ASC")
        ->orderBy('name', 'asc')
        ->limit($limit)
        ->get([
            'id', 'name', 'price', 'piece_price', 'description', 'updated_at',
            'product_type', 'quantity', 'min_stock',
            'waste_percentage', 'roll_length',
            'is_splittable', 'items_per_unit', 'quick_sale_default_unit'
        ])
        ->map(function($product) {
            // تحويل الأسعار لأرقام حديثة صريحة حتى لا تعتمد واجهة البيع السريع على قيم قديمة أو منسقة كنص.
            $product->price = (float) $product->price;
            $product->piece_price = (float) ($product->piece_price ?? 0);
            $product->price_label = number_format($product->price, 0) . ' ر.س';
            $product->piece_price_label = number_format($product->piece_price, 0) . ' ر.س';
            $product->price_updated_at = optional($product->updated_at)->toDateTimeString();

            // تحديد نوع المنتج
            $product->is_fractional = ($product->product_type === 'fractional');
            $product->is_set = ($product->is_splittable == 1 && $product->items_per_unit > 0);

            // حساب الكمية المعروضة حسب النوع
            $displayQuantity = (float) $product->quantity;

            if ($product->is_fractional) {
                if ($product->roll_length > 0) {
                    $displayQuantity = $product->quantity / $product->roll_length;
                    $product->display_quantity = number_format($displayQuantity, 2);
                    $product->display_unit = 'رول';
                    $product->display_min_stock = number_format($product->min_stock, 2);
                    $product->meter_price = number_format($product->price / $product->roll_length, 2);
                } else {
                    $product->display_quantity = number_format($displayQuantity, 2);
                    $product->display_unit = 'متر';
                    $product->display_min_stock = number_format($product->min_stock, 2);
                }
            } elseif ($product->is_set) {
                $product->display_quantity = number_format($displayQuantity, 2);
                $product->display_unit = 'طقم';
                $product->display_min_stock = number_format($product->min_stock, 2);
            } else {
                $product->display_quantity = number_format($displayQuantity, 2);
                $product->display_unit = 'قطعة';
                $product->display_min_stock = number_format($product->min_stock, 2);
            }

            // حالة المخزون
            $product->is_out_of_stock = $displayQuantity <= 0;
            $product->is_low_stock = !$product->is_out_of_stock && $displayQuantity <= $product->min_stock;

            return $product;
        });

    return response()
        ->json($products)
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
}

    private function normalizeArabicSearchTerm(string $value): string
    {
        return strtr(mb_strtolower(trim($value), 'UTF-8'), [
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ى' => 'ي',
            'ئ' => 'ي',
            'ؤ' => 'و',
            'ة' => 'ه',
        ]);
    }

    private function normalizedArabicSql(string $column): string
    {
        return "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($column, 'أ', 'ا'), 'إ', 'ا'), 'آ', 'ا'), 'ى', 'ي'), 'ئ', 'ي'), 'ؤ', 'و'), 'ة', 'ه'))";
    }

}
