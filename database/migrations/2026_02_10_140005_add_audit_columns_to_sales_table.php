<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditColumnsToSalesTable extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('support_request_id')->nullable()->after('manual_added');
            $table->unsignedBigInteger('granted_by_admin_id')->nullable()->after('support_request_id');

            $table->index('support_request_id');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['support_request_id', 'granted_by_admin_id']);
        });
    }
}
