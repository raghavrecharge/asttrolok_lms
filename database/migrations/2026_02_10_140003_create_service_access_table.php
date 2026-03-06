<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceAccessTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('service_access')) { return; }
        Schema::create('service_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('service_type', 50);
            $table->unsignedBigInteger('service_id')->nullable();
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
            $table->unsignedBigInteger('support_request_id');
            $table->unsignedBigInteger('granted_by');
            $table->timestamps();

            $table->index('user_id');
            $table->index(['status', 'end_date']);
            $table->index('support_request_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_access');
    }
}
