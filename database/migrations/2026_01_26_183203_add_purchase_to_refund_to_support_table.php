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
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            // Add purchase_to_refund field if it doesn't exist
            if (!Schema::hasColumn('new_support_for_asttrolok', 'purchase_to_refund')) {
                $table->unsignedBigInteger('purchase_to_refund')->nullable()->after('payment_screenshot');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            if (Schema::hasColumn('new_support_for_asttrolok', 'purchase_to_refund')) {
                $table->dropColumn('purchase_to_refund');
            }
        });
    }
};
