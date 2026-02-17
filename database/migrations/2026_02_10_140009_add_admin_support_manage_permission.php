<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAdminSupportManagePermission extends Migration
{
    public function up()
    {
        $sectionExists = DB::table('sections')->where('name', 'admin_support_manage')->exists();

        if (!$sectionExists) {
            // Find the section_group for existing support permissions
            $groupId = DB::table('sections')->where('name', 'admin_supports')->value('section_group_id');

            DB::table('sections')->insert([
                'name' => 'admin_support_manage',
                'section_group_id' => $groupId,
                'caption' => 'Support Tickets Management',
            ]);
        }

        // Grant to admin role (role_id from roles table where is_admin=1)
        $sectionId = DB::table('sections')->where('name', 'admin_support_manage')->value('id');
        $adminRole = DB::table('roles')->where('is_admin', 1)->first();

        if ($sectionId && $adminRole) {
            $alreadyGranted = DB::table('permissions')
                ->where('role_id', $adminRole->id)
                ->where('section_id', $sectionId)
                ->exists();

            if (!$alreadyGranted) {
                DB::table('permissions')->insert([
                    'role_id' => $adminRole->id,
                    'section_id' => $sectionId,
                    'allow' => 1,
                ]);
            }
        }
    }

    public function down()
    {
        $sectionId = DB::table('sections')->where('name', 'admin_support_manage')->value('id');
        if ($sectionId) {
            DB::table('permissions')->where('section_id', $sectionId)->delete();
        }
        DB::table('sections')->where('name', 'admin_support_manage')->delete();
    }
}
