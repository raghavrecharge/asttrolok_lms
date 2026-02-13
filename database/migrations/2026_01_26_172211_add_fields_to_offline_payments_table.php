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
        Schema::table('offline_payments', function (Blueprint $table) {
            // Add course_id field if it doesn't exist
            if (!Schema::hasColumn('offline_payments', 'webinar_id')) {
                $table->unsignedBigInteger('webinar_id')->nullable()->after('user_id');
            }

            // Add UTR number field (rename from reference_number to utr_number)
            if (!Schema::hasColumn('offline_payments', 'utr_number')) {
                $table->string('utr_number', 64)->nullable()->after('attachment');
            }

            // Add payment screenshot path field
            if (!Schema::hasColumn('offline_payments', 'screenshot_path')) {
                $table->string('screenshot_path')->nullable()->after('utr_number');
            }

            // Add admin remark field
            if (!Schema::hasColumn('offline_payments', 'admin_remark')) {
                $table->text('admin_remark')->nullable()->after('status');
            }

            // Add sale_id field to link with order when approved
            if (!Schema::hasColumn('offline_payments', 'sale_id')) {
                $table->unsignedBigInteger('sale_id')->nullable()->after('admin_remark');
            }

            // Add processed_by field to track which admin processed it
            if (!Schema::hasColumn('offline_payments', 'processed_by')) {
                $table->unsignedBigInteger('processed_by')->nullable()->after('sale_id');
            }

            // Add processed_at field
            if (!Schema::hasColumn('offline_payments', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('processed_by');
            }

            // Update status enum to include 'pending' and 'failed'
            if (Schema::hasColumn('offline_payments', 'status')) {
                $table->dropColumn('status');
            }
            $table->enum('status', ['pending', 'approved', 'failed', 'waiting', 'reject'])->default('pending')->after('pay_date');

            // Add proper timestamps if they don't exist
            if (!Schema::hasColumn('offline_payments', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('offline_payments', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }

            // Add indexes
            $table->index('webinar_id');
            $table->index('status');
            $table->index('sale_id');
            $table->index('processed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offline_payments', function (Blueprint $table) {
            // Drop the columns we added
            $table->dropColumn([
                'webinar_id',
                'utr_number', 
                'screenshot_path',
                'admin_remark',
                'sale_id',
                'processed_by',
                'processed_at'
            ]);

            // Restore original status enum if needed
            $table->dropColumn('status');
            $table->enum('status', ['waiting', 'approved', 'reject'])->default('waiting');
        });
    }
};
