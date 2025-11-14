<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use App\Mail\TestMail;
class MailController extends Controller
{
    public function index()
{
    $data = [
        'title' => 'The Title',
        'body' => 'The Body',
    ];

    Mail::to('jitendrabodana87@gmail.com')->send(new TestMail($data));
}
}