<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Translation\CategoryTranslation;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            removeContentLocale();

            $this->authorize('admin_categories_list');

            $categories = Category::where('parent_id', null)
                ->with([
                    'subCategories'
                ])
                ->orderBy('id', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('admin/pages/categories.categories_list_page_title'),
                'categories' => $categories
            ];

            return view('admin.categories.lists', $data);
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
            $this->authorize('admin_categories_create');

            $data = [
                'pageTitle' => trans('admin/main.category_new_page_title'),
            ];

            return view('admin.categories.create', $data);
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
            $this->authorize('admin_categories_create');

            $this->validate($request, [
                'title' => 'required|min:3|max:128',
                'slug' => 'nullable|max:255|unique:categories,slug',
                'icon' => 'required'
            ]);

            $data = $request->all();

            if (!empty($data['order'])) {
                $order = $data['order'];
            } else {
                $order = Category::whereNull('parent_id')->count() + 1;
            }

            $category = Category::create([
                'slug' => $data['slug'] ?? Category::makeSlug($data['title']),
                'icon' => $data['icon'],
                'order' => $order,
            ]);

            CategoryTranslation::updateOrCreate([
                'category_id' => $category->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
            ]);

            $hasSubCategories = (!empty($request->get('has_sub')) and $request->get('has_sub') == 'on');
            $this->setSubCategory($category, $request->get('sub_categories'), $hasSubCategories, $data['locale']);

            cache()->forget(Category::$cacheKey);

            removeContentLocale();

            return redirect(getAdminPanelUrl() . '/categories');
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
            $this->authorize('admin_categories_edit');

            $category = Category::findOrFail($id);
            $subCategories = Category::where('parent_id', $category->id)
                ->orderBy('order', 'asc')
                ->get();

            $locale = $request->get('locale', app()->getLocale());
            storeContentLocale($locale, $category->getTable(), $category->id);

            $data = [
                'pageTitle' => trans('admin/pages/categories.edit_page_title'),
                'category' => $category,
                'subCategories' => $subCategories
            ];

            return view('admin.categories.create', $data);
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
            $this->authorize('admin_categories_edit');

            $category = Category::findOrFail($id);

            $this->validate($request, [
                'title' => 'required|min:3|max:255',
                'slug' => 'nullable|max:255|unique:categories,slug,' . $category->id,
                'icon' => 'required',
            ]);

            $data = $request->all();

            $category->update([
                'icon' => $data['icon'],
                'slug' => $data['slug'] ?? Category::makeSlug($data['title']),
                'order' => $data['order'] ?? $category->order,
            ]);

            CategoryTranslation::updateOrCreate([
                'category_id' => $category->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
            ]);

            $hasSubCategories = (!empty($request->get('has_sub')) and $request->get('has_sub') == 'on');
            $this->setSubCategory($category, $request->get('sub_categories'), $hasSubCategories, $data['locale']);

            cache()->forget(Category::$cacheKey);

            removeContentLocale();

            return redirect(getAdminPanelUrl() . '/categories');
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
            $this->authorize('admin_categories_delete');

            $category = Category::where('id', $id)->first();
            $parent = !empty($category->parent_id) ? $category->parent_id : null;

            if (!empty($category)) {
                Category::where('parent_id', $category->id)
                    ->delete();

                $category->delete();
            }

            cache()->forget(Category::$cacheKey);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => !empty($parent) ? trans('update.sub_category_successfully_deleted') : trans('update.category_successfully_deleted'),
                'status' => 'success'
            ];

            return !empty($parent) ? back()->with(['toast' => $toastData]) : redirect(getAdminPanelUrl() . '/categories')->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function search(Request $request)
    {
        try {
            $term = $request->get('term');

            $option = $request->get('option', null);

            $query = Category::select('id')
                ->whereTranslationLike('title', "%$term%");

            $categories = $query->get();

            return response()->json($categories, 200);
        } catch (\Exception $e) {
            \Log::error('search error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function setSubCategory(Category $category, $subCategories, $hasSubCategories, $locale)
    {
        try {
            $order = 1;
            $oldIds = [];

            if ($hasSubCategories and !empty($subCategories) and count($subCategories)) {
                foreach ($subCategories as $key => $subCategory) {
                    $check = Category::where('id', $key)->first();

                    if (is_numeric($key)) {
                        $oldIds[] = $key;
                    }

                    if (!empty($subCategory['title'])) {
                        if (!empty($check)) {
                            $check->update([
                                'order' => $order,
                                'icon' => $subCategory['icon'] ?? null,
                                'slug' => $subCategory['slug'] ?? null,
                            ]);

                            CategoryTranslation::updateOrCreate([
                                'category_id' => $check->id,
                                'locale' => mb_strtolower($locale),
                            ], [
                                'title' => $subCategory['title'],
                            ]);
                        } else {
                            $checkSlug = 0;
                            if (!empty($subCategory['slug'])) {
                                $checkSlug = Category::query()->where('slug', $subCategory['slug'])->count();
                            }

                            $new = Category::create([
                                'parent_id' => $category->id,
                                'slug' => ($checkSlug < 1 and !empty($subCategory['slug'])) ? $subCategory['slug'] : Category::makeSlug($subCategory['title']),
                                'icon' => $subCategory['icon'] ?? null,
                                'order' => $order,
                            ]);

                            CategoryTranslation::updateOrCreate([
                                'category_id' => $new->id,
                                'locale' => mb_strtolower($locale),
                            ], [
                                'title' => $subCategory['title'],
                            ]);

                            $oldIds[] = $new->id;
                        }

                        $order += 1;
                    }
                }
            }

            Category::where('parent_id', $category->id)
                ->whereNotIn('id', $oldIds)
                ->delete();

            return true;
        } catch (\Exception $e) {
            \Log::error('setSubCategory error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
