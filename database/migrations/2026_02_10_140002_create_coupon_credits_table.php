<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponCreditsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('coupon_credits')) { return; }
        Schema::create('coupon_credits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->unsignedBigInteger('installment_order_id')->nullable();
            $table->unsignedBigInteger('discount_id');
            $table->string('coupon_code', 50);
            $table->decimal('original_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2);
            $table->decimal('credit_amount', 15, 2);
            $table->unsignedBigInteger('support_request_id');
            $table->unsignedBigInteger('processed_by');
            $table->timestamps();

            $table->index('user_id');
            $table->index('support_request_id');
            $table->index('discount_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupon_credits');
    }
}
