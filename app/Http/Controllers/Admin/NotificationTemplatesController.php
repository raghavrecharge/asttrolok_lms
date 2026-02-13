<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationTemplatesController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_notifications_list');

            $templates = NotificationTemplate::orderBy('id','desc')->paginate(10);

            $data = [
                'pageTitle' => trans('admin/pages/users.templates'),
                'templates' => $templates
            ];

            return view('admin.notifications.templates', $data);
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
            $this->authorize('admin_notifications_template_create');

            $data = [
                'pageTitle' => trans('admin/pages/users.new_template'),
            ];

            return view('admin.notifications.new_template', $data);
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
            $this->authorize('admin_notifications_template_create');

            $this->validate($request, [
                'title' => 'required',
                'template' => 'required',
            ]);

            $data = $request->all();

            NotificationTemplate::create([
                'title' => $data['title'],
                'template' => $data['template'],
            ]);

            return redirect(getAdminPanelUrl().'/notifications/templates');
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
            $this->authorize('admin_notifications_template_edit');

            $template = NotificationTemplate::findOrFail($id);

            $data = [
                'pageTitle' => trans('admin/pages/users.edit_template'),
                'template' => $template
            ];

            return view('admin.notifications.new_template', $data);
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
            $this->authorize('admin_notifications_template_edit');

            $this->validate($request, [
                'title' => 'required',
                'template' => 'required',
            ]);

            $data = $request->all();
            $template = NotificationTemplate::findOrFail($id);

            $template->update([
                'title' => $data['title'],
                'template' => $data['template'],
            ]);

            return redirect(getAdminPanelUrl().'/notifications/templates');
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $this->authorize('admin_notifications_template_delete');

            $template = NotificationTemplate::findOrFail($id);

            $template->delete();

            return redirect(getAdminPanelUrl().'/notifications/templates');
        } catch (\Exception $e) {
            \Log::error('delete error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
