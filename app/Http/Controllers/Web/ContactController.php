<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\User;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class ContactController extends Controller
{
    public function index()
    {
        $contactSettings = getContactPageSettings();

        $seoSettings = getSeoMetas('contact');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('site.contact_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.contact_page_title');
        $pageRobot = getPageRobot('contact');

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'contactSettings' => $contactSettings
        ];

        $agent = new Agent();
        if ($agent->isMobile()){
                return view(getTemplate() . '.pages.contact', $data);
        }else{
            return view('web.default2' . '.pages.contact', $data);
        }
        // return view('web.default.pages.contact', $data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'phone' => 'required|numeric',
            'subject' => 'required|string',
            'captcha' => 'required|captcha',
        ]);

        $data = $request->all();
        unset($data['_token']);
        $data['created_at'] = time();

        Contact::create($data);

        $notifyOptions = [
            '[c.u.title]' => $data['subject'],
            '[u.name]' => $data['name'],
            '[time.date]' => dateTimeFormat(time(), 'j M Y H:i'),
            '[c.u.message]' => $data['message'],
        ];

        sendNotification('contact_message_submission_for_admin', $notifyOptions, 1);

        sendNotificationToEmail('contact_message_submission', $notifyOptions, $data['email']);
        
        if(isset($data['name'])){
		    
// $webhookurl = 'https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTY5MDYzMzA0MzU1MjZlNTUzNzUxMzMi_pc';
 $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/b092167d-5307-4bdc-84e6-95457a89c816';


// Collection object
$webhookdata = [
  'name' => $data['name'],
  'mobile' => $data['phone'],
  'email' => $data['email'],
  'subject' => $data['subject'],
  'message' => $data['message'],
  'create_at' => date("Y/m/d H:i"),
  
];
// // Initializes a new cURL session
// $webhookcurl = curl_init($webhookurl);
// // Set the CURLOPT_RETURNTRANSFER option to true
// curl_setopt($webhookcurl, CURLOPT_RETURNTRANSFER, true);
// // Set the CURLOPT_POST option to true for POST request
// curl_setopt($webhookcurl, CURLOPT_POST, true);
// // Set the request data as JSON using json_encode function
// curl_setopt($webhookcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));
// // Set custom headers for RapidAPI Auth and Content-Type header

// // Execute cURL request with all previous settings
// $webhookresponse = curl_exec($webhookcurl);
// // Close cURL session
// curl_close($webhookcurl);




             
$gohighlevelcurl = curl_init($gohighlevel);
// Set the CURLOPT_RETURNTRANSFER option to true
curl_setopt($gohighlevelcurl, CURLOPT_RETURNTRANSFER, true);
// Set the CURLOPT_POST option to true for POST request
curl_setopt($gohighlevelcurl, CURLOPT_POST, true);
// Set the request data as JSON using json_encode function
// curl_setopt($gohighlevelcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));
curl_setopt($gohighlevelcurl, CURLOPT_POSTFIELDS, json_encode($webhookdata));
// Set custom headers for RapidAPI Auth and Content-Type header
curl_setopt($gohighlevelcurl, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json', // Ensure JSON data is being sent
    'Accept: application/json' // Accept JSON response if needed
]);
// Execute cURL request with all previous settings
$gohighlevelresponse = curl_exec($gohighlevelcurl); 
}       
        return redirect('/thank-you');
        // return Redirect::to('https://www.asttrolok.com/thank-you');
        // return view('web.default.pages.thankyou');
        // return back()->with(['msg' => trans('site.contact_store_success')]);
    }
    
    
    
    
    
    public function course(Request $request)
    {
        
        
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'phone' => 'required|numeric',
            'subject' => 'required|string',
        ]);

        $data = $request->all();
        unset($data['_token']);
        $data['created_at'] = time();

        Contact::create($data);

        $notifyOptions = [
            '[c.u.title]' => $data['subject'],
            '[u.name]' => $data['name'],
            '[time.date]' => dateTimeFormat(time(), 'j M Y H:i'),
            '[c.u.message]' => $data['message'],
        ];

        sendNotification('contact_message_submission_for_admin', $notifyOptions, 1);

        sendNotificationToEmail('contact_message_submission', $notifyOptions, $data['email']);
        
        if(isset($data['name'])){
		    
$webhookurl = 'https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTY5MDYzMzA0MzU1MjZlNTUzNzUxMzMi_pc';
// Collection object
$webhookdata = [
  'name' => $data['name'],
  'mobile' => $data['phone'],
  'email' => $data['email'],
  'subject' => $data['subject'],
  'message' => $data['message']
  
];
// Initializes a new cURL session
$webhookcurl = curl_init($webhookurl);
// Set the CURLOPT_RETURNTRANSFER option to true
curl_setopt($webhookcurl, CURLOPT_RETURNTRANSFER, true);
// Set the CURLOPT_POST option to true for POST request
curl_setopt($webhookcurl, CURLOPT_POST, true);
// Set the request data as JSON using json_encode function
curl_setopt($webhookcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));
// Set custom headers for RapidAPI Auth and Content-Type header

// Execute cURL request with all previous settings
$webhookresponse = curl_exec($webhookcurl);
// Close cURL session
curl_close($webhookcurl);
}       
        return true;
        // return Redirect::to('https://www.asttrolok.com/thank-you');
        // return view('web.default.pages.thankyou');
        // return back()->with(['msg' => trans('site.contact_store_success')]);
    }
}
