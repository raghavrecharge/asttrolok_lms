<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE upe_ledger_entries MODIFY COLUMN payment_method ENUM(
            'cash', 'bank_transfer', 'razorpay', 'paypal', 'stripe',
            'payment_link', 'wallet', 'system', 'coupon'
        ) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE upe_ledger_entries MODIFY COLUMN payment_method ENUM(
            'cash', 'bank_transfer', 'razorpay', 'paypal', 'stripe',
            'payment_link', 'wallet', 'system'
        ) NULL");
    }
};
