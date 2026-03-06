<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('refunds')) { return; }
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('installment_order_id')->nullable();
            $table->unsignedBigInteger('support_request_id');
            $table->decimal('refund_amount', 15, 2);
            $table->enum('refund_method', ['bank_transfer', 'wallet_credit', 'original_method'])->default('bank_transfer');
            $table->string('bank_account_number', 255)->nullable();
            $table->string('ifsc_code', 20)->nullable();
            $table->string('account_holder_name', 255)->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('sale_id');
            $table->index('support_request_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('refunds');
    }
}
