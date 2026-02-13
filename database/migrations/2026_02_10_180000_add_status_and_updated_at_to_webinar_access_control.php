<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('webinar_access_control', function (Blueprint $table) {
            if (!Schema::hasColumn('webinar_access_control', 'status')) {
                $table->string('status', 20)->default('active')->after('expire');
            }
            if (!Schema::hasColumn('webinar_access_control', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down()
    {
        Schema::table('webinar_access_control', function (Blueprint $table) {
            $table->dropColumn(['status', 'updated_at']);
        });
    }
};
