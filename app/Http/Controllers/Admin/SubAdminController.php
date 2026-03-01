<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Section;
use App\Models\SubAdminActivityLog;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SubAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Hard block: sub-admins can NEVER manage other sub-admins
            if (auth()->check() && auth()->user()->role_name === 'sub_admin') {
                abort(403, 'Only the main Admin can manage Sub-Admins.');
            }
            return $next($request);
        });
    }

    /**
     * Get or create the sub_admin role.
     */
    private function getSubAdminRole()
    {
        $role = Role::where('name', 'sub_admin')->first();

        if (!$role) {
            $role = Role::create([
                'name' => 'sub_admin',
                'caption' => 'Sub Admin',
                'is_admin' => true,
                'created_at' => time(),
            ]);
        }

        return $role;
    }

    /**
     * Check if the current user is a super admin (main admin role).
     */
    private function isSuperAdmin()
    {
        $user = auth()->user();
        return $user && $user->role && $user->role->name === Role::$admin;
    }

    /**
     * Prevent sub-admins from managing other sub-admins (only super admin can).
     */
    private function ensureSuperAdmin()
    {
        if (!$this->isSuperAdmin()) {
            abort(403, 'Only the main Admin can manage Sub-Admins.');
        }
    }

    /**
     * List all sub-admins.
     */
    public function index(Request $request)
    {
        $this->authorize('admin_sub_admins_list');

        $subAdminRole = $this->getSubAdminRole();

        $query = User::where('role_id', $subAdminRole->id);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('status', 'active')->whereNull('ban');
            } elseif ($status === 'inactive') {
                $query->where(function ($q) {
                    $q->where('status', '!=', 'active')->orWhereNotNull('ban');
                });
            }
        }

        $subAdmins = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get permission count for each sub-admin
        $permissionCounts = Permission::where('role_id', $subAdminRole->id)
            ->where('allow', true)
            ->count();

        $data = [
            'pageTitle' => 'Sub-Admin Management',
            'subAdmins' => $subAdmins,
            'permissionCount' => $permissionCounts,
            'subAdminRole' => $subAdminRole,
        ];

        return view('admin.sub_admins.lists', $data);
    }

    /**
     * Show create sub-admin form.
     */
    public function create()
    {
        $this->authorize('admin_sub_admins_create');

        $data = [
            'pageTitle' => 'Create Sub-Admin',
        ];

        return view('admin.sub_admins.create', $data);
    }

    /**
     * Store a new sub-admin.
     */
    public function store(Request $request)
    {
        $this->authorize('admin_sub_admins_create');

        $this->validate($request, [
            'full_name' => 'required|string|min:2|max:128',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $subAdminRole = $this->getSubAdminRole();

        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password),
            'role_id' => $subAdminRole->id,
            'role_name' => 'sub_admin',
            'status' => 'active',
            'created_at' => time(),
        ]);

        SubAdminActivityLog::log(auth()->id(), 'sub_admin_created', "Created sub-admin: {$user->full_name} ({$user->email})", [
            'target_id' => $user->id,
            'target_type' => 'user',
        ]);

        return redirect(getAdminPanelUrl() . '/sub-admins/' . $user->id . '/permissions')
            ->with('msg', 'Sub-Admin created successfully. Now assign permissions.');
    }

    /**
     * Show edit sub-admin form.
     */
    public function edit($id)
    {
        $this->authorize('admin_sub_admins_edit');

        $subAdminRole = $this->getSubAdminRole();
        $user = User::where('id', $id)->where('role_id', $subAdminRole->id)->firstOrFail();

        $data = [
            'pageTitle' => 'Edit Sub-Admin: ' . $user->full_name,
            'subAdmin' => $user,
        ];

        return view('admin.sub_admins.create', $data);
    }

    /**
     * Update sub-admin details.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('admin_sub_admins_edit');

        $subAdminRole = $this->getSubAdminRole();
        $user = User::where('id', $id)->where('role_id', $subAdminRole->id)->firstOrFail();

        $this->validate($request, [
            'full_name' => 'required|string|min:2|max:128',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'nullable|string|max:20',
        ]);

        $oldValues = [
            'full_name' => $user->full_name,
            'email' => $user->email,
            'mobile' => $user->mobile,
        ];

        $user->update([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'mobile' => $request->mobile,
        ]);

        SubAdminActivityLog::log(auth()->id(), 'sub_admin_updated', "Updated sub-admin: {$user->full_name}", [
            'target_id' => $user->id,
            'target_type' => 'user',
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($request->only(['full_name', 'email', 'mobile'])),
        ]);

        return redirect(getAdminPanelUrl() . '/sub-admins')
            ->with('msg', 'Sub-Admin updated successfully.');
    }

    /**
     * Toggle sub-admin active/inactive status.
     */
    public function toggleStatus($id)
    {
        $this->authorize('admin_sub_admins_toggle_status');

        $subAdminRole = $this->getSubAdminRole();
        $user = User::where('id', $id)->where('role_id', $subAdminRole->id)->firstOrFail();

        if ($user->ban) {
            $user->update(['ban' => null, 'ban_start_at' => null, 'ban_end_at' => null]);
            $action = 'sub_admin_activated';
            $desc = "Activated sub-admin: {$user->full_name}";
        } else {
            $user->update(['ban' => true, 'ban_start_at' => time(), 'ban_end_at' => null]);
            $action = 'sub_admin_deactivated';
            $desc = "Deactivated sub-admin: {$user->full_name}";
        }

        SubAdminActivityLog::log(auth()->id(), $action, $desc, [
            'target_id' => $user->id,
            'target_type' => 'user',
        ]);

        return redirect(getAdminPanelUrl() . '/sub-admins')
            ->with('msg', $user->ban ? 'Sub-Admin deactivated.' : 'Sub-Admin activated.');
    }

    /**
     * Show reset password form.
     */
    public function showResetPassword($id)
    {
        $this->authorize('admin_sub_admins_reset_password');

        $subAdminRole = $this->getSubAdminRole();
        $user = User::where('id', $id)->where('role_id', $subAdminRole->id)->firstOrFail();

        $data = [
            'pageTitle' => 'Reset Password: ' . $user->full_name,
            'subAdmin' => $user,
        ];

        return view('admin.sub_admins.reset_password', $data);
    }

    /**
     * Reset sub-admin password.
     */
    public function resetPassword(Request $request, $id)
    {
        $this->authorize('admin_sub_admins_reset_password');

        $subAdminRole = $this->getSubAdminRole();
        $user = User::where('id', $id)->where('role_id', $subAdminRole->id)->firstOrFail();

        $this->validate($request, [
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        SubAdminActivityLog::log(auth()->id(), 'sub_admin_password_reset', "Reset password for sub-admin: {$user->full_name}", [
            'target_id' => $user->id,
            'target_type' => 'user',
        ]);

        return redirect(getAdminPanelUrl() . '/sub-admins')
            ->with('msg', 'Password reset successfully.');
    }

    /**
     * Show permissions assignment page.
     */
    public function permissions($id)
    {
        $this->authorize('admin_sub_admins_permissions');

        $subAdminRole = $this->getSubAdminRole();
        $user = User::where('id', $id)->where('role_id', $subAdminRole->id)->firstOrFail();

        $sections = Section::whereNull('section_group_id')
            ->with('children')
            ->get();

        $currentPermissions = Permission::where('role_id', $subAdminRole->id)
            ->where('allow', true)
            ->pluck('section_id')
            ->toArray();

        // Check if this sub-admin has individual overrides
        $userPermissions = DB::table('sub_admin_permissions')
            ->where('user_id', $user->id)
            ->where('allow', true)
            ->pluck('section_id')
            ->toArray();

        // If individual permissions exist, use those; otherwise use role-based
        $hasIndividualPermissions = DB::table('sub_admin_permissions')
            ->where('user_id', $user->id)
            ->exists();

        $activePermissions = $hasIndividualPermissions ? $userPermissions : $currentPermissions;

        $data = [
            'pageTitle' => 'Manage Permissions: ' . $user->full_name,
            'subAdmin' => $user,
            'sections' => $sections,
            'activePermissions' => $activePermissions,
            'hasIndividualPermissions' => $hasIndividualPermissions,
        ];

        return view('admin.sub_admins.permissions', $data);
    }

    /**
     * Update permissions for a sub-admin.
     */
    public function updatePermissions(Request $request, $id)
    {
        $this->authorize('admin_sub_admins_permissions');

        $subAdminRole = $this->getSubAdminRole();
        $user = User::where('id', $id)->where('role_id', $subAdminRole->id)->firstOrFail();

        $selectedSections = $request->get('permissions', []);

        // Store individual permissions for this sub-admin
        DB::table('sub_admin_permissions')->where('user_id', $user->id)->delete();

        if (!empty($selectedSections)) {
            $sectionIds = Section::whereIn('id', $selectedSections)->pluck('id');
            $permissions = [];
            foreach ($sectionIds as $sectionId) {
                $permissions[] = [
                    'user_id' => $user->id,
                    'section_id' => $sectionId,
                    'allow' => true,
                ];
            }
            DB::table('sub_admin_permissions')->insert($permissions);
        }

        // Clear cached permissions
        Cache::forget('sections');
        // Clear user's cached permissions
        $user->permissions = null;

        SubAdminActivityLog::log(auth()->id(), 'permissions_updated', "Updated permissions for sub-admin: {$user->full_name} (" . count($selectedSections) . " permissions)", [
            'target_id' => $user->id,
            'target_type' => 'user',
            'new_values' => json_encode(['section_ids' => $selectedSections]),
        ]);

        return redirect(getAdminPanelUrl() . '/sub-admins')
            ->with('msg', 'Permissions updated successfully for ' . $user->full_name);
    }

    /**
     * Show activity logs.
     */
    public function activityLogs(Request $request)
    {
        $this->authorize('admin_sub_admins_activity_logs');

        $query = SubAdminActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('from')) {
            $from = strtotime($request->from);
            if ($from) $query->where('created_at', '>=', $from);
        }

        if ($request->filled('to')) {
            $to = strtotime($request->to . ' 23:59:59');
            if ($to) $query->where('created_at', '<=', $to);
        }

        $logs = $query->paginate(25);

        $subAdminRole = $this->getSubAdminRole();
        $subAdmins = User::where('role_id', $subAdminRole->id)->get();

        $data = [
            'pageTitle' => 'Sub-Admin Activity Logs',
            'logs' => $logs,
            'subAdmins' => $subAdmins,
        ];

        return view('admin.sub_admins.activity_logs', $data);
    }

    /**
     * Delete a sub-admin.
     */
    public function destroy($id)
    {
        $this->authorize('admin_sub_admins_delete');

        $subAdminRole = $this->getSubAdminRole();
        $user = User::where('id', $id)->where('role_id', $subAdminRole->id)->firstOrFail();

        $name = $user->full_name;
        $email = $user->email;

        // Remove individual permissions
        DB::table('sub_admin_permissions')->where('user_id', $user->id)->delete();

        SubAdminActivityLog::log(auth()->id(), 'sub_admin_deleted', "Deleted sub-admin: {$name} ({$email})", [
            'target_id' => $user->id,
            'target_type' => 'user',
        ]);

        $user->delete();

        return redirect(getAdminPanelUrl() . '/sub-admins')
            ->with('msg', 'Sub-Admin deleted successfully.');
    }
}
