<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexesAndConstraints extends Migration
{
    public function up()
    {
        // V-20: Unique constraint on installment_orders to prevent duplicates
        Schema::table('installment_orders', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'webinar_id', 'installment_id', 'status'],
                'idx_installment_orders_unique_active'
            );
        });

        // V-06: Unique index on transactions_history_razorpay for idempotency
        Schema::table('transactions_history_razorpay', function (Blueprint $table) {
            $table->index(
                ['razorpay_payment_id', 'status'],
                'idx_razorpay_payment_status'
            );
        });
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
