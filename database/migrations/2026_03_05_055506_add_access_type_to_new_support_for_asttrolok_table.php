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
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            $table->string('access_type')->nullable()->after('course_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            $table->dropColumn('access_type');
        });
    }
};
