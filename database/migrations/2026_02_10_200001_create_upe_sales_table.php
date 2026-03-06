<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('upe_sales')) { return; }
        Schema::create('upe_sales', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->enum('sale_type', [
                'paid', 'free', 'trial', 'referral', 'upgrade', 'adjustment',
            ]);
            $table->enum('pricing_mode', [
                'full', 'installment', 'subscription', 'free',
            ]);
            $table->decimal('base_fee_snapshot', 15, 2)->comment('Product base_fee at time of sale — immutable');
            $table->char('currency', 3)->default('INR');
            $table->enum('status', [
                'pending_payment', 'active', 'completed', 'refunded',
                'partially_refunded', 'expired', 'cancelled', 'suspended',
            ])->default('pending_payment');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->unsignedBigInteger('parent_sale_id')->nullable()->comment('For upgrades/adjustments');
            $table->unsignedBigInteger('referral_id')->nullable();
            $table->unsignedBigInteger('support_request_id')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('executed_at')->nullable()->comment('Idempotency guard');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('product_id');
            $table->index('status');
            $table->index('parent_sale_id');
            $table->index(['user_id', 'product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_sales');
    }
};
