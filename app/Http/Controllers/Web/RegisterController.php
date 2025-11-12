<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mixins\RegistrationBonus\RegistrationBonusAccounting;
use App\Models\Accounting;
use App\Models\Affiliate;
use App\Models\AffiliateCode;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Role;
use App\Models\UserMeta;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Agent;
use App\Models\Webinar;
use App\Models\Sale;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationFormForCourse()
    {
        $seoSettings = getSeoMetas('register');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('site.register_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.register_page_title');
        $pageRobot = getPageRobot('register');

        $referralSettings = getReferralSettings();

        $referralCode = Cookie::get('referral_code');

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'referralCode' => $referralCode,
            'referralSettings' => $referralSettings,
        ];

$agent = new Agent();
                    if ($agent->isMobile()){
                        return view(getTemplate() . '.course.landingPage.register', $data);
                }else{
                    return view('web.default2' . '.course.landingPage.register', $data);
                }
        // return view(getTemplate() . '.auth.register', $data);
    }

    public function showRegistrationFormForFree()
    {
        $seoSettings = getSeoMetas('register');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('site.register_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.register_page_title');
        $pageRobot = getPageRobot('register');

        $referralSettings = getReferralSettings();

        $referralCode = Cookie::get('referral_code');

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'referralCode' => $referralCode,
            'referralSettings' => $referralSettings,
        ];

$agent = new Agent();
                    if ($agent->isMobile()){
                        return view(getTemplate() . '.course.landingPage.register_free', $data);
                }else{
                    return view('web.default2' . '.course.landingPage.register_free', $data);
                }
        // return view(getTemplate() . '.auth.register', $data);
    }
    public function showRegistrationFormForFreeEnglish()
    {
        $seoSettings = getSeoMetas('register');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('site.register_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.register_page_title');
        $pageRobot = getPageRobot('register');

        $referralSettings = getReferralSettings();

        $referralCode = Cookie::get('referral_code');

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'referralCode' => $referralCode,
            'referralSettings' => $referralSettings,
        ];

$agent = new Agent();
                    if ($agent->isMobile()){
                        return view(getTemplate() . '.course.landingPage.register_free_english', $data);
                }else{
                    return view('web.default2' . '.course.landingPage.register_free_english', $data);
                }
        // return view(getTemplate() . '.auth.register', $data);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';

        if (!empty($data['mobile']) and !empty($data['country_code'])) {
            $data['mobile'] = ltrim($data['country_code'], '+') . ltrim($data['mobile'], '0');
        }

        $rules = [
            'country_code' => ($registerMethod == 'mobile') ? 'required' : 'nullable',
            'mobile' => (($registerMethod == 'mobile') ? 'required' : 'required') . '|numeric|unique:users',
            'email' => (($registerMethod == 'email') ? 'required' : 'nullable') . '|email|max:255|unique:users',
            'term' => 'required',
            'full_name' => 'required|string|min:3',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|same:password',
            'referral_code' => 'nullable|exists:affiliates_codes,code'
        ];

        if (!empty(getGeneralSecuritySettings('captcha_for_register'))) {
            $rules['captcha'] = 'required|captcha';
        }

        return Validator::make($data, $rules);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return
     */
    protected function create(array $data)
    {
       
        if (!empty($data['mobile']) and !empty($data['country_code'])) {
            $data['mobile'] = ltrim($data['country_code'], '+') . ltrim($data['mobile'], '0');
        }

        $referralSettings = getReferralSettings();
        $usersAffiliateStatus = (!empty($referralSettings) and !empty($referralSettings['users_affiliate_status']));

        if (empty($data['timezone'])) {
            $data['timezone'] = getGeneralSettings('default_time_zone') ?? null;
        }

        $disableViewContentAfterUserRegister = getFeaturesSettings('disable_view_content_after_user_register');
        $accessContent = !((!empty($disableViewContentAfterUserRegister) and $disableViewContentAfterUserRegister));

        $roleName = Role::$user;
        $roleId = Role::getUserRoleId();

        if (!empty($data['account_type'])) {
            if ($data['account_type'] == Role::$teacher) {
                $roleName = Role::$teacher;
                $roleId = Role::getTeacherRoleId();
            } else if ($data['account_type'] == Role::$organization) {
                $roleName = Role::$organization;
                $roleId = Role::getOrganizationRoleId();
            }
        }

        $user = User::create([
            'role_name' => $roleName,
            'role_id' => $roleId,
            'mobile' => $data['mobile'] ?? null,
            'email' => $data['email'] ?? null,
            'full_name' => $data['full_name'],
            'country_code' => $data['country_code'],
            // 'status' => User::$pending,
            'status'=>'active',
            'access_content' => $accessContent,
            'password' => Hash::make($data['password']),
            'pwd_hint' => $data['password'],
            'affiliate' => $usersAffiliateStatus,
            'timezone' => $data['timezone'] ?? null,
            'created_at' => time()
        ]);

        if (!empty($data['certificate_additional'])) {
            UserMeta::updateOrCreate([
                'user_id' => $user->id,
                'name' => 'certificate_additional'
            ], [
                'value' => $data['certificate_additional']
            ]);
        }

        return $user;
    }


    public function registerForFree(Request $request, $slug)
    {
        $this->validator($request->all())->validate();
        $this->validate($request, [
            'full_name' => 'required|string|max:60',
            'country_code' => 'required|string|max:60',
            'mobile' => 'required|string|max:10',
            'email' => 'required|string|max:60',
            'password' => 'required|string|max:40',
            'password_confirmation' => 'required|string|max:40',
        ]);

        $user = $this->create($request->all());

        event(new Registered($user));

        // $notifyOptions = [
        //     '[u.name]' => $user->full_name,
        //     '[u.role]' => trans("update.role_{$user->role_name}"),
        //     '[time.date]' => dateTimeFormat($user->created_at, 'j M Y H:i'),
        // ];
        // sendNotification("new_registration", $notifyOptions, 1);

        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';

        $value = $request->get($registerMethod);
        if ($registerMethod == 'mobile') {
            $value = $request->get('country_code') . ltrim($request->get('mobile'), '0');
        }

        // $referralCode = $request->get('referral_code', null);
        // if (!empty($referralCode)) {
        //     session()->put('referralCode', $referralCode);
        // }

        // $verificationController = new VerificationController();
        

        // $checkConfirmed = $verificationController->checkConfirmed($user, $registerMethod, $value);
        // $referralCode = $request->get('referral_code', null);

        // if ($checkConfirmed['status'] == 'send') {

            // if (!empty($referralCode)) {
            //     session()->put('referralCode', $referralCode);
            // }
            
          date_default_timezone_set('Asia/Kolkata');  
            
            // $webhookurl='https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTZmMDYzMTA0Mzc1MjZlNTUzYzUxMzEi_pc1';
            $webhookurl='https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTY0MDYzNjA0Mzc1MjZhNTUzMDUxMzYi_pc';
            
            
// Collection object
$webhookdata = [
  'user_id' => $user->id,
  'user_name' => $user->full_name,
  'user_mobile' => $user->mobile,
  'country_code' => $request->get('country_code'),
  'user_email' => $user->email,
  'user_role' => $user->role_name,
  'user_password' => $request->password,
  'slug' => $slug,
  'create_at' => date("Y/m/d H:i"),
  'by' =>'register'
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


	   
            $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/caece889-e99d-4975-a107-341ef58c5f7f';
        if($slug=='learn-free-astrology-course-english'){
            $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/ff19d522-10e4-40e8-99b9-4c61796ac9a4';
        }
// Collection object


                    
             
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

            // return redirect('/verification');
      //comment ####################################################
             $this->guard()->login($user);

            $enableRegistrationBonus = false;
            $registrationBonusAmount = null;
            $registrationBonusSettings = getRegistrationBonusSettings();
            if (!empty($registrationBonusSettings['status']) and !empty($registrationBonusSettings['registration_bonus_amount'])) {
                $enableRegistrationBonus = true;
                $registrationBonusAmount = $registrationBonusSettings['registration_bonus_amount'];
            }


            $user->update([
                'status' => User::$active,
                'enable_registration_bonus' => $enableRegistrationBonus,
                'registration_bonus_amount' => $registrationBonusAmount,
            ]);

            $registerReward = RewardAccounting::calculateScore(Reward::REGISTER);
            RewardAccounting::makeRewardAccounting($user->id, $registerReward, Reward::REGISTER, $user->id, true);

            if (!empty($referralCode)) {
                Affiliate::storeReferral($user, $referralCode);
            }

            $registrationBonusAccounting = new RegistrationBonusAccounting();
            $registrationBonusAccounting->storeRegistrationBonusInstantly($user);
            

            if ($response = $this->registered($request, $user)) {
                return $response;
            }
            
            $course = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!empty($course)) {
                $checkCourseForSale = checkCourseForSale($course, $user);

                if ($checkCourseForSale != 'ok') {
                    return $checkCourseForSale;
                }

                if (!empty($course->price) and $course->price > 0) {
                    $toastData = [
                        'title' => trans('cart.fail_purchase'),
                        'msg' => trans('cart.course_not_free'),
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                Sale::create([
                    'buyer_id' => $user->id,
                    'seller_id' => $course->creator_id,
                    'webinar_id' => $course->id,
                    'type' => Sale::$webinar,
                    'payment_method' => Sale::$credit,
                    'amount' => 0,
                    'total_amount' => 0,
                    'created_at' => time(),
                ]);
            }
            $rdn='/course/learning/'.$slug;
//             if ($request->has('rd')) {
//     $rdn=$request->get('rd');
// }
             
            if($rdn!=''){
                // $rdn=$request->get('rd');
                 return redirect($rdn);
            }else{
             return $request->wantsJson()
                ? new JsonResponse([], 201)
                : redirect($this->redirectPath());
            }
 //comment ####################################################
           
                
                
        // } elseif ($checkConfirmed['status'] == 'verified') {
        //     $this->guard()->login($user);

        //     $enableRegistrationBonus = false;
        //     $registrationBonusAmount = null;
        //     $registrationBonusSettings = getRegistrationBonusSettings();
        //     if (!empty($registrationBonusSettings['status']) and !empty($registrationBonusSettings['registration_bonus_amount'])) {
        //         $enableRegistrationBonus = true;
        //         $registrationBonusAmount = $registrationBonusSettings['registration_bonus_amount'];
        //     }


        //     $user->update([
        //         'status' => User::$active,
        //         'enable_registration_bonus' => $enableRegistrationBonus,
        //         'registration_bonus_amount' => $registrationBonusAmount,
        //     ]);

        //     $registerReward = RewardAccounting::calculateScore(Reward::REGISTER);
        //     RewardAccounting::makeRewardAccounting($user->id, $registerReward, Reward::REGISTER, $user->id, true);

        //     if (!empty($referralCode)) {
        //         Affiliate::storeReferral($user, $referralCode);
        //     }

        //     $registrationBonusAccounting = new RegistrationBonusAccounting();
        //     $registrationBonusAccounting->storeRegistrationBonusInstantly($user);
            

        //     if ($response = $this->registered($request, $user)) {
        //         return $response;
        //     }
            

        //     return $request->wantsJson()
        //         ? new JsonResponse([], 201)
        //         : redirect($this->redirectPath());
        // }
    }


    public function registerForCourse(Request $request, $slug)
    {
        $this->validator($request->all())->validate();
        $this->validate($request, [
            'full_name' => 'required|string|max:60',
            'mobile' => 'required|string|max:10',
            'email' => 'required|string|max:60',
            'password' => 'required|string|max:40',
            'password_confirmation' => 'required|string|max:40',
        ]);

        $user = $this->create($request->all());

        event(new Registered($user));

        // $notifyOptions = [
        //     '[u.name]' => $user->full_name,
        //     '[u.role]' => trans("update.role_{$user->role_name}"),
        //     '[time.date]' => dateTimeFormat($user->created_at, 'j M Y H:i'),
        // ];
        // sendNotification("new_registration", $notifyOptions, 1);

        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';

        $value = $request->get($registerMethod);
        if ($registerMethod == 'mobile') {
            $value = $request->get('country_code') . ltrim($request->get('mobile'), '0');
        }

        // $referralCode = $request->get('referral_code', null);
        // if (!empty($referralCode)) {
        //     session()->put('referralCode', $referralCode);
        // }

        // $verificationController = new VerificationController();
        

        // $checkConfirmed = $verificationController->checkConfirmed($user, $registerMethod, $value);
        // $referralCode = $request->get('referral_code', null);

        // if ($checkConfirmed['status'] == 'send') {

            // if (!empty($referralCode)) {
            //     session()->put('referralCode', $referralCode);
            // }
            
            $webhookurl='https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTZmMDYzMTA0Mzc1MjZlNTUzYzUxMzEi_pc1';
// Collection object
$webhookdata = [
  'user_id' => $user->id,
  'user_name' => $user->full_name,
  'user_mobile' => $user->mobile,
  'user_email' => $user->email,
  'user_role' => $user->role_name,
  'create_at' => date("Y/m/d H:i")
  
  
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

            // return redirect('/verification');
      //comment ####################################################
             $this->guard()->login($user);

            $enableRegistrationBonus = false;
            $registrationBonusAmount = null;
            $registrationBonusSettings = getRegistrationBonusSettings();
            if (!empty($registrationBonusSettings['status']) and !empty($registrationBonusSettings['registration_bonus_amount'])) {
                $enableRegistrationBonus = true;
                $registrationBonusAmount = $registrationBonusSettings['registration_bonus_amount'];
            }


            $user->update([
                'status' => User::$active,
                'enable_registration_bonus' => $enableRegistrationBonus,
                'registration_bonus_amount' => $registrationBonusAmount,
            ]);

            $registerReward = RewardAccounting::calculateScore(Reward::REGISTER);
            RewardAccounting::makeRewardAccounting($user->id, $registerReward, Reward::REGISTER, $user->id, true);

            if (!empty($referralCode)) {
                Affiliate::storeReferral($user, $referralCode);
            }

            $registrationBonusAccounting = new RegistrationBonusAccounting();
            $registrationBonusAccounting->storeRegistrationBonusInstantly($user);
            

            if ($response = $this->registered($request, $user)) {
                return $response;
            }
            
            $course = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!empty($course)) {
                $checkCourseForSale = checkCourseForSale($course, $user);

                if ($checkCourseForSale != 'ok') {
                    return $checkCourseForSale;
                }

                if (!empty($course->price) and $course->price > 0) {
                    $toastData = [
                        'title' => trans('cart.fail_purchase'),
                        'msg' => trans('cart.course_not_free'),
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                Sale::create([
                    'buyer_id' => $user->id,
                    'seller_id' => $course->creator_id,
                    'webinar_id' => $course->id,
                    'type' => Sale::$webinar,
                    'payment_method' => Sale::$credit,
                    'amount' => 0,
                    'total_amount' => 0,
                    'created_at' => time(),
                ]);
            }
            $rdn='/course/learning/'.$slug;
//             if ($request->has('rd')) {
//     $rdn=$request->get('rd');
// }
             
            if($rdn!=''){
                // $rdn=$request->get('rd');
                 return redirect($rdn);
            }else{
             return $request->wantsJson()
                ? new JsonResponse([], 201)
                : redirect($this->redirectPath());
            }
 //comment ####################################################
           
                
                
        // } elseif ($checkConfirmed['status'] == 'verified') {
        //     $this->guard()->login($user);

        //     $enableRegistrationBonus = false;
        //     $registrationBonusAmount = null;
        //     $registrationBonusSettings = getRegistrationBonusSettings();
        //     if (!empty($registrationBonusSettings['status']) and !empty($registrationBonusSettings['registration_bonus_amount'])) {
        //         $enableRegistrationBonus = true;
        //         $registrationBonusAmount = $registrationBonusSettings['registration_bonus_amount'];
        //     }


        //     $user->update([
        //         'status' => User::$active,
        //         'enable_registration_bonus' => $enableRegistrationBonus,
        //         'registration_bonus_amount' => $registrationBonusAmount,
        //     ]);

        //     $registerReward = RewardAccounting::calculateScore(Reward::REGISTER);
        //     RewardAccounting::makeRewardAccounting($user->id, $registerReward, Reward::REGISTER, $user->id, true);

        //     if (!empty($referralCode)) {
        //         Affiliate::storeReferral($user, $referralCode);
        //     }

        //     $registrationBonusAccounting = new RegistrationBonusAccounting();
        //     $registrationBonusAccounting->storeRegistrationBonusInstantly($user);
            

        //     if ($response = $this->registered($request, $user)) {
        //         return $response;
        //     }
            

        //     return $request->wantsJson()
        //         ? new JsonResponse([], 201)
        //         : redirect($this->redirectPath());
        // }
    }
}
