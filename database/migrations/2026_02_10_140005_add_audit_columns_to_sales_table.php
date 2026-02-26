<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditColumnsToSalesTable extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'support_request_id')) {
                $table->unsignedBigInteger('support_request_id')->nullable()->after('manual_added');
            }
            if (!Schema::hasColumn('sales', 'granted_by_admin_id')) {
                $table->unsignedBigInteger('granted_by_admin_id')->nullable()->after('support_request_id');
            }
        });

        try {
            Schema::table('sales', function (Blueprint $table) {
                $table->index('support_request_id');
            });
        } catch (\Exception $e) {
            // Index may already exist
        }
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['support_request_id', 'granted_by_admin_id']);
        });
    }
}
