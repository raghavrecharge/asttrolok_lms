<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactFormController extends Controller
{

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|email',
                'phone' => 'required|numeric',
                'subject' => 'required|string',
                'message' => 'required|string',

            ]);

            $data = $request->all();
            unset($data['_token']);
            $data['created_at'] = time();

            Contact::create($data);

            $notifyOptions = [
                '[c.u.title]' => $data['subject'],
                '[u.name]' => $data['name']
            ];

             return apiResponse2(1, 'stored', "Your inquiry has been sent successfully.");
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
