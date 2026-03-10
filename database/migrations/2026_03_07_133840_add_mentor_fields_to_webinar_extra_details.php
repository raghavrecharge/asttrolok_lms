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
        Schema::table('webinar_extra_details', function (Blueprint $table) {
            $table->string('mentor_name')->nullable();
            $table->string('mentor_designation')->nullable();
            $table->text('mentor_bio')->nullable();
            $table->string('mentor_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webinar_extra_details', function (Blueprint $table) {
            $table->dropColumn(['mentor_name', 'mentor_designation', 'mentor_bio', 'mentor_image']);
        });
    }
};
