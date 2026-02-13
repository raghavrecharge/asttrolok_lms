<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoriesController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_blog_categories');

            $blogCategories = BlogCategory::withCount('blog')->get();

            $data = [
                'pageTitle' => trans('admin/pages/blog.blog_categories'),
                'blogCategories' => $blogCategories
            ];

            return view('admin.blog.categories', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
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
            $this->authorize('admin_blog_categories_create');

            $this->validate($request, [
                'title' => 'required|string|unique:blog_categories',
            ]);

            BlogCategory::create([
                'title' => $request->get('title')
            ]);

            return redirect(getAdminPanelUrl().'/blog/categories');
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit($category_id)
    {
        try {
            $this->authorize('admin_blog_categories_edit');

            $editCategory = BlogCategory::findOrFail($category_id);

            $data = [
                'pageTitle' => trans('admin/pages/blog.blog_categories'),
                'editCategory' => $editCategory
            ];

            return view('admin.blog.categories', $data);
        } catch (\Exception $e) {
            \Log::error('edit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request, $category_id)
    {
        try {
            $this->authorize('admin_blog_categories_edit');

            $this->validate($request, [
                'title' => 'required',
            ]);

            $editCategory = BlogCategory::findOrFail($category_id);

            $editCategory->update([
                'title' => $request->get('title')
            ]);

            return redirect(getAdminPanelUrl().'/blog/categories');
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function delete($category_id)
    {
        try {
            $this->authorize('admin_blog_categories_delete');

            $editCategory = BlogCategory::findOrFail($category_id);

            $editCategory->delete();

            return redirect(getAdminPanelUrl().'/blog/categories');
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
