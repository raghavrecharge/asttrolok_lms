<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Bitwise\UserLevelOfTraining;
use App\Http\Controllers\Api\Controller;
use App\Http\Controllers\Api\Objects\UserObj;
use App\Models\Category;
use App\Models\Newsletter;

use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\UserMeta;
use App\Models\Follow;

use App\Models\UserZoomApi;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\UserOccupation;

use App\Http\Controllers\Api\UploadFileManager;

class UsersController extends Controller

{

    public function setting()
    {
        try {
            $user = apiAuth();
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                [
                    'user' => $user->details
                ]
            );
        } catch (\Exception $e) {
            \Log::error('setting error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function updateImages(Request $request)
    {
        try {
            $user = apiAuth();
            $url = '';
            if ($request->file('profile_image')) {

                $profileImage = $this->createImage($user, $request->file('profile_image'));

                $url = $profileImage;
                $user->update([
                    'avatar' => $profileImage
                ]);
            }else{

                $defaultUrl = 'https://storage.googleapis.com/astrolok/assets/default/img/default/avatar-1.png';
                $user->update([
                            'avatar' => $defaultUrl
                        ]);

            }

            if ($request->file('identity_scan')) {

                $storage = new UploadFileManager($request->file('identity_scan'));

                $user->update([
                    'identity_scan' => $storage->storage_path,
                ]);

            }

            if ($request->file('certificate')) {

                $storage = new UploadFileManager($request->file('certificate'));

                $user->update([
                    'certificate' => $storage->storage_path,
                ]);

            }

            return apiResponse2(1, 'updated', trans('api.public.updated'),['url'=> $url]);
        } catch (\Exception $e) {
            \Log::error('updateImages error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request)
    {
        try {
            if ($request->input("password")) {
            $available_inputs = [
                'full_name', 'language', 'email', 'mobile', 'newsletter', 'public_message', 'timezone' ,'password',
                'about', 'bio',
                'account_type', 'iban', 'account_id',
                'level_of_training', 'meeting_type',
                'country_id', 'province_id', 'city_id', 'district_id',
                'location'
            ];
            }else {

             $available_inputs = [
                'full_name', 'language', 'email', 'mobile', 'newsletter', 'public_message', 'timezone',
                'about', 'bio',
                'account_type', 'iban', 'account_id',
                'level_of_training', 'meeting_type',
                'country_id', 'province_id', 'city_id', 'district_id',
                'location'
            ];
            }
            $meta = ['address', 'gender', 'age'];

            $user = apiAuth();

            validateParam($request->all(), [
                'full_name' => 'string',
                'language' => 'string',
                'email' => 'email|unique:users,email,' . $user->id,
                'mobile' => 'numeric|unique:users,mobile,' . $user->id,
                'timezone' => ['string', Rule::in(getListOfTimezones())],
                'public_message' => 'boolean',
                'newsletter' => 'boolean',

                'iban' => 'required_with:account_type',
                'account_id' => 'required_with:account_type',

                'bio' => 'nullable|string|min:3|max:48',
                'level_of_training' => 'array|in:beginner,middle,expert',
                'meeting_type' => 'in:in_person,all,online',

                'gender' => 'nullable|in:man,woman',
                'location' => 'array|size:2',
                'location.latitude' => 'required_with:location',
                'location.longitude' => 'required_with:location',
                'address' => 'string',
                'country_id' => 'exists:regions,id',
                'province_id' => 'exists:regions,id',
                'city_id' => 'exists:regions,id',
                'district_id' => 'exists:regions,id',
            ]);

            $user = User::find($user->id);

            foreach ($available_inputs as $input) {
                if ($request->has($input)) {
                    $value = $request->input($input);
                    if ($input == 'level_of_training') {
                        $value = (new UserLevelOfTraining())->getValue($value);
                    }
                    if ($input == 'location') {
                        $value = DB::raw("POINT(" . $value['latitude'] . "," . $value['longitude'] . ")");
                    }
                    if ($input == 'password' && $request->input("password")) {
                        $value = User::generatePassword($value);
                    }

                    $user->update([
                        $input => $value
                    ]);
                }
            }

            // pwd_hint removed — plaintext password storage eliminated (V-02)

            if (!$user->isUser()) {
                if ($request->has('zoom_jwt_token') and !empty($request->input('zoom_jwt_token'))) {

                    UserZoomApi::updateOrCreate(
                        [
                            'user_id' => $user->id,
                        ],
                        [
                            'jwt_token' => $request->input('zoom_jwt_token'),
                            'created_at' => time()
                        ]
                    );

                } else {
                    UserZoomApi::where('user_id', $user->id)->delete();
                }
            }

            if ($request->has('newsletter')) {
                $this->handleNewsletter($user->email, $user->id, $user->newsletter);
            }

            if ($request->has('occupations')) {
                UserOccupation::where('user_id', $user->id)->delete();
                        if (!empty($request->has('occupations'))) {
                            foreach ($request->occupations as $category_id) {
                                UserOccupation::create([
                                    'user_id' => $user->id,
                                    'category_id' => $category_id
                                ]);
                            }
                        }
            }

            return apiResponse2(1, 'updated', trans('api.public.updated'));
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleNewsletter($email, $user_id, $joinNewsletter)
    {
        $check = Newsletter::where('email', $email)->first();
        if ($joinNewsletter) {
            if (empty($check)) {
                Newsletter::create([
                    'user_id' => $user_id,
                    'email' => $email,
                    'created_at' => time()
                ]);
            } else {
                $check->update([
                    'user_id' => $user_id,
                ]);
            }

            $newsletterReward = RewardAccounting::calculateScore(Reward::NEWSLETTERS);
            RewardAccounting::makeRewardAccounting($user_id, $newsletterReward, Reward::NEWSLETTERS, $user_id, true);
        } elseif (!empty($check)) {
            $reward = RewardAccounting::where('user_id', $user_id)
                ->where('item_id', $user_id)
                ->where('type', Reward::NEWSLETTERS)
                ->where('status', RewardAccounting::ADDICTION)
                ->first();

            if (!empty($reward)) {
                $reward->delete();
            }

            $check->delete();
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            validateParam($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required|string|min:6',
            ]);

            $user = apiAuth();
            if (Hash::check($request->input('current_password'), $user->password)) {
                $user->update([
                    'password' => User::generatePassword($request->input('new_password'))
                ]);
                $token = auth('api')->refresh();

                return apiResponse2(1, 'updated', trans('api.public.updated'), [
                    'token' => $token
                ]);

            }
            return apiResponse2(0, 'incorrect', trans('api.public.profile_setting.incorrect'));
        } catch (\Exception $e) {
            \Log::error('updatePassword error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeMetas(Request $request)
    {
        try {
            $data = $request->all();

            if (!empty($data['name']) and !empty($data['value'])) {

                if (!empty($data['user_id'])) {
                    $organization = apiAuth();
                    $user = User::where('id', $data['user_id'])
                        ->where('organ_id', $organization->id)
                        ->first();
                } else {
                    $user = apiAuth();
                }
                $UserMeta = UserMeta::where('user_id', $user->id)
                        ->where('name', $data['name'])
                        ->where('value', $data['value'])
                        ->first();
                if(!isset($UserMeta)){
                    UserMeta::create([
                        'user_id' => $user->id,
                        'name' => $data['name'],
                        'value' => $data['value'],
                    ]);
                    return apiResponse2(1, 'updated', trans('api.public.updated'));
                }
                return apiResponse2(0, 'duplicate', 'You entered duplicate value');

            }
             return apiResponse2(0, 'incorrect', 'Please give correct value');
        } catch (\Exception $e) {
            \Log::error('storeMetas error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function deleteMeta(Request $request)
    {
        try {
            $data = $request->all();
            $user = apiAuth();

            if (!empty($user)) {
                $checkUser = User::find($user->id);

                if (!empty($checkUser) and ($user->id == $user->id or $checkUser->organ_id == $user->id)) {
                    $meta = UserMeta::where('user_id', $user->id)
                        ->where('name', $data['name'])
                        ->where('value', $data['value'])
                        ->first();

                    $meta->delete();
                    return apiResponse2(1, 'deleted', 'the item has been deleted');

                }
            }
            return apiResponse2(0, 'login', 'please login');
            return response()->json([], 422);
        } catch (\Exception $e) {
            \Log::error('deleteMeta error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function followToggle(Request $request, $id)
    {
        try {
            $authUser = apiAuth();
            validateParam($request->all(), [
                'status' => 'required|boolean'
            ]);

            $status = $request->input('status');

            $user = User::where('id', $id)->first();
            if (!$user) {
                abort(404);
            }
            $followStatus = false;
            $follow = Follow::where('follower', $authUser->id)
                ->where('user_id', $user->id)
                ->first();

            if ($status) {

                if (empty($follow)) {
                    Follow::create([
                        'follower' => $authUser->id,
                        'user_id' => $user->id,
                        'status' => Follow::$accepted,
                    ]);

                    $followStatus = true;

                }
                return apiResponse2(1, 'followed', trans('api.user.followed'));

            }

            if (!empty($follow)) {

                $follow->delete();
                return apiResponse2(1, 'unfollowed', trans('api.user.unfollowed'));

            }

            return apiResponse2(0, 'not_followed', trans('api.user.not_followed'));
        } catch (\Exception $e) {
            \Log::error('followToggle error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function createImages($user, $img)
    {
        try {
            $folderPath = "/" . $user->id . '/avatar';

            $file = uniqid() . '.' . $img->getClientOriginalExtension();
            $storage_path = $img->storeAs($folderPath, $file);
            return 'store/' . $storage_path;
        } catch (\Exception $e) {
            \Log::error('createImages error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

public function createImage($user, $img)
{
    try {
        $folderPath = "/" . $user->id . '/avatar/';

        if ($img instanceof \Illuminate\Http\UploadedFile) {

            $originalName = pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME);

            $originalName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $originalName);

            $extension = $img->getClientOriginalExtension();

            $file = $originalName . '.' . $extension;

            $fileContents = file_get_contents($img->getRealPath());

            Storage::disk('gcs')->put($folderPath . $file, $fileContents);

            return Storage::disk('gcs')->url($folderPath . $file);
        }

        if (is_string($img) && strpos($img, 'data:image') === 0) {
            $image_parts = explode(";base64,", $img);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = uniqid() . '.' . $image_type;

            Storage::disk('gcs')->put($folderPath . $file, $image_base64);

            return Storage::disk('gcs')->url($folderPath . $file);
        }

        return response()->json(['error' => 'Invalid image format'], 400);

    } catch (\Exception $e) {
        \Log::error('Image upload failed: ' . $e->getMessage());
        return response()->json(['error' => 'Image upload failed: ' . $e->getMessage()], 500);
    }
}

}
