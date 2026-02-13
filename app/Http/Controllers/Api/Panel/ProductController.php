<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Api\Product;
use App\Models\Api\Comment;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    public function index()
    {
        try {
            $user = apiAuth();

            if ((!$user->isTeacher() and !$user->isOrganization()) or !$user->checkCanAccessToStore()) {
                abort(403);
            }

            $query = Product::where('creator_id', $user->id);
            $physicalProducts = deepClone($query)->where('type', Product::$physical)->count();;
            $virtualProducts = deepClone($query)->where('type', Product::$virtual)->count();

            $totalPhysicalSales = deepClone($query)->where('products.type', Product::$physical)
                ->join('product_orders', 'products.id', 'product_orders.product_id')
                ->leftJoin('sales', function ($join) {
                    $join->on('product_orders.id', '=', 'sales.product_order_id')
                        ->whereNull('sales.refund_at');
                })
                ->select(DB::raw('sum(sales.total_amount) as total_sales'))
                ->whereNotNull('product_orders.sale_id')
                ->whereNotIn('product_orders.status', [ProductOrder::$canceled, ProductOrder::$pending])
                ->first();

            $totalVirtualSales = deepClone($query)->where('products.type', Product::$virtual)
                ->join('product_orders', 'products.id', 'product_orders.product_id')
                ->leftJoin('sales', function ($join) {
                    $join->on('product_orders.id', '=', 'sales.product_order_id')
                        ->whereNull('sales.refund_at');
                })
                ->select(DB::raw('sum(sales.total_amount) as total_sales'))
                ->whereNotNull('product_orders.sale_id')
                ->whereNotIn('product_orders.status', [ProductOrder::$canceled, ProductOrder::$pending])
                ->first();

            $products = deepClone($query)
                ->orderBy('created_at', 'desc')
                ->get();

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                ['products' => ProductResource::collection($products),
                    'physical_products_count' => $physicalProducts,
                    'virtual_products_count' => $virtualProducts,
                    'physical_products_sale' => (float)$totalPhysicalSales->total_sales ?? 0,
                    'virtual_products_sale' => (float)$totalVirtualSales->total_sales ?? 0,
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

    public function store(Request $request)
    {

    }

    public function show($id)
    {
        try {
            $product = Product::where('creator_id', apiAuth()->id)
                ->where('id', $id)->get();
            if (!$product) {

            }
        } catch (\Exception $e) {
            \Log::error('show error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function purchasedComment()
    {
        try {
            $comments = Comment::where('user_id', apiAuth()->id)
                ->whereNotNull('product_id')
                ->handleFilters()->orderBy('created_at', 'desc')->get()->map(function ($comment) {
                    return $comment->details;
                });

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                [
                    'comments' => $comments,

                ]);
        } catch (\Exception $e) {
            \Log::error('purchasedComment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function myComments(Request $request)
    {
        try {
            $user = apiAuth();

            $query = Comment::where('status', 'active')
                ->whereNotNull('product_id')
                ->whereHas('product', function ($query) use ($user) {
                    $query->where('creator_id', $user->id);
                });

            $repliedCommentsCount = deepClone($query)->whereNotNull('reply_id')->count();

            $comments = $query->handleFilters()->orderBy('created_at', 'desc')
                ->get();

            foreach ($comments->whereNull('viewed_at') as $comment) {
                $comment->update([
                    'viewed_at' => time()
                ]);
            }
            $comments = $comments->map(function ($comment) {
                return $comment->details;
            });

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                [
                    'comments_count' => $comments->count(),
                    'replied_comment_count' => $repliedCommentsCount,
                    'comments' => $comments,

                ]);
        } catch (\Exception $e) {
            \Log::error('myComments error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
