<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refund_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_ticket_id')->nullable();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('webinar_id');
            $table->decimal('refund_amount', 10, 2);
            $table->text('refund_reason');
            $table->string('bank_account_number');
            $table->string('ifsc_code');
            $table->string('account_holder_name');
            $table->unsignedBigInteger('processed_by');
            $table->timestamp('processed_at');
            $table->string('ticket_number')->nullable();
            
            $table->index(['user_id']);
            $table->index(['sale_id']);
            $table->index(['webinar_id']);
            $table->index(['processed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_records');
    }
};
