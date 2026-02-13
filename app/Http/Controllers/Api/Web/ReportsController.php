<?php

namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Api\WebinarReport ;

class ReportsController extends Controller
{

   public function index(){
        try {
            $reasons=getReportReasons() ;
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),$reasons);
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
