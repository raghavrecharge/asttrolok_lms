<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Translation\PageTranslation;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function index()
    {
        try {
            removeContentLocale();

            $this->authorize('admin_pages_list');

            $pages = Page::orderBy('created_at', 'desc')->paginate(10);

            $data = [
                'pageTitle' => trans('admin/pages/setting.pages'),
                'pages' => $pages
            ];

            return view('admin.pages.lists', $data);
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
            $this->authorize('admin_pages_create');

            $data = [
                'pageTitle' => trans('admin/pages/setting.new_pages')
            ];

            return view('admin.pages.create', $data);
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
            $this->authorize('admin_pages_create');

            $this->validate($request, [
                'locale' => 'required',
                'name' => 'required',
                'link' => 'required|unique:pages,link',
                'title' => 'required',
                'seo_description' => 'nullable|string|max:255',
                'content' => 'required',
            ]);

            $data = $request->all();

            $firstCharacter = substr($data['link'], 0, 1);
            if ($firstCharacter !== '/') {
                $data['link'] = '/' . $data['link'];
            }

            $data['robot'] = (!empty($data['robot']) and $data['robot'] == '1');

            $page = Page::create([
                'link' => $data['link'],
                'name' => $data['name'],
                'robot' => $data['robot'],
                'status' => $data['status'],
                'created_at' => time(),
            ]);

            PageTranslation::updateOrCreate([
                'page_id' => $page->id,
                'locale' => mb_strtolower($data['locale'])
            ], [
                'title' => $data['title'],
                'seo_description' => $data['seo_description'] ?? null,
                'content' => $data['content'],
            ]);

            return redirect(getAdminPanelUrl().'/pages');
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $this->authorize('admin_pages_edit');

            $locale = $request->get('locale', app()->getLocale());

            $page = Page::findOrFail($id);

            storeContentLocale($locale, $page->getTable(), $page->id);

            $data = [
                'pageTitle' => trans('admin/pages/setting.edit_pages') . $page->name,
                'page' => $page
            ];

            return view('admin.pages.create', $data);
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
            $this->authorize('admin_pages_edit');

            $page = Page::findOrFail($id);

            $this->validate($request, [
                'locale' => 'required',
                'name' => 'required',
                'link' => 'required|unique:pages,link,' . $page->id,
                'title' => 'required',
                'seo_description' => 'nullable|string|max:255',
                'content' => 'required',
            ]);

            $data = $request->all();

            $firstCharacter = substr($data['link'], 0, 1);
            if ($firstCharacter !== '/') {
                $data['link'] = '/' . $data['link'];
            }

            $data['robot'] = (!empty($data['robot']) and $data['robot'] == '1');

            $page->update([
                'link' => $data['link'],
                'name' => $data['name'],
                'robot' => $data['robot'],
                'status' => $data['status'],
                'created_at' => time(),
            ]);

            PageTranslation::updateOrCreate([
                'page_id' => $page->id,
                'locale' => mb_strtolower($data['locale'])
            ], [
                'title' => $data['title'],
                'seo_description' => $data['seo_description'] ?? null,
                'content' => $data['content'],
            ]);

            removeContentLocale();

            return redirect(getAdminPanelUrl().'/pages');
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
            $this->authorize('admin_pages_delete');

            $page = Page::findOrFail($id);

            $page->delete();

            return redirect(getAdminPanelUrl().'/pages');
        } catch (\Exception $e) {
            \Log::error('delete error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function statusTaggle($id)
    {
        try {
            $this->authorize('admin_pages_toggle');

            $page = Page::findOrFail($id);

            $page->update([
                'status' => ($page->status == 'draft') ? 'publish' : 'draft'
            ]);

            return redirect(getAdminPanelUrl().'/pages');
        } catch (\Exception $e) {
            \Log::error('statusTaggle error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
