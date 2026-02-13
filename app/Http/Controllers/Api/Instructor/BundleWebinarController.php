<?php

namespace App\Http\Controllers\Api\Instructor;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebinarResource;
use App\Models\Api\Bundle;
use Illuminate\Http\Request;

class BundleWebinarController extends Controller
{
    public function index($id)
    {
        try {
            $user = apiAuth();
            $bundle = Bundle::where('id', $id)
                ->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id)
                        ->orWhere('teacher_id', $user->id);
                })
                ->with([
                    'bundleWebinars' => function ($query) {
                        $query->with([
                            'webinar'
                        ]);
                        $query->orderBy('order', 'asc');
                    }
                ])
                ->first();
            if (!$bundle) {
                abort(404);
            }

            ;
            $webinars = $bundle->bundleWebinars->map(function ($bundleWebinar){
                return $bundleWebinar->webinar ;
            });

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                ['webinars' => WebinarResource::collection($webinars)]);
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
