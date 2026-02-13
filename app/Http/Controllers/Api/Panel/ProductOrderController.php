<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductOrderResource;
use App\Http\Resources\ProductResource;
use App\Models\Api\ProductOrder;
use App\Models\Comment;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductOrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = apiAuth();

            $query = ProductOrder::where('product_orders.seller_id', $user->id)
                ->where('product_orders.status', '!=', 'pending')
                ->whereHas('sale', function ($query) {
                    $query->whereNull('refund_at');
                });

            $totalOrders = deepClone($query)->count();
            $pendingOrders = deepClone($query)->where('product_orders.status', ProductOrder::$waitingDelivery)->count();
            $canceledOrders = deepClone($query)->where('product_orders.status', ProductOrder::$canceled)->count();

            $totalSales = deepClone($query)
                ->join('sales', 'sales.product_order_id', 'product_orders.id')
                ->select(DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as totalAmount'))
                ->first();

            $orders = $query->handleFilters()->orderBy('created_at', 'desc')->get();

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                ['orders' => ProductOrderResource::collection($orders),
                    'total_orders_count' => $totalOrders,
                    'pending_orders_count' => $pendingOrders,
                    'canceled_orders_count' => $canceledOrders,
                    'total_sales' => $totalSales->totalAmount ?? 0,
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

    public function getBuyers()
    {
        try {
            $user = apiAuth();

            $query = ProductOrder::where('product_orders.seller_id', $user->id)
                ->where('product_orders.status', '!=', 'pending')
                ->whereHas('sale', function ($query) {
                    $query->whereNull('refund_at');
                });
            $customerIds = deepClone($query)->pluck('buyer_id')->toArray();
            $customers = User::select('id', 'full_name')
                ->whereIn('id', array_unique($customerIds))
                ->get();

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                ['users' => $customers
                ]);
        } catch (\Exception $e) {
            \Log::error('getBuyers error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getPurchases()
    {
        try {
            $query = ProductOrder::where('product_orders.buyer_id', apiAuth()->id)
                ->where('product_orders.status', '!=', 'pending')
                ->whereHas('sale', function ($query) {
                    $query->where('type', 'product');
                    $query->where('access_to_purchased_item', true);
                    $query->whereNull('refund_at');
                });

            $totalOrders = deepClone($query)->count();
            $pendingOrders = deepClone($query)->where(function ($query) {
                $query->where('status', ProductOrder::$waitingDelivery)
                    ->orWhere('status', ProductOrder::$shipped);
            })->count();
            $canceledOrders = deepClone($query)->where('status', ProductOrder::$canceled)->count();

            $totalPurchase = deepClone($query)
                ->join('sales', 'sales.product_order_id', 'product_orders.id')
                ->select(DB::raw("sum(total_amount) as totalAmount"))
                ->first();

            $orders = $query->handleFilters()->orderBy('created_at', 'desc')
                ->get();

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                [
                    'total_orders_count' => $totalOrders,
                    'pending_orders_count' => $pendingOrders,
                    'canceled_orders_count' => $canceledOrders,
                    'total_purchase_amount' => $totalPurchase->totalAmount ?? 0,
                    'orders' => ProductOrderResource::collection($orders),
                ]);
        } catch (\Exception $e) {
            \Log::error('getPurchases error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getSellers()
    {
        try {
            $query = ProductOrder::where('product_orders.buyer_id', apiAuth()->id)
                ->where('product_orders.status', '!=', 'pending')
                ->whereHas('sale', function ($query) {
                    $query->where('type', 'product');
                    $query->where('access_to_purchased_item', true);
                    $query->whereNull('refund_at');
                });

            $sellerIds = deepClone($query)->pluck('seller_id')->toArray();
            $sellers = User::select('id', 'full_name')
                ->whereIn('id', array_unique($sellerIds))
                ->get();
        } catch (\Exception $e) {
            \Log::error('getSellers error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
