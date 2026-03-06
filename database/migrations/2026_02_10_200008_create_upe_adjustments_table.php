<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('upe_adjustments')) { return; }
        Schema::create('upe_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_sale_id');
            $table->unsignedBigInteger('target_sale_id');
            $table->enum('adjustment_type', ['upgrade', 'cross_course', 'wrong_course']);
            $table->decimal('source_amount', 15, 2)->comment('Amount taken from source');
            $table->decimal('target_amount', 15, 2)->comment('Amount applied to target');
            $table->decimal('adjustment_percent', 5, 2)->comment('Policy % transferred');
            $table->json('policy_snapshot')->comment('Policy rules at time of adjustment');
            $table->unsignedBigInteger('source_ledger_entry_id')->comment('Debit entry on source');
            $table->unsignedBigInteger('target_ledger_entry_id')->comment('Credit entry on target');
            $table->unsignedBigInteger('approved_by');
            $table->timestamp('created_at')->useCurrent();

            $table->index('source_sale_id');
            $table->index('target_sale_id');
            $table->index('adjustment_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_adjustments');
    }
};
