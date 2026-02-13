<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\DeleteAccountRequest;
use Illuminate\Http\Request;

class DeleteAccountRequestController extends Controller
{
    public function store()
    {
        try {
            DeleteAccountRequest::updateOrCreate([
                'user_id' => apiAuth()->id,
            ], [
                'created_at' => time()
            ]);

            return apiResponse2(1, 'stored', trans('update.delete_account_request_stored_msg'));
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
