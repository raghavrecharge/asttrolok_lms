<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create parent section group for Sub-Admin Management
        $parentId = DB::table('sections')->insertGetId([
            'name' => 'admin_sub_admins',
            'caption' => 'Sub-Admin Management',
            'section_group_id' => null,
        ]);

        // Child sections under Sub-Admin Management
        $children = [
            ['name' => 'admin_sub_admins_list', 'caption' => 'Sub-Admins List'],
            ['name' => 'admin_sub_admins_create', 'caption' => 'Create Sub-Admin'],
            ['name' => 'admin_sub_admins_edit', 'caption' => 'Edit Sub-Admin'],
            ['name' => 'admin_sub_admins_delete', 'caption' => 'Delete Sub-Admin'],
            ['name' => 'admin_sub_admins_toggle_status', 'caption' => 'Activate / Deactivate Sub-Admin'],
            ['name' => 'admin_sub_admins_reset_password', 'caption' => 'Reset Sub-Admin Password'],
            ['name' => 'admin_sub_admins_permissions', 'caption' => 'Assign / Update Permissions'],
            ['name' => 'admin_sub_admins_activity_logs', 'caption' => 'View Activity Logs'],
        ];

        foreach ($children as $child) {
            DB::table('sections')->insert([
                'name' => $child['name'],
                'caption' => $child['caption'],
                'section_group_id' => $parentId,
            ]);
        }

        // Auto-grant all sub-admin sections to the main admin role (role_id = 1 typically)
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        if ($adminRole) {
            $sectionIds = DB::table('sections')
                ->where('name', 'like', 'admin_sub_admins%')
                ->pluck('id');

            foreach ($sectionIds as $sectionId) {
                DB::table('permissions')->insertOrIgnore([
                    'role_id' => $adminRole->id,
                    'section_id' => $sectionId,
                    'allow' => true,
                ]);
            }
        }

        // Clear sections cache
        \Illuminate\Support\Facades\Cache::forget('sections');
    }

    public function down(): void
    {
        $sectionIds = DB::table('sections')
            ->where('name', 'like', 'admin_sub_admins%')
            ->pluck('id');

        DB::table('permissions')->whereIn('section_id', $sectionIds)->delete();
        DB::table('sections')->where('name', 'like', 'admin_sub_admins%')->delete();

        \Illuminate\Support\Facades\Cache::forget('sections');
    }
};
