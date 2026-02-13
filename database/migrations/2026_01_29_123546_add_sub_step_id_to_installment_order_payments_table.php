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
        Schema::table('installment_order_payments', function (Blueprint $table) {
            $table->unsignedInteger('sub_step_id')->nullable()->after('step_id');
            $table->index('sub_step_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installment_order_payments', function (Blueprint $table) {
            //
        });
    }
};
