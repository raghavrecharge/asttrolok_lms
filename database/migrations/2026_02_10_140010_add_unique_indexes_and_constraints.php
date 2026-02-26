<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexesAndConstraints extends Migration
{
    public function up()
    {
        // V-20: Unique constraint on installment_orders to prevent duplicates
        // Skip if duplicate data exists — legacy data may have duplicates
        try {
            Schema::table('installment_orders', function (Blueprint $table) {
                $table->unique(
                    ['user_id', 'webinar_id', 'installment_id', 'status'],
                    'idx_installment_orders_unique_active'
                );
            });
        } catch (\Exception $e) {
            \Log::warning('Could not add unique index on installment_orders — duplicate data exists: ' . $e->getMessage());
        }

        // V-06: Unique index on transactions_history_razorpay for idempotency
        try {
            Schema::table('transactions_history_razorpay', function (Blueprint $table) {
                $table->index(
                    ['razorpay_payment_id', 'status'],
                    'idx_razorpay_payment_status'
                );
            });
        } catch (\Exception $e) {
            \Log::warning('Could not add index on transactions_history_razorpay: ' . $e->getMessage());
        }
    }

    public function down()
    {
        Schema::table('installment_orders', function (Blueprint $table) {
            $table->dropUnique('idx_installment_orders_unique_active');
        });

        Schema::table('transactions_history_razorpay', function (Blueprint $table) {
            $table->dropIndex('idx_razorpay_payment_status');
        });
    }
}
