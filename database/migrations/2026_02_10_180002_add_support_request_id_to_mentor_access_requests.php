<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mentor_access_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('mentor_access_requests', 'support_request_id')) {
                $table->unsignedBigInteger('support_request_id')->nullable()->after('approved_at');
                $table->index('support_request_id');
            }
        });
    }

    public function down()
    {
        Schema::table('mentor_access_requests', function (Blueprint $table) {
            $table->dropColumn('support_request_id');
        });
    }
};
