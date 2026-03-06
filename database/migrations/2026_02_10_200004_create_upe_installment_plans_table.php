<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('upe_installment_plans')) { return; }
        Schema::create('upe_installment_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->decimal('total_amount', 15, 2);
            $table->unsignedInteger('num_installments');
            $table->enum('plan_type', ['standard', 'flexible']);
            $table->enum('status', ['active', 'completed', 'defaulted', 'restructured'])->default('active');
            $table->unsignedBigInteger('restructured_from_id')->nullable()->comment('If this plan replaced another');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();

            $table->index('sale_id');
            $table->index('status');
            $table->index('restructured_from_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_installment_plans');
    }
};
