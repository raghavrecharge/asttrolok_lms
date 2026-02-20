<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `upe_payment_requests` MODIFY COLUMN `request_type` ENUM(
            'offline_payment', 'refund', 'upgrade', 'adjustment',
            'restructure', 'manual_discount', 'subscription_cancel',
            'course_extension', 'post_purchase_coupon', 'installment_restructure'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `upe_payment_requests` MODIFY COLUMN `request_type` ENUM(
            'offline_payment', 'refund', 'upgrade', 'adjustment',
            'restructure', 'manual_discount', 'subscription_cancel'
        ) NOT NULL");
    }
};
