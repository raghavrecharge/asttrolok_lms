<?php

use App\Api\Response;
use App\Api\Request;
use Illuminate\Support\Facades\Validator;
function validateParam($request_input, $rules, $somethingElseIsInvalid = null)
{
    $validator = Validator::make($request_input, $rules);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    return $validator->validated();
}
function apiResponse2($success, $status, $msg, $data = null,$title=null)
{
    $response = new Response();
    return $response->apiResponse2($success, $status, $msg, $data,$title);
}


function apiAuth()
{
    if (request()->input('test_auth_id')) {
        return App\Models\Api\User::find(request()->input('test_auth_id')) ?? die('test_auth_id not found');
    }
    return auth('api')->user();


}

function nicePrice($price)
{
    return round(handlePrice($price, false), 2);
}

function nicePriceWithTax($price)
{

   // return round(handlePrice($price, true,false,true), 2);
    return handlePrice($price, false,false,true);
}




