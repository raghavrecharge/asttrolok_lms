<?php

namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdvertisingBanner;

class AdvertisingBannerController extends Controller
{

    public function list(Request $request)
    {
        try {
            $advertisingBanners = AdvertisingBanner::where('published', true)->get()->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'image' => url($banner->image),
                    'link' => $banner->link,
                    'possion' => $banner->position,
                ];

            });

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
                'count' => $advertisingBanners->count(),
                'advertising_banners' => $advertisingBanners,
            ]);
        } catch (\Exception $e) {
            \Log::error('list error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
