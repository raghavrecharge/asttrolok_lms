<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkflowColumnsToSupportRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            if (!Schema::hasColumn('new_support_for_asttrolok', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'executed_by')) {
                $table->unsignedBigInteger('executed_by')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'executed_at')) {
                $table->timestamp('executed_at')->nullable()->after('executed_by');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'verified_amount')) {
                $table->decimal('verified_amount', 15, 2)->nullable()->after('executed_at');
            }
            if (!Schema::hasColumn('new_support_for_asttrolok', 'idempotency_key')) {
                $table->string('idempotency_key', 64)->nullable()->unique()->after('verified_amount');
            }
        });

        // Add indexes safely using raw SQL check
        $existingIndexes = collect(DB::select("SHOW INDEX FROM new_support_for_asttrolok"))->pluck('Key_name')->unique()->toArray();

        Schema::table('new_support_for_asttrolok', function (Blueprint $table) use ($existingIndexes) {
            if (!in_array('new_support_for_asttrolok_verified_by_index', $existingIndexes) && Schema::hasColumn('new_support_for_asttrolok', 'verified_by')) {
                $table->index('verified_by');
            }
            if (!in_array('new_support_for_asttrolok_executed_by_index', $existingIndexes) && Schema::hasColumn('new_support_for_asttrolok', 'executed_by')) {
                $table->index('executed_by');
            }
        });
    }

    public function down()
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            $table->dropColumn([
                'verified_by', 'verified_at', 'executed_by', 'executed_at',
                'verified_amount', 'idempotency_key'
            ]);
        });
    }
}
