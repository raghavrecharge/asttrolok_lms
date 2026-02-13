<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Api\Traits\ReviewTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BundleReviewController extends Controller
{
    use ReviewTrait;

    public function store()
    {
        try {
            return $this->store() ;
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
