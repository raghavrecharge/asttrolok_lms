<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMobileUniqueIndexToUsers extends Migration
{
    public function up()
    {
        // V-18: Add unique index on mobile (where not null)
        // NOTE: Before running, ensure no duplicate mobile numbers exist:
        // SELECT mobile, COUNT(*) c FROM users WHERE mobile IS NOT NULL GROUP BY mobile HAVING c > 1;
        Schema::table('users', function (Blueprint $table) {
            $table->unique('mobile', 'idx_users_mobile_unique');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('idx_users_mobile_unique');
        });
    }
}
