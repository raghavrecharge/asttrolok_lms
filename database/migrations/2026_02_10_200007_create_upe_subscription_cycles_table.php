<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upe_subscription_cycles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->unsignedInteger('cycle_number');
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'waived'])->default('pending');
            $table->unsignedBigInteger('ledger_entry_id')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();

            $table->index('subscription_id');
            $table->index('status');
            $table->index(['subscription_id', 'cycle_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_subscription_cycles');
    }
};
