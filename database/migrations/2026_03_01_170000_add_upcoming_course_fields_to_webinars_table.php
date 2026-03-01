<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpcomingCourseFieldsToWebinarsTable extends Migration
{
    public function up()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->unsignedInteger('launch_date')->nullable()->after('start_date');
            $table->string('post_launch_type', 20)->nullable()->after('launch_date');
        });
    }

    public function down()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->dropColumn(['launch_date', 'post_launch_type']);
        });
    }
}
