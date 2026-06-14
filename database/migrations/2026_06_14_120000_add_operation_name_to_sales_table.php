<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('operation_name', 500)->nullable()->after('description');
        });

        DB::table('sales')
            ->where(function ($query) {
                $query->where('description', 'like', '%تضليل%')
                    ->orWhere('description', 'like', '%تظليل%');
            })
            ->update(['operation_name' => DB::raw('description')]);
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('operation_name');
        });
    }
};
