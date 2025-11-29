<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Api\ApiResponseBuilderTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests,ApiResponseBuilderTrait;
    public static $auth ;

}
