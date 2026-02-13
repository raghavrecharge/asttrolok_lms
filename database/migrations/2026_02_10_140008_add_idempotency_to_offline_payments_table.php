<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdempotencyToOfflinePaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('offline_payments', function (Blueprint $table) {
            $table->string('idempotency_key', 64)->nullable()->unique()->after('status');
        });
    }

    public function down()
    {
        Schema::table('offline_payments', function (Blueprint $table) {
            $table->dropColumn('idempotency_key');
        });
    }
}
