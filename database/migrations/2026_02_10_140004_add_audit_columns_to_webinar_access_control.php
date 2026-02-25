<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditColumnsToWebinarAccessControl extends Migration
{
    public function up()
    {
        Schema::table('webinar_access_control', function (Blueprint $table) {
            if (!Schema::hasColumn('webinar_access_control', 'type')) {
                $table->enum('type', ['temporary', 'extension', 'mentor_access', 'permanent'])->default('temporary')->after('id');
            }
            if (!Schema::hasColumn('webinar_access_control', 'status')) {
                $table->enum('status', ['active', 'expired', 'revoked', 'replaced'])->default('active')->after('expire');
            }
            if (!Schema::hasColumn('webinar_access_control', 'replaced_by')) {
                $table->unsignedBigInteger('replaced_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('webinar_access_control', 'support_request_id')) {
                $table->unsignedBigInteger('support_request_id')->nullable()->after('replaced_by');
            }
            if (!Schema::hasColumn('webinar_access_control', 'mentor_access_request_id')) {
                $table->unsignedBigInteger('mentor_access_request_id')->nullable()->after('support_request_id');
            }
            if (!Schema::hasColumn('webinar_access_control', 'granted_by')) {
                $table->unsignedBigInteger('granted_by')->nullable()->after('mentor_access_request_id');
            }
            if (!Schema::hasColumn('webinar_access_control', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('webinar_access_control', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        // Add indexes (ignore if they already exist)
        try {
            Schema::table('webinar_access_control', function (Blueprint $table) {
                $table->index(['user_id', 'webinar_id', 'status']);
                $table->index('support_request_id');
            });
        } catch (\Exception $e) {
            // Indexes may already exist — safe to ignore
        }
    }

    public function down()
    {
        Schema::table('webinar_access_control', function (Blueprint $table) {
            $table->dropColumn([
                'type', 'status', 'replaced_by', 'support_request_id',
                'mentor_access_request_id', 'granted_by', 'created_at', 'updated_at'
            ]);
        });
    }
}
