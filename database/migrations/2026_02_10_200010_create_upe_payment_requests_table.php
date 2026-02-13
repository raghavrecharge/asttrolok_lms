<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upe_payment_requests', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->enum('request_type', [
                'offline_payment', 'refund', 'upgrade', 'adjustment',
                'restructure', 'manual_discount', 'subscription_cancel',
            ]);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->json('payload')->comment('Request-specific data');
            $table->enum('status', [
                'pending', 'verified', 'approved', 'executed', 'rejected',
            ])->default('pending');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('executed_at')->nullable()->comment('Idempotency guard');
            $table->json('execution_result')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('sale_id');
            $table->index('request_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_payment_requests');
    }
};
