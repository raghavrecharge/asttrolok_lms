<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upe_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('billing_amount', 15, 2);
            $table->enum('billing_interval', ['monthly', 'quarterly', 'yearly']);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->unsignedInteger('grace_period_days')->default(3);
            $table->enum('status', [
                'trial', 'active', 'past_due', 'grace', 'cancelled', 'expired',
            ])->default('trial');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('gateway_subscription_id', 255)->nullable();
            $table->timestamps();

            $table->index('sale_id');
            $table->index('user_id');
            $table->index('product_id');
            $table->index('status');
            $table->index('current_period_end');
            $table->index('gateway_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_subscriptions');
    }
};
