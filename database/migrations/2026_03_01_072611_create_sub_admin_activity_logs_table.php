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
        Schema::create('sub_admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->index();
            $table->string('action', 100);
            $table->string('section', 150)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('method', 10)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_type', 150)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('created_at');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_admin_activity_logs');
    }
};
