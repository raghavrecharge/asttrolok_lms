<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('upe_audit_log')) { return; }
        Schema::create('upe_audit_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_role', 50);
            $table->string('action', 100)->comment('e.g. sale.created, ledger.payment, refund.approved');
            $table->string('entity_type', 50)->comment('sale, ledger_entry, discount, etc.');
            $table->unsignedBigInteger('entity_id');
            $table->json('old_state')->nullable();
            $table->json('new_state')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('actor_id');
            $table->index('action');
            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_audit_log');
    }
};
