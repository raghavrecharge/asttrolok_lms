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
        Schema::create('course_correction_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_ticket_id')->nullable();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('wrong_course_id');
            $table->unsignedBigInteger('correct_course_id');
            $table->text('correction_reason');
            $table->unsignedBigInteger('processed_by');
            $table->timestamp('processed_at');
            $table->string('ticket_number')->nullable();
            
            $table->index(['user_id']);
            $table->index(['sale_id']);
            $table->index(['wrong_course_id']);
            $table->index(['correct_course_id']);
            $table->index(['processed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_correction_records');
    }
};
