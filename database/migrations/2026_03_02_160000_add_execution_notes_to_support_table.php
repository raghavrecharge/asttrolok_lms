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
        if (Schema::hasColumn('new_support_for_asttrolok', 'execution_notes')) { return; }
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            $table->text('execution_notes')->nullable()->after('execution_result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            $table->dropColumn('execution_notes');
        });
    }
};
