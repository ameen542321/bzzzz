<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('employee_withdrawals', function (Blueprint $table) {
        // حذف الـ FK القديم إذا كان موجودًا
        try {
            $table->dropForeign('employee_withdrawals_employee_id_foreign');
        } catch (\Exception $e) {
            // تجاهل الخطأ إذا لم يكن موجودًا
        }
    });
}

public function down()
{
    Schema::table('employee_withdrawals', function (Blueprint $table) {
        // لا نعيد الـ FK لأنه غير مناسب للنظام polymorphic
    });
}

};
