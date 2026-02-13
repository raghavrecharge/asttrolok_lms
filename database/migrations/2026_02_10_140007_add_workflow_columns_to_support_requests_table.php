<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkflowColumnsToSupportRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('new_support_for_asttrolok', function (Blueprint $table) {
            $table->unsignedBigInteger('verified_by')->nullable()->after('status');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->unsignedBigInteger('executed_by')->nullable()->after('verified_at');
            $table->timestamp('executed_at')->nullable()->after('executed_by');
            $table->decimal('verified_amount', 15, 2)->nullable()->after('executed_at');
            $table->string('idempotency_key', 64)->nullable()->unique()->after('verified_amount');

            $table->index('verified_by');
            $table->index('executed_by');
            $table->index('status');
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
