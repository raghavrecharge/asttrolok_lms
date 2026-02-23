<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            if (!Schema::hasColumn('new_support_for_asttrolok', 'relative_description')) {
                $table->text('relative_description')->nullable()->after('relative_relation');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'payment_screenshot')) {
                $table->string('payment_screenshot')->nullable()->after('payment_location');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'execution_notes')) {
                $table->text('execution_notes')->nullable()->after('execution_result');
            }
            // Make description nullable — some scenarios don't require it
            // (relative_friends_access sets description=null and uses relative_description instead)
        });

        // Make description column nullable if it isn't already
        if (Schema::hasColumn('new_support_for_asttrolok', 'description')) {
            DB::statement("ALTER TABLE `new_support_for_asttrolok` MODIFY COLUMN `description` TEXT NULL");
        }
        // Make webinar_id nullable — some scenarios don't require a course
        if (Schema::hasColumn('new_support_for_asttrolok', 'webinar_id')) {
            DB::statement("ALTER TABLE `new_support_for_asttrolok` MODIFY COLUMN `webinar_id` BIGINT UNSIGNED NULL");
        }
    }

    public function down(): void
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            $cols = ['relative_description', 'payment_screenshot', 'execution_notes'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('new_support_for_asttrolok', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
