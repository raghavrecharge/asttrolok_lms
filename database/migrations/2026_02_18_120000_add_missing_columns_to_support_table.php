<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            if (!Schema::hasColumn('new_support_for_asttrolok', 'temporary_access_percentage')) {
                $table->unsignedTinyInteger('temporary_access_percentage')->nullable()->after('temporary_access_reason');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'source_course_id')) {
                $table->unsignedBigInteger('source_course_id')->nullable()->after('execution_result');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'target_course_id')) {
                $table->unsignedBigInteger('target_course_id')->nullable()->after('source_course_id');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'total_users_count')) {
                $table->unsignedInteger('total_users_count')->nullable()->after('target_course_id');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'granted_users_count')) {
                $table->unsignedInteger('granted_users_count')->nullable()->after('total_users_count');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'already_had_access_count')) {
                $table->unsignedInteger('already_had_access_count')->nullable()->after('granted_users_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            $cols = ['temporary_access_percentage', 'source_course_id', 'target_course_id',
                     'total_users_count', 'granted_users_count', 'already_had_access_count'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('new_support_for_asttrolok', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
