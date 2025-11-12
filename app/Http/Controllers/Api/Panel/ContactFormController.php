<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactFormController extends Controller
{

    public function store(Request $request)
    {
      $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email',
            'phone' => 'required|numeric',
            'subject' => 'required|string',
            'message' => 'required|string',
            // 'captcha' => 'required|captcha',
        ]);

        $data = $request->all();
        unset($data['_token']);
        $data['created_at'] = time();

        Contact::create($data);

        $notifyOptions = [
            '[c.u.title]' => $data['subject'],
            '[u.name]' => $data['name']
        ];
        // sendNotification('new_contact_message', $notifyOptions, 1);

         return apiResponse2(1, 'stored', "Your inquiry has been sent successfully.");
    }
}
