<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    DB::statement("
        ALTER TABLE notifications
        MODIFY COLUMN channel
        ENUM('site', 'push', 'both', 'CARLED')
        NOT NULL
    ");
}

public function down()
{
    DB::statement("
        ALTER TABLE notifications
        MODIFY COLUMN channel
        ENUM('site', 'push', 'both')
        NOT NULL
    ");
}

};
