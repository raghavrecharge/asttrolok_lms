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
        
       if (Schema::hasTable('new_support_for_asttrolok')) { return; }
        Schema::create('new_support_for_asttrolok', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            
            // User information
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            
            // Support Scenario - Main Field
            $table->string('support_scenario');
            
            // Course Selection
            $table->unsignedBigInteger('webinar_id');
            
            // Common Fields
            $table->string('title');
            $table->text('description');
            $table->json('attachments')->nullable();
            
            // Course Extension Fields
            $table->integer('extension_days')->nullable();
            $table->text('extension_reason')->nullable();
            
            // Temporary Access Fields
            $table->boolean('payment_pending')->default(false);
            $table->date('expected_payment_date')->nullable();
            $table->decimal('pending_amount', 10, 2)->nullable();
            
            // Mentor Access Fields (kept for future flexibility, not used in current form)
            $table->unsignedBigInteger('requested_mentor_id')->nullable();
            $table->text('mentor_change_reason')->nullable();
            
            // Relatives/Friends Access Fields
            // Relatives/Friends Access Fields (kept for future flexibility, not used in current form)
            $table->string('relative_name')->nullable();
            $table->string('relative_email')->nullable();
            $table->string('relative_phone')->nullable();
            $table->string('relative_relation')->nullable();
            
            // Free Course Grant Fields
            $table->text('free_course_reason')->nullable();
            $table->boolean('is_special_case')->default(false);
            
            // Offline/Cash Payment Fields
            $table->decimal('cash_amount', 10, 2)->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_receipt_number')->nullable();
            $table->string('payment_location')->nullable();
            
            // Installment Restructure Fields
            $table->integer('requested_installments')->nullable();
            $table->decimal('installment_amount', 10, 2)->nullable();
            $table->text('restructure_reason')->nullable();
            
            // New Service Access Fields
            $table->string('requested_service')->nullable();
            $table->text('service_details')->nullable();
            
            // Refund Payment Fields
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('account_holder_name')->nullable();
            
            // Post-Purchase Coupon Fields
            $table->string('coupon_code')->nullable();
            $table->decimal('original_amount', 10, 2)->nullable();
            $table->text('coupon_apply_reason')->nullable();
            
            // Wrong Course Correction Fields
            $table->unsignedBigInteger('wrong_course_id')->nullable();
            $table->unsignedBigInteger('correct_course_id')->nullable();
            $table->text('correction_reason')->nullable();
            
            // Flow and Purchase Status
            $table->string('flow_type');
            $table->string('purchase_status');
            $table->timestamp('course_purchased_at')->nullable();
            $table->timestamp('course_expires_at')->nullable();
            
            // Support Handling
            $table->unsignedBigInteger('support_handler_id')->nullable();
            $table->text('support_remarks')->nullable();
            $table->json('recommended_action')->nullable();
            
            // Approval
            $table->unsignedBigInteger('sub_admin_id')->nullable();
            $table->text('approval_remarks')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Status
            $table->string('status')->default('pending');
            $table->timestamp('executed_at')->nullable();
            $table->json('execution_result')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('webinar_id')->references('id')->on('webinars')->onDelete('cascade');
            // $table->foreign('requested_mentor_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('wrong_course_id')->references('id')->on('webinars')->onDelete('set null');
            // $table->foreign('correct_course_id')->references('id')->on('webinars')->onDelete('set null');
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['ticket_number']);
            $table->index(['webinar_id', 'support_scenario']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_support_for_asttrolok');
    }
};
