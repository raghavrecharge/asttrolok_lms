<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToInstallmentOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('installment_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_order_id')->nullable()->after('status');
            $table->unsignedBigInteger('restructure_request_id')->nullable()->after('parent_order_id');
            $table->unsignedBigInteger('transferred_to_order_id')->nullable()->after('restructure_request_id');

            $table->index('parent_order_id');
            $table->index('restructure_request_id');
        });
    }

    public function down()
    {
        Schema::table('installment_orders', function (Blueprint $table) {
            $table->dropColumn(['parent_order_id', 'restructure_request_id', 'transferred_to_order_id']);
        });
    }
}
