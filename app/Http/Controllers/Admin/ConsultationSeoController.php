<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Models\ConsultationSeo;
use App\Models\Role;

class ConsultationSeoController extends Controller
{
    public function index(Request $request)
    {
        $users = User::whereIn('role_name', [Role::$teacher, Role::$organization])->where('consultant', 1)->whereNotIn('status', ['inactive', 'pending'])->with(['consultationSeos' => function($q) {
            $q->latest();
        }]);
    
        if ($request->user_id) {
            $users = $users->where('id', $request->user_id);
        }
    
        $users = $users->paginate(10);
    
        return view('admin.consultation_seo.index', compact('users'));
    }

    public function create(Request $request)
    {
        $users = User::whereIn('role_name', [Role::$teacher, Role::$organization])->where('consultant', 1)->orderBy('full_name')->get();
        $selectedUserId = $request->user_id ?? null;
        return view('admin.consultation_seo.create', compact('users', 'selectedUserId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'h1' => 'nullable|string|max:1200',
            'keyword' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        ConsultationSeo::create($validated);

        return redirect(getAdminPanelUrl().'/consultation-seo')->with('success', 'SEO created successfully.');
    }

    public function edit($id)
    {
        $seo = ConsultationSeo::findOrFail($id);
        $users = User::whereIn('role_name', [Role::$teacher, Role::$organization])->where('consultant', 1)->orderBy('full_name')->get();
        return view('admin.consultation_seo.edit', compact('seo', 'users'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'h1' => 'nullable|string|max:1200',
            'keyword' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        $seo = ConsultationSeo::findOrFail($id);
        $seo->update($validated);

        return redirect(getAdminPanelUrl().'/consultation-seo')->with('success', 'SEO updated successfully.');
    }

    public function destroy($id)
    {
        $seo = ConsultationSeo::findOrFail($id);
        $seo->delete();
        return redirect(getAdminPanelUrl().'/consultation-seo')->with('success', 'SEO deleted successfully.');
    }
}
