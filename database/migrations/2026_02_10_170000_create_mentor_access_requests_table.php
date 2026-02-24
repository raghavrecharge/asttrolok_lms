<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('mentor_access_requests')) {
            Schema::create('mentor_access_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('webinar_id')->nullable();
                $table->unsignedBigInteger('requested_mentor_id')->nullable();
                $table->text('mentor_change_reason')->nullable();
                $table->string('status', 50)->default('pending');
                $table->text('admin_notes')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('support_request_id')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('webinar_id');
                $table->index('support_request_id');
                $table->index('status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('mentor_access_requests');
    }
};
