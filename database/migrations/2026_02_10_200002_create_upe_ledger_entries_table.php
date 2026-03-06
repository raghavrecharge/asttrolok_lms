<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('upe_ledger_entries')) { return; }
        Schema::create('upe_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('sale_id');
            $table->enum('entry_type', [
                'payment', 'refund', 'discount', 'adjustment_in', 'adjustment_out',
                'referral_bonus', 'installment_payment', 'subscription_charge',
                'penalty', 'write_off',
            ]);
            $table->enum('direction', ['credit', 'debit']);
            $table->decimal('amount', 15, 2)->unsigned()->comment('Always positive; direction determines sign');
            $table->char('currency', 3)->default('INR');
            $table->enum('payment_method', [
                'cash', 'bank_transfer', 'razorpay', 'paypal', 'stripe',
                'payment_link', 'wallet', 'system',
            ])->nullable();
            $table->string('gateway_transaction_id', 255)->nullable();
            $table->json('gateway_response')->nullable();
            $table->string('reference_type', 50)->nullable()->comment('Polymorphic: discount, installment_schedule, etc.');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->string('idempotency_key', 255)->unique()->nullable()->comment('Prevents duplicate entries');
            $table->timestamp('created_at')->useCurrent();

            // NO updated_at — ledger entries are immutable

            $table->index('sale_id');
            $table->index('entry_type');
            $table->index('payment_method');
            $table->index('gateway_transaction_id');
            $table->index(['sale_id', 'entry_type']);
            $table->index(['sale_id', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_ledger_entries');
    }
};
