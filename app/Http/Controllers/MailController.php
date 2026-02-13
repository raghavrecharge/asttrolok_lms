<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Exception;

use Illuminate\Http\Request;
use Mail;
use App\Mail\TestMail;
class MailController extends Controller
{
    public function index()
{
        try {
            $data = [
            'title' => 'The Title',
            'body' => 'The Body',
            ];

            Mail::to('jitendrabodana87@gmail.com')->send(new TestMail($data));
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