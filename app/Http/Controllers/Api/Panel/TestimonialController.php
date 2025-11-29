<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    public function index()
    {
        try {
            $testimonials = Cache::remember('active_testimonials', 3600, function () {
                return  $testimonials = Testimonial::where('status', 'active')->get();
            });

            if ($testimonials->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active testimonials found.'
                ], 404);
            }

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $testimonials);
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
