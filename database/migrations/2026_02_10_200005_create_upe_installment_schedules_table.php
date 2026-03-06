<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('upe_installment_schedules')) { return; }
        Schema::create('upe_installment_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedInteger('sequence')->comment('1, 2, 3...');
            $table->decimal('amount_due', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->date('due_date');
            $table->enum('status', [
                'upcoming', 'due', 'paid', 'partial', 'overdue', 'waived',
            ])->default('upcoming');
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('ledger_entry_id')->nullable()->comment('Entry that completed this schedule');
            $table->timestamps();

            $table->index('plan_id');
            $table->index('status');
            $table->index(['plan_id', 'sequence']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_installment_schedules');
    }
};
