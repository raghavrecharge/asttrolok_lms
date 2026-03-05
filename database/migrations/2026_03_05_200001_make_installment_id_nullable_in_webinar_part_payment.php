<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `webinar_part_payment` MODIFY COLUMN `installment_id` bigint(20) unsigned NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `webinar_part_payment` MODIFY COLUMN `installment_id` bigint(20) unsigned NOT NULL");
    }
};
