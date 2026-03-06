<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportAuditLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('support_audit_logs')) { return; }
        Schema::create('support_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_request_id');
            $table->unsignedBigInteger('user_id');
            $table->string('action', 50);
            $table->string('role', 50);
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30)->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('support_request_id');
            $table->index('user_id');
            $table->index('action');
        });
    }

    public function down()
    {
        Schema::dropIfExists('support_audit_logs');
    }
}
