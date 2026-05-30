<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up() {
        // 1. تعديل أنواع الحقول باستخدام Native SQL لتجنب الحاجة لمكتبة DBAL
        DB::statement('ALTER TABLE products MODIFY COLUMN quantity DECIMAL(15, 2) DEFAULT 0.00');
        DB::statement('ALTER TABLE stock_movements MODIFY COLUMN quantity DECIMAL(15, 2) NOT NULL');

        // 2. إضافة الحقول الجديدة لجدول المنتجات
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'product_type')) {
                $table->string('product_type')->default('standard')->after('category_id');
            }
            if (!Schema::hasColumn('products', 'waste_percentage')) {
                $table->decimal('waste_percentage', 5, 2)->default(0)->after('product_type');
            }
        });
    }

    public function down() {
        // للرجوع عن التعديلات إذا لزم الأمر
        DB::statement('ALTER TABLE products MODIFY COLUMN quantity INT(11)');
        DB::statement('ALTER TABLE stock_movements MODIFY COLUMN quantity INT(11)');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'waste_percentage']);
        });
    }
};
