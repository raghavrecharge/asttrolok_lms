<?php

namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Api\BlogCategory ;

class BlogCategoryController extends Controller
{

    public function index(){
        try {
            $categories=BlogCategory::all()->map(function($category){
                return $category->details ;
            }) ;
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),$categories);
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
