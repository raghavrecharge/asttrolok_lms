<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE sub_step_installments MODIFY COLUMN status ENUM('pending','approved','paid','rejected','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE sub_step_installments MODIFY COLUMN status ENUM('pending','approved','paid','rejected') NOT NULL DEFAULT 'pending'");
    }
};
