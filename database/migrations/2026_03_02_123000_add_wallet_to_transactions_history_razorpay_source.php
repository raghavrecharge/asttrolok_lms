<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'wallet' to the source ENUM column in transactions_history_razorpay
        DB::statement("ALTER TABLE transactions_history_razorpay MODIFY COLUMN source ENUM('webhook', 'callback', 'wallet') DEFAULT 'callback'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reverting might fail if 'wallet' data already exists
        DB::statement("ALTER TABLE transactions_history_razorpay MODIFY COLUMN source ENUM('webhook', 'callback') DEFAULT 'callback'");
    }
};
