<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TrendCategory;
use Illuminate\Http\Request;

class TrendCategoriesController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_trending_categories');

            $trends = TrendCategory::with('category')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('home.trending_categories'),
                'trends' => $trends,
            ];

            return view('admin.categories.trends_lists', $data);
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
            $this->authorize('admin_create_trending_categories');

            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $data = [
                'pageTitle' => trans('admin/pages/categories.new_trend'),
                'categories' => $categories
            ];

            return view('admin.categories.create_trend', $data);
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
            $this->authorize('admin_create_trending_categories');

            $this->validate($request, [
                'category_id' => 'required',
                'icon' => 'required',
                'color' => 'required',
            ]);

            $data = $request->all();

            TrendCategory::create([
                'category_id' => $data['category_id'],
                'icon' => $data['icon'],
                'color' => $data['color'],
                'created_at' => time(),
            ]);

            return redirect(getAdminPanelUrl().'/categories/trends');
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit($trend_id)
    {
        try {
            $this->authorize('admin_edit_trending_categories');

            $trend = TrendCategory::findOrFail($trend_id);

            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $data = [
                'pageTitle' => trans('admin/pages/categories.new_trend'),
                'categories' => $categories,
                'trend' => $trend
            ];

            return view('admin.categories.create_trend', $data);
        } catch (\Exception $e) {
            \Log::error('edit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request, $trend_id)
    {
        try {
            $this->authorize('admin_create_trending_categories');

            $this->validate($request, [
                'category_id' => 'required',
                'icon' => 'required',
                'color' => 'required',
            ]);

            $trend = TrendCategory::findOrFail($trend_id);
            $data = $request->all();

            $trend->update([
                'category_id' => $data['category_id'],
                'icon' => $data['icon'],
                'color' => $data['color'],
            ]);

            return redirect(getAdminPanelUrl().'/categories/trends');
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function destroy($trend_id)
    {
        try {
            $this->authorize('admin_delete_trending_categories');

            $trend = TrendCategory::findOrFail($trend_id);

            $trend->delete();

            return redirect(getAdminPanelUrl().'/categories/trends');
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
