<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upe_mentor_badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('granted_by');
            $table->timestamp('granted_at');
            $table->timestamp('revoked_at')->nullable();
            $table->enum('status', ['active', 'revoked'])->default('active');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upe_mentor_badges');
    }
};
