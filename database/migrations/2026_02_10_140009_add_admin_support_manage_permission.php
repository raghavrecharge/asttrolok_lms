<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAdminSupportManagePermission extends Migration
{
    public function up()
    {
        $sectionExists = DB::table('sections')->where('key', 'admin_support_manage')->exists();

        if (!$sectionExists) {
            DB::table('sections')->insert([
                'key' => 'admin_support_manage',
                'title' => 'Support Tickets Management',
            ]);
        }
    }

    public function down()
    {
        DB::table('sections')->where('key', 'admin_support_manage')->delete();
    }
}
