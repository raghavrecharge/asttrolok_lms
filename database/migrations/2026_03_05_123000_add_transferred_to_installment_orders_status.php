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
        // Add 'transferred' to the status ENUM column in installment_orders
        DB::statement("ALTER TABLE installment_orders MODIFY COLUMN status ENUM('paying', 'open', 'rejected', 'pending_verification', 'canceled', 'refunded', 'transferred') DEFAULT 'paying'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reverting might fail if 'transferred' data already exists
        DB::statement("ALTER TABLE installment_orders MODIFY COLUMN status ENUM('paying', 'open', 'rejected', 'pending_verification', 'canceled', 'refunded') DEFAULT 'paying'");
    }
};
