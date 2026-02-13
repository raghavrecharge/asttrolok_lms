<?php

namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Api\TrendCategory;
use App\Models\Api\Webinar;
use Illuminate\Http\Request;
use App\Models\Api\Category;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $categories = Category::whereNull('parent_id')->get()
            ->map(function($category){
                return $category->details ;
            }) ;
            ;
             return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),[

                'count'=>$categories->count() ,
                'categories'=>$categories
            ]);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function trendCategory()
    {
        try {
            $categories = TrendCategory::orderBy('created_at', 'desc')
                ->get()->map(function ($trendCategories) {
                    return $trendCategories->details ;
                 });

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),[
                'count'=>$categories->count() ,
                'categories'=>$categories
            ] );
        } catch (\Exception $e) {
            \Log::error('trendCategory error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function categoryWebinar(Request $request, $id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                 abort(404);
            }
            $webinars = Webinar::where('category_id', $category->id)
            ->where('private', false)
            ->handleFilters()->get()
            ->map(function($webinar){

                return $webinar->brief ;
            }) ;
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),$webinars);
        } catch (\Exception $e) {
            \Log::error('categoryWebinar error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
