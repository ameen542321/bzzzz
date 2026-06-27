<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accountant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('daily_balance_id')->nullable()->constrained()->nullOnDelete();
            $table->date('business_date')->index();
            $table->unsignedTinyInteger('shift_number')->default(1);
            $table->timestamp('scheduled_start')->nullable();
            $table->timestamp('scheduled_end')->nullable();
            $table->timestamp('actual_start')->nullable();
            $table->timestamp('actual_end')->nullable();
            $table->string('status')->default('closed')->index();
            $table->string('closure_action')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'business_date', 'shift_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_shifts');
    }
};
