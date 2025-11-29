<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    protected $redirectTo = '/admin';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');

        $this->redirectTo = getAdminPanelUrl();
    }

    public function showLoginForm()
    {
        try {
            $data = [
                'pageTitle' => trans('auth.login'),
            ];

            return view('admin.auth.login', $data);
        } catch (\Exception $e) {
            \Log::error('showLoginForm error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function username()
    {
        try {
            return 'email';
        } catch (\Exception $e) {
            \Log::error('username error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
                'email' => 'required|email|exists:users,email,status,active',
                'password' => 'required|min:4',
            ]
        );
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $request->session()->put('login_error', trans('auth.failed'));
        throw ValidationException::withMessages(
            [
                'error' => [trans('auth.failed')],
            ]
        );
    }

    public function login(Request $request)
    {
        try {
            $rules = [
                'email' => 'required|email|exists:users,email,status,active',
                'password' => 'required|min:4',
            ];

            if (!empty(getGeneralSecuritySettings('captcha_for_admin_login'))) {
                $rules['captcha'] = 'required|captcha';
            }

            $this->validate($request, $rules);

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
                return Redirect::to(getAdminPanelUrl());
            }

            return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors([
                'password' => 'Wrong password or this account not approved yet.',
            ]);
        } catch (\Exception $e) {
            \Log::error('login error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout();
            return redirect(getAdminPanelUrl() . '/login');
        } catch (\Exception $e) {
            \Log::error('logout error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
