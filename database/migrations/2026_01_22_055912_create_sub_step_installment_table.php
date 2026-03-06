<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sub_step_installments')) { return; }
        Schema::create('sub_step_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('installment_step_id')->comment('Reference to installment_steps table');
            $table->unsignedBigInteger('user_id')->comment('User who owns this sub-step');
            $table->unsignedBigInteger('order_id')->nullable()->comment('Reference to orders table');
            $table->unsignedBigInteger('webinar_id')->nullable()->comment('Reference to webinars table');
            
            $table->integer('sub_step_number')->default(1)->comment('Part 1 or Part 2');
            $table->decimal('price', 13, 2)->comment('Amount for this sub-step (50% of main step)');
            $table->integer('due_date')->comment('Unix timestamp for due date');
            
            $table->enum('status', ['pending', 'approved', 'paid', 'rejected'])->default('pending')->comment('Payment status of sub-step');
            
            $table->integer('payment_date')->nullable()->comment('Unix timestamp when payment was made');
            $table->string('transaction_id')->nullable()->comment('Payment transaction reference');
            
            $table->timestamps();
            
            // Indexes
            $table->index('installment_step_id');
            $table->index('user_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('due_date');
            
            // Foreign keys (optional - uncomment if you want to add foreign key constraints)
            // $table->foreign('installment_step_id')->references('id')->on('installment_steps')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_step_installments');
    }
};