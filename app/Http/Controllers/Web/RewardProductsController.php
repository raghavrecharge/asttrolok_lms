<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class RewardProductsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::where('products.status', Product::$active)
                ->where('ordering', true)
                ->whereNotNull('point');

            $productController = new ProductController();

            $query = $productController->handleFilters($request, $query, true);

            $products = $query->paginate(9);

            $categories = ProductCategory::whereNull('parent_id')
                ->with([
                    'subCategories' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                ])
                ->get();

            $selectedCategory = null;

            if (!empty($data['category_id'])) {
                $selectedCategory = ProductCategory::where('id', $data['category_id'])->first();
            }

            $seoSettings = getSeoMetas('reward_products');
            $pageTitle = $seoSettings['title'] ?? '';
            $pageDescription = $seoSettings['description'] ?? '';
            $pageRobot = getPageRobot('reward_products');

            $data = [
                'pageTitle' => $pageTitle,
                'pageDescription' => $pageDescription,
                'pageRobot' => $pageRobot,
                'productsCount' => $products->total(),
                'productCategories' => $categories,
                'selectedCategory' => $selectedCategory,
                'products' => $products,
                'isRewardProducts' => true
            ];

            return view(getTemplate() . '.products.search', $data);
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
