<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    public function index()
    {
        // Cache me store karne ke liye
        $testimonials = Cache::remember('active_testimonials', 3600, function () {
            return  $testimonials = Testimonial::where('status', 'active')->get();
        });

        // Agar koi data nahi mila
        if ($testimonials->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active testimonials found.'
            ], 404);
        }

        // Response return karein
        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $testimonials);
    }
}
