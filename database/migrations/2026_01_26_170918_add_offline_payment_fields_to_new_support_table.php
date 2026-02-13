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
    public function up(): void
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            // Add payment receipt number field if it doesn't exist
            if (!Schema::hasColumn('new_support_for_asttrolok', 'payment_receipt_number')) {
                $table->string('payment_receipt_number')->nullable()->after('payment_date');
            }

            // Add payment screenshot field if it doesn't exist
            if (!Schema::hasColumn('new_support_for_asttrolok', 'payment_screenshot')) {
                $table->string('payment_screenshot')->nullable()->after('payment_receipt_number');
            }

            // Add purchase_to_refund field if it doesn't exist
            if (!Schema::hasColumn('new_support_for_asttrolok', 'purchase_to_refund')) {
                $table->unsignedBigInteger('purchase_to_refund')->nullable()->after('payment_screenshot');
            }

            // Add execution fields if they don't exist
            if (!Schema::hasColumn('new_support_for_asttrolok', 'support_handler_id')) {
                $table->unsignedBigInteger('support_handler_id')->nullable()->after('status');
            }

            if (!Schema::hasColumn('new_support_for_asttrolok', 'sub_admin_id')) {
                $table->unsignedBigInteger('sub_admin_id')->nullable()->after('support_handler_id');
            }

            if (!Schema::hasColumn('new_support_for_asttrolok', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('sub_admin_id');
            }

            if (!Schema::hasColumn('new_support_for_asttrolok', 'approval_remarks')) {
                $table->text('approval_remarks')->nullable()->after('approved_at');
            }

            if (!Schema::hasColumn('new_support_for_asttrolok', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approval_remarks');
            }

            if (!Schema::hasColumn('new_support_for_asttrolok', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }

            if (!Schema::hasColumn('new_support_for_asttrolok', 'executed_at')) {
                $table->timestamp('executed_at')->nullable()->after('rejection_reason');
            }

            if (!Schema::hasColumn('new_support_for_asttrolok', 'execution_notes')) {
                $table->text('execution_notes')->nullable()->after('executed_at');
            }

            if (!Schema::hasColumn('new_support_for_asttrolok', 'support_remarks')) {
                $table->text('support_remarks')->nullable()->after('execution_notes');
            }
        });

        // Create offline_payment_records table if it doesn't exist
        if (!Schema::hasTable('offline_payment_records')) {
            Schema::create('offline_payment_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('support_ticket_id');
                $table->unsignedBigInteger('sale_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('webinar_id');
                $table->decimal('amount', 10, 2);
                $table->date('payment_date');
                $table->string('payment_location');
                $table->string('receipt_number')->nullable();
                $table->unsignedBigInteger('approved_by');
                $table->timestamp('approved_at');
                $table->string('ticket_number');
                $table->string('status')->default('approved');
                $table->text('notes')->nullable();
                $table->timestamps();

                // Indexes
                $table->index('support_ticket_id');
                $table->index('sale_id');
                $table->index('user_id');
                $table->index('webinar_id');
                $table->index('ticket_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            // Drop the columns we added
            $table->dropColumn([
                'payment_receipt_number',
                'payment_screenshot',
                'purchase_to_refund',
                'support_handler_id',
                'sub_admin_id',
                'approved_at',
                'approval_remarks',
                'rejected_at',
                'rejection_reason',
                'executed_at',
                'execution_notes',
                'support_remarks'
            ]);
        });

        // Drop offline_payment_records table
        Schema::dropIfExists('offline_payment_records');
    }
};
