<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employee_logs', function (Blueprint $table) {

            // 1) حذف الـ FOREIGN KEY القديم
            try {
                $table->dropForeign('employee_logs_employee_id_foreign');
            } catch (\Exception $e) {}

            // 2) حذف الـ INDEX القديم
            try {
                $table->dropIndex('employee_logs_employee_id_foreign');
            } catch (\Exception $e) {}

            // 3) حذف الأعمدة القديمة
            foreach ([
                'loggable_id',
                'loggable_type',
                'type',
                'amount',
                'added_by',
                'logged_at',
                'category',
            ] as $col) {
                if (Schema::hasColumn('employee_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down()
    {
        Schema::table('employee_logs', function (Blueprint $table) {

            // إعادة الأعمدة فقط لو احتجت rollback
            $table->unsignedBigInteger('loggable_id')->nullable();
            $table->string('loggable_type')->nullable();
            $table->string('type')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamp('logged_at')->nullable();
            $table->string('category')->default('operation')->nullable();
        });
    }
};
