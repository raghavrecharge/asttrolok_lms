<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Api\Bundle;
use App\Models\RewardAccounting;
use App\Models\Sale;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function buyWithPoint($id)
    {
        try {
            $user = apiAuth();

            $bundle = Bundle::where('id', $id)
                ->where('status', 'active')
                ->first();
            if (!$bundle) {
                abort(404);
            }

            if (empty($bundle->points)) {
                return apiResponse2(0, 'no_points', trans('update.can_not_buy_this_bundle_with_point'));
            }

            if ($user->getRewardPoints() < $bundle->points) {
                return apiResponse2(0, 'no_enough_points', trans('update.you_have_no_enough_points_for_this_bundle'));

            }

            $checkCourseForSale = $bundle->checkWebinarForSale($user);

            if ($checkCourseForSale != 'ok') {
                return $checkCourseForSale;
            }

            Sale::create([
                'buyer_id' => $user->id,
                'seller_id' => $bundle->creator_id,
                'bundle_id' => $bundle->id,
                'type' => Sale::$bundle,
                'payment_method' => Sale::$credit,
                'amount' => 0,
                'total_amount' => 0,
                'created_at' => time(),
            ]);

            RewardAccounting::makeRewardAccounting($user->id, $bundle->points, 'withdraw', null, false, RewardAccounting::DEDUCTION);

            return apiResponse2(1, 'paid', trans('update.success_pay_bundle_with_point_msg'));
        } catch (\Exception $e) {
            \Log::error('buyWithPoint error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function free(Request $request, $id)
    {
        try {
            $user = apiAuth();

            $bundle = Bundle::where('id', $id)
                ->where('status', 'active')
                ->first();
            if (!$bundle) {
                abort(404);
            }

            $checkCourseForSale = $bundle->checkWebinarForSale($user);

            if ($checkCourseForSale != 'ok') {
                return $checkCourseForSale;
            }

            if (!empty($bundle->price) and $bundle->price > 0) {
                return apiResponse2(0, 'not_free', trans('update.bundle_not_free'));
            }

            Sale::create([
                'buyer_id' => $user->id,
                'seller_id' => $bundle->creator_id,
                'bundle_id' => $bundle->id,
                'type' => Sale::$bundle,
                'payment_method' => Sale::$credit,
                'amount' => 0,
                'total_amount' => 0,
                'created_at' => time(),
            ]);
            return apiResponse2(1, 'enrolled', trans('cart.success_pay_msg_for_free_course'));
        } catch (\Exception $e) {
            \Log::error('free error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
