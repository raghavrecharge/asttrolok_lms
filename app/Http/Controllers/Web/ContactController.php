<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\User;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class ContactController extends Controller
{
    public function index()
    {
        try {
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
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function store(Request $request)
    {
        try {
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

            $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/b092167d-5307-4bdc-84e6-95457a89c816';

            $webhookdata = [
            'name' => $data['name'],
            'mobile' => $data['phone'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'create_at' => date("Y/m/d H:i"),

            ];

            $gohighlevelcurl = curl_init($gohighlevel);

            curl_setopt($gohighlevelcurl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($gohighlevelcurl, CURLOPT_POST, true);

            curl_setopt($gohighlevelcurl, CURLOPT_POSTFIELDS, json_encode($webhookdata));

            curl_setopt($gohighlevelcurl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
            ]);

            $gohighlevelresponse = curl_exec($gohighlevelcurl);
            }
            return redirect('/thank-you');
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function course(Request $request)
    {
        try {
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

            $webhookdata = [
            'name' => $data['name'],
            'mobile' => $data['phone'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message']

            ];

            $webhookcurl = curl_init($webhookurl);

            curl_setopt($webhookcurl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($webhookcurl, CURLOPT_POST, true);

            curl_setopt($webhookcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));

            $webhookresponse = curl_exec($webhookcurl);

            curl_close($webhookcurl);
            }
            return true;
        } catch (\Exception $e) {
            \Log::error('course error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
