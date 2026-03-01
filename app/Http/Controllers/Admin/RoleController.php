<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Section;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Sub-admins cannot manage roles (privilege escalation prevention)
            if (auth()->check() && auth()->user()->role_name === 'sub_admin') {
                abort(403, 'Only the main Admin can manage roles.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $this->authorize('admin_roles_list');

            $roles = Role::with('users')
                ->orderBy('created_at', 'asc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('admin/pages/roles.page_lists_title'),
                'roles' => $roles,
            ];

            return view('admin.roles.lists', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function create()
    {
        try {
            $this->authorize('admin_roles_create');

            $sections = Section::whereNull('section_group_id')
                ->with('children')
                ->get();

            $data = [
                'pageTitle' => trans('admin/main.role_new_page_title'),
                'sections' => $sections
            ];

            return view('admin.roles.create', $data);
        } catch (\Exception $e) {
            \Log::error('create error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('admin_roles_create');

            $this->validate($request, [
                'name' => 'required|min:3|max:64|unique:roles,name',
                'caption' => 'required|min:3|max:64|unique:roles,caption',
            ]);

            $data = $request->all();

            $role = Role::create([
                'name' => $data['name'],
                'caption' => $data['caption'],
                'is_admin' => (!empty($data['is_admin']) and $data['is_admin'] == 'on'),
                'created_at' => time(),
            ]);

            if ($role->is_admin and $request->has('permissions')) {
                $this->storePermission($role, $data['permissions']);
            }
            Cache::forget('sections');

            return redirect(getAdminPanelUrl().'/roles');
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit($id)
    {
        try {
            $this->authorize('admin_roles_edit');

            $role = Role::findOrFail($id);
            $permissions = Permission::where('role_id', '=', $role->id)->get();
            $sections = Section::whereNull('section_group_id')
                ->with('children')
                ->get();

            $data = [
                'pageTitle' => trans('/admin/main.edit'),
                'role' => $role,
                'sections' => $sections,
                'permissions' => $permissions->keyBy('section_id')
            ];

            return view('admin.roles.create', $data);
        } catch (\Exception $e) {
            \Log::error('edit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->authorize('admin_roles_edit');

            $role = Role::find($id);

            $this->validate($request, [
                'name' => 'required',
                'caption' => 'required',
            ]);

            $data = $request->all();

            $role->update([
                'caption' => $data['caption'],
                'is_admin' => ((!empty($data['is_admin']) and $data['is_admin'] == 'on') or $role->name == Role::$admin),
            ]);

            Permission::where('role_id', '=', $role->id)->delete();

            if ($role->is_admin and !empty($data['permissions'])) {
                $this->storePermission($role, $data['permissions']);
            }

            Cache::forget('sections');

            return redirect(getAdminPanelUrl().'/roles');
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->authorize('admin_roles_delete');

            $role = Role::find($id);
            if ($role->id !== 2) {
                $role->delete();
            }

            return redirect(getAdminPanelUrl().'/roles');
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storePermission($role, $sections)
    {
        try {
            $sectionsId = Section::whereIn('id', $sections)->pluck('id');
            $permissions = [];
            foreach ($sectionsId as $section_id) {
                $permissions[] = [
                    'role_id' => $role->id,
                    'section_id' => $section_id,
                    'allow' => true,
                ];
            }
            Permission::insert($permissions);
        } catch (\Exception $e) {
            \Log::error('storePermission error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
