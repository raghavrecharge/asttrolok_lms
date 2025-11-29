<?php
namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Api\Controller;
use App\Models\Api\SupportDepartment ;

class SupportDepartmentsController extends Controller {

    public function index(){
        try {
            $formattedDepartments = SupportDepartment::all()->map(function($department){
                return $department->details ;
            }) ;
            return apiResponse2(1, 'retrieved', trans('public.retrieved'),
                $formattedDepartments
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