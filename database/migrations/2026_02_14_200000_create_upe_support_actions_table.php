<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upe_support_actions', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();

            $table->enum('action_type', [
                'extension',
                'temporary_access',
                'mentor_access',
                'relative_access',
                'offline_payment',
                'refund',
                'payment_migration',
                'coupon_apply',
            ]);

            $table->enum('status', [
                'pending', 'approved', 'executed', 'rejected', 'expired',
            ])->default('pending');

            // Actor: who initiated the action
            $table->unsignedBigInteger('user_id')->comment('Payer / requester');
            // Beneficiary: who gets access (may differ for relative_access)
            $table->unsignedBigInteger('beneficiary_id')->nullable()->comment('Access holder if different from user_id');

            $table->unsignedBigInteger('product_id')->comment('Target UPE product');
            $table->unsignedBigInteger('source_sale_id')->nullable()->comment('Existing sale (source for migration/refund)');
            $table->unsignedBigInteger('target_sale_id')->nullable()->comment('Newly created sale');

            $table->decimal('amount', 15, 2)->default(0)->comment('Money amount involved');
            $table->string('payment_method', 50)->nullable();

            // For temporary access
            $table->timestamp('expires_at')->nullable()->comment('Auto-expiry for temporary access');

            // For coupon apply
            $table->string('coupon_code', 100)->nullable();
            $table->unsignedBigInteger('discount_id')->nullable();

            // For payment migration
            $table->unsignedBigInteger('source_product_id')->nullable()->comment('Source product for migration');

            $table->json('metadata')->nullable()->comment('Action-specific data: reason, admin_remark, policy_snapshot');

            // Workflow
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('executed_by')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('executed_at')->nullable()->comment('Idempotency guard');
            $table->text('rejection_reason')->nullable();

            $table->string('idempotency_key', 255)->unique()->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('beneficiary_id');
            $table->index('product_id');
            $table->index('action_type');
            $table->index('status');
            $table->index(['user_id', 'product_id', 'action_type']);
            $table->index('source_sale_id');
            $table->index('target_sale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_support_actions');
    }
};
