<?php

namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimeZonesController extends Controller
{

    public function index()
    {
        try {
            $list = getListOfTimezones();

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),

                $list
            );
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
