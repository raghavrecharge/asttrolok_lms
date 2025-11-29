<?php

namespace App\Http\Controllers\Admin\Store;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\Translation\ProductCategoryTranslation;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            removeContentLocale();

            $this->authorize('admin_store_categories_list');

            $categories = ProductCategory::where('parent_id', null)
                ->orderBy('id', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('admin/main.categories'),
                'categories' => $categories
            ];

            return view('admin.store.categories.lists', $data);
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
            $this->authorize('admin_store_categories_create');

            $data = [
                'pageTitle' => trans('admin/main.category_new_page_title'),
            ];

            return view('admin.store.categories.create', $data);
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
            $this->authorize('admin_store_categories_create');

            $this->validate($request, [
                'title' => 'required|min:3|max:128',
                'icon' => 'required',
            ]);

            $data = $request->all();
            $category = ProductCategory::create([
                'icon' => $data['icon'],
            ]);

            ProductCategoryTranslation::updateOrCreate([
                'product_category_id' => $category->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
            ]);

            $hasSubCategories = (!empty($request->get('has_sub')) and $request->get('has_sub') == 'on');
            $this->setSubCategory($category, $request->get('sub_categories'), $hasSubCategories, $data['locale']);

            removeContentLocale();

            return redirect(getAdminPanelUrl().'/store/categories');
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
            $this->authorize('admin_store_categories_edit');

            $category = ProductCategory::findOrFail($id);
            $subCategories = ProductCategory::where('parent_id', $category->id)
                ->orderBy('order', 'asc')
                ->get();

            $locale = $request->get('locale', app()->getLocale());
            storeContentLocale($locale, $category->getTable(), $category->id);

            $data = [
                'pageTitle' => trans('admin/pages/categories.edit_page_title'),
                'category' => $category,
                'subCategories' => $subCategories
            ];

            return view('admin.store.categories.create', $data);
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
            $this->authorize('admin_store_categories_edit');

            $this->validate($request, [
                'title' => 'required|min:3|max:128',
                'icon' => 'required',
            ]);

            $data = $request->all();

            $category = ProductCategory::findOrFail($id);
            $category->update([
                'icon' => $data['icon'],
            ]);

            ProductCategoryTranslation::updateOrCreate([
                'product_category_id' => $category->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
            ]);

            $hasSubCategories = (!empty($request->get('has_sub')) and $request->get('has_sub') == 'on');
            $this->setSubCategory($category, $request->get('sub_categories'), $hasSubCategories, $data['locale']);

            removeContentLocale();

            return redirect(getAdminPanelUrl().'/store/categories');
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
            $this->authorize('admin_store_categories_delete');

            $category = ProductCategory::where('id', $id)->first();

            if (!empty($category)) {
                ProductCategory::where('parent_id', $category->id)
                    ->delete();

                $category->delete();
            }

            return redirect(getAdminPanelUrl().'/store/categories');
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

            $query = ProductCategory::select('id')
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

    public function setSubCategory(ProductCategory $category, $subCategories, $hasSubCategories, $locale)
    {
        try {
            $order = 1;
            $oldIds = [];

            if ($hasSubCategories and !empty($subCategories) and count($subCategories)) {
                foreach ($subCategories as $key => $subCategory) {
                    $check = ProductCategory::where('id', $key)->first();

                    if (is_numeric($key)) {
                        $oldIds[] = $key;
                    }

                    if (!empty($subCategory['title'])) {
                        if (!empty($check)) {
                            $check->update([
                                'order' => $order,
                            ]);

                            ProductCategoryTranslation::updateOrCreate([
                                'product_category_id' => $check->id,
                                'locale' => mb_strtolower($locale),
                            ], [
                                'title' => $subCategory['title'],
                            ]);
                        } else {
                            $new = ProductCategory::create([
                                'parent_id' => $category->id,
                                'order' => $order,
                            ]);

                            ProductCategoryTranslation::updateOrCreate([
                                'product_category_id' => $new->id,
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

            ProductCategory::where('parent_id', $category->id)
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
