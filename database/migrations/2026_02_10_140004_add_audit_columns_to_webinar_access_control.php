<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditColumnsToWebinarAccessControl extends Migration
{
    public function up()
    {
        Schema::table('webinar_access_control', function (Blueprint $table) {
            $table->enum('type', ['temporary', 'extension', 'mentor_access', 'permanent'])->default('temporary')->after('id');
            $table->enum('status', ['active', 'expired', 'revoked', 'replaced'])->default('active')->after('expire');
            $table->unsignedBigInteger('replaced_by')->nullable()->after('status');
            $table->unsignedBigInteger('support_request_id')->nullable()->after('replaced_by');
            $table->unsignedBigInteger('mentor_access_request_id')->nullable()->after('support_request_id');
            $table->unsignedBigInteger('granted_by')->nullable()->after('mentor_access_request_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['user_id', 'webinar_id', 'status']);
            $table->index('support_request_id');
        });
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
