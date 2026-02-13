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
        Schema::create('installment_restructure_requests', function (Blueprint $table) {
            $table->id(); // This creates BIGINT UNSIGNED auto_increment
            
            // Reference fields - CHANGED TO INTEGER (not bigInteger) to match existing tables
            $table->unsignedInteger('installment_order_id')->comment('Original installment order');
            $table->unsignedInteger('installment_step_id')->comment('Which step to restructure');
            $table->unsignedInteger('user_id')->comment('User who requested restructure');
            $table->unsignedInteger('webinar_id')->nullable()->comment('Related webinar/course');
            $table->unsignedInteger('product_id')->nullable()->comment('Related product');
            $table->unsignedInteger('bundle_id')->nullable()->comment('Related bundle');
            
            // Request details
            $table->text('reason')->nullable()->comment('Why user wants to restructure');
            $table->decimal('original_amount', 13, 2)->comment('Original step amount');
            $table->integer('original_deadline')->comment('Original deadline in days');
            
            // Restructure proposal
            $table->integer('number_of_sub_steps')->default(2)->comment('How many sub-steps to split into');
            $table->json('sub_steps_data')->nullable()->comment('JSON array of proposed sub-steps [{amount, deadline, order}]');
            
            // Status tracking
            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'closed',
                'cancelled'
            ])->default('pending')->comment('Request status');
            
            // Admin action
            $table->unsignedInteger('reviewed_by')->nullable()->comment('Admin who reviewed');
            $table->text('admin_notes')->nullable()->comment('Admin comments/notes');
            $table->timestamp('reviewed_at')->nullable()->comment('When admin reviewed');
            
            // Support ticket reference
            $table->unsignedInteger('support_ticket_id')->nullable()->comment('Related support ticket');
            
            // Attachments (if user provides documents)
            $table->json('attachments')->nullable()->comment('JSON array of file paths');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete for record keeping');
            
            // Foreign keys - using unsignedInteger references
            $table->foreign('installment_order_id')
                  ->references('id')
                  ->on('installment_orders')
                  ->onDelete('cascade');
            
            $table->foreign('installment_step_id')
                  ->references('id')
                  ->on('installment_steps')
                  ->onDelete('cascade');
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->foreign('webinar_id')
                  ->references('id')
                  ->on('webinars')
                  ->onDelete('set null');
            
            $table->foreign('reviewed_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // $table->foreign('support_ticket_id')
            //       ->references('id')
            //       ->on('new_support_for_asttrolok')
            //       ->onDelete('set null');
            
            // Indexes for better performance
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index('installment_order_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_restructure_requests');
    }
};