<?php

namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{

    public function index()
    {
        try {
            $categories = ProductCategory::whereNull('parent_id')
                ->with([
                    'subCategories' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                ])
                ->get();

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
                'categories' => ProductCategoryResource::collection($categories)
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
}
