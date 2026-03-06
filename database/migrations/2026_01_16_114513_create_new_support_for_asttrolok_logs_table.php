<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('new_support_for_asttrolok_logs')) {
            return;
        }
        Schema::create('new_support_for_asttrolok_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('support_request_id');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('action', 100);
            $table->text('remarks')->nullable();

            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            // Optional foreign keys (agar strict chahte ho)
            // $table->foreign('support_request_id')->references('id')->on('support_requests')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('new_support_for_asttrolok_logs');
    }
};
