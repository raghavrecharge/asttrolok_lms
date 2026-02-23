<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions_history_razorpay MODIFY COLUMN payment_type ENUM('subscription','webinar','cart','consultation','meeting','bundle','product','installment','part','subscription_one_time','quick_pay') DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transactions_history_razorpay MODIFY COLUMN payment_type ENUM('subscription','webinar','cart','consultation','meeting','bundle','product','installment','part','subscription_one_time') DEFAULT NULL");
    }
};
