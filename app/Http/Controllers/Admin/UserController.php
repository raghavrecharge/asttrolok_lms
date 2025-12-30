<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;
use App\Bitwise\UserLevelOfTraining;
use App\Exports\OrganizationsExport;
use App\Exports\StudentsExport;
use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\BecomeInstructor;
use App\Models\Category;
use App\Models\ForumTopic;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Meeting;
use App\Models\Region;
use App\Models\ReserveMeeting;
use App\Models\Role;
use App\Models\Sale;
use App\Models\UserBadge;
use App\Models\UserBank;
use App\Models\UserManualPurchase;
use App\Models\UserMeta;
use App\Models\UserOccupation;
use App\Models\UserRegistrationPackage;
use App\Models\UserSelectedBank;
use App\Models\UserSelectedBankSpecification;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ImportUsers;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\InstallmentOrder;
use App\Models\Product;
use App\Models\RegistrationPackage;
use App\Models\Subscribe;
use App\Models\SubscribeUse;
use App\Models\CourseProgress;
use App\Models\WebinarChapter;
use App\Models\Installment;
use App\Models\InstallmentSpecificationItem;
use App\Models\InstallmentStep;

class UserController extends Controller
{
    public function staffs(Request $request)
    {
        try {
            $this->authorize('admin_staffs_list');

            $staffsRoles = Role::where('is_admin', true)->get();
            $staffsRoleIds = $staffsRoles->pluck('id')->toArray();

            $query = User::whereIn('role_id', $staffsRoleIds);
            $query = $this->filters($query, $request);

            $users = $query->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('admin/main.staff_list_title'),
                'users' => $users,
                'staffsRoles' => $staffsRoles,
            ];

            return view('admin.users.staffs', $data);
        } catch (\Exception $e) {
            \Log::error('staffs error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function organizations(Request $request, $is_export_excel = false)
    {
        try {
            $this->authorize('admin_organizations_list');

            $query = User::where('role_name', Role::$organization);

            $totalOrganizations = deepClone($query)->count();
            $verifiedOrganizations = deepClone($query)->where('verified', true)
                ->count();
            $totalOrganizationsTeachers = User::where('role_name', Role::$teacher)
                ->whereNotNull('organ_id')
                ->count();
            $totalOrganizationsStudents = User::where('role_name', Role::$user)
                ->whereNotNull('organ_id')
                ->count();
            $userGroups = Group::where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            $query = $this->filters($query, $request);

            if ($is_export_excel) {
                $users = $query->orderBy('created_at', 'desc')->get();
            } else {
                $users = $query->orderBy('created_at', 'desc')
                    ->paginate(10);
            }

            $users = $this->addUsersExtraInfo($users);

            if ($is_export_excel) {
                return $users;
            }

            $data = [
                'pageTitle' => trans('admin/main.organizations'),
                'users' => $users,
                'totalOrganizations' => $totalOrganizations,
                'verifiedOrganizations' => $verifiedOrganizations,
                'totalOrganizationsTeachers' => $totalOrganizationsTeachers,
                'totalOrganizationsStudents' => $totalOrganizationsStudents,
                'userGroups' => $userGroups,
            ];

            return view('admin.users.organizations', $data);
        } catch (\Exception $e) {
            \Log::error('organizations error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function students(Request $request, $is_export_excel = false)
    {
        try {
            $this->authorize('admin_users_list');

            $query = User::where('role_name', Role::$user);

            $totalStudents = deepClone($query)->count();
            $inactiveStudents = deepClone($query)->where('status', 'inactive')
                ->count();
            $banStudents = deepClone($query)->where('ban', true)
                ->whereNotNull('ban_end_at')
                ->where('ban_end_at', '>', time())
                ->count();

            $totalOrganizationsStudents = User::where('role_name', Role::$user)
                ->whereNotNull('organ_id')
                ->count();
            $userGroups = Group::where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            $organizations = User::select('id', 'full_name', 'created_at')
                ->where('role_name', Role::$organization)
                ->orderBy('created_at', 'desc')
                ->get();

            $query = $this->filters($query, $request);

            if ($is_export_excel) {
                $users = $query->orderBy('created_at', 'desc')->get();
            } else {
                $users = $query->orderBy('created_at', 'desc')
                    ->paginate(10);
            }

            $users = $this->addUsersExtraInfo($users);

            if ($is_export_excel) {
                return $users;
            }

            $data = [
                'pageTitle' => trans('public.students'),
                'users' => $users,
                'totalStudents' => $totalStudents,
                'inactiveStudents' => $inactiveStudents,
                'banStudents' => $banStudents,
                'totalOrganizationsStudents' => $totalOrganizationsStudents,
                'userGroups' => $userGroups,
                'organizations' => $organizations,
            ];

            return view('admin.users.students', $data);
        } catch (\Exception $e) {
            \Log::error('students error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function instructors(Request $request, $is_export_excel = false)
    {
        try {
            $this->authorize('admin_instructors_list');

            $query = User::where('role_name', Role::$teacher);

            $totalInstructors = deepClone($query)->count();
            $inactiveInstructors = deepClone($query)->where('status', 'inactive')
                ->count();
            $banInstructors = deepClone($query)->where('ban', true)
                ->whereNotNull('ban_end_at')
                ->where('ban_end_at', '>', time())
                ->count();

            $totalOrganizationsInstructors = User::where('role_name', Role::$teacher)
                ->whereNotNull('organ_id')
                ->count();
            $userGroups = Group::where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            $organizations = User::select('id', 'full_name', 'created_at')
                ->where('role_name', Role::$organization)
                ->orderBy('created_at', 'desc')
                ->get();

            $query = $this->filters($query, $request);

            if ($is_export_excel) {
                $users = $query->orderBy('created_at', 'desc')->get();
            } else {
                $users = $query->orderBy('created_at', 'desc')
                    ->paginate(10);
            }

            $users = $this->addUsersExtraInfo($users);

            if ($is_export_excel) {
                return $users;
            }

            $data = [
                'pageTitle' => trans('admin/main.instructors'),
                'users' => $users,
                'totalInstructors' => $totalInstructors,
                'inactiveInstructors' => $inactiveInstructors,
                'banInstructors' => $banInstructors,
                'totalOrganizationsInstructors' => $totalOrganizationsInstructors,
                'userGroups' => $userGroups,
                'organizations' => $organizations,
            ];

            return view('admin.users.instructors', $data);
        } catch (\Exception $e) {
            \Log::error('instructors error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function addUsersExtraInfo($users)
    {
        foreach ($users as $user) {
            $salesQuery = Sale::where('seller_id', $user->id)
                ->whereNull('refund_at');

            $classesSaleQuery = deepClone($salesQuery)->whereNotNull('webinar_id')
                ->whereNull('meeting_id')
                ->whereNull('promotion_id')
                ->whereNull('subscribe_id');

            $user->classesSalesCount = $classesSaleQuery->count();
            $user->classesSalesSum = $classesSaleQuery->sum('total_amount');

            $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');
            $reserveMeetingsQuery = ReserveMeeting::whereIn('meeting_id', $meetingIds)
                ->where(function ($query) {
                    $query->whereHas('sale', function ($query) {
                        $query->whereNull('refund_at');
                    });

                    $query->orWhere(function ($query) {
                        $query->whereIn('status', ['canceled']);
                        $query->whereHas('sale');
                    });
                });

            $user->meetingsSalesCount = deepClone($reserveMeetingsQuery)->count();
            $user->meetingsSalesSum = deepClone($reserveMeetingsQuery)->sum('paid_amount');

            $purchasedQuery = Sale::where('buyer_id', $user->id)
                ->whereNull('refund_at');

            $classesPurchasedQuery = deepClone($purchasedQuery)->whereNotNull('webinar_id')
                ->whereNull('meeting_id')
                ->whereNull('promotion_id')
                ->whereNull('subscribe_id');

            $user->classesPurchasedsCount = $classesPurchasedQuery->count();
            $user->classesPurchasedsSum = $classesPurchasedQuery->sum('total_amount');

            $meetingsPurchasedQuery = deepClone($purchasedQuery)->whereNotNull('meeting_id')
                ->whereNull('webinar_id')
                ->whereNull('promotion_id')
                ->whereNull('subscribe_id');

            $user->meetingsPurchasedsCount = $meetingsPurchasedQuery->count();
            $user->meetingsPurchasedsSum = $meetingsPurchasedQuery->sum('total_amount');
        }

        return $users;
    }

    private function filters($query, $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $full_name = $request->get('full_name');
        $sort = $request->get('sort');
        $group_id = $request->get('group_id');
        $status = $request->get('status');
        $role_id = $request->get('role_id');
        $organization_id = $request->get('organization_id');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($full_name)) {
            $query->where('email', 'like', "%$full_name%");
            $query->Orwhere('mobile', 'like', "%$full_name%");
            $query->Orwhere('full_name', 'like', "%$full_name%");

        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'sales_classes_asc':
                    $query->join('sales', 'users.id', '=', 'sales.seller_id')
                        ->select('users.*', 'sales.seller_id', 'sales.webinar_id', 'sales.refund_at', DB::raw('count(sales.seller_id) as sales_count'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.seller_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_classes_desc':
                    $query->join('sales', 'users.id', '=', 'sales.seller_id')
                        ->select('users.*', 'sales.seller_id', 'sales.webinar_id', 'sales.refund_at', DB::raw('count(sales.seller_id) as sales_count'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.seller_id')
                        ->orderBy('sales_count', 'desc');
                    break;
                case 'purchased_classes_asc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.refund_at', DB::raw('count(sales.buyer_id) as purchased_count'))
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_count', 'asc');
                    break;
                case 'purchased_classes_desc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.refund_at', DB::raw('count(sales.buyer_id) as purchased_count'))
                        ->groupBy('sales.buyer_id')
                        ->whereNull('sales.refund_at')
                        ->orderBy('purchased_count', 'desc');
                    break;
                case 'purchased_classes_amount_asc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.amount', 'sales.refund_at', DB::raw('sum(sales.amount) as purchased_amount'))
                        ->groupBy('sales.buyer_id')
                        ->whereNull('sales.refund_at')
                        ->orderBy('purchased_amount', 'asc');
                    break;
                case 'purchased_classes_amount_desc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.amount', 'sales.refund_at', DB::raw('sum(sales.amount) as purchased_amount'))
                        ->groupBy('sales.buyer_id')
                        ->whereNull('sales.refund_at')
                        ->orderBy('purchased_amount', 'desc');
                    break;
                case 'sales_appointments_asc':
                    $query->join('sales', 'users.id', '=', 'sales.seller_id')
                        ->select('users.*', 'sales.seller_id', 'sales.meeting_id', 'sales.refund_at', DB::raw('count(sales.seller_id) as sales_count'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.seller_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_appointments_desc':
                    $query->join('sales', 'users.id', '=', 'sales.seller_id')
                        ->select('users.*', 'sales.seller_id', 'sales.meeting_id', 'sales.refund_at', DB::raw('count(sales.seller_id) as sales_count'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.seller_id')
                        ->orderBy('sales_count', 'desc');
                    break;
                    break;
                case 'purchased_appointments_asc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.meeting_id', 'sales.refund_at', DB::raw('count(sales.buyer_id) as purchased_count'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_count', 'asc');
                    break;
                case 'purchased_appointments_desc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.meeting_id', 'sales.refund_at', DB::raw('count(sales.buyer_id) as purchased_count'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_count', 'desc');
                    break;
                case 'purchased_appointments_amount_asc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.amount', 'sales.meeting_id', 'sales.refund_at', DB::raw('sum(sales.amount) as purchased_amount'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_amount', 'asc');
                    break;
                case 'purchased_appointments_amount_desc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.amount', 'sales.meeting_id', 'sales.refund_at', DB::raw('sum(sales.amount) as purchased_amount'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_amount', 'desc');
                    break;
                case 'register_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'register_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }

        if (!empty($group_id)) {
            $userIds = GroupUser::where('group_id', $group_id)->pluck('user_id')->toArray();

            $query->whereIn('id', $userIds);
        }

        if (!empty($status)) {
            switch ($status) {
                case 'active_verified':
                    $query->where('status', 'active')
                        ->where('verified', true);
                    break;
                case 'active_notVerified':
                    $query->where('status', 'active')
                        ->where('verified', false);
                    break;
                case 'inactive':
                    $query->where('status', 'inactive');
                    break;
                case 'ban':
                    $query->where('ban', true)
                        ->whereNotNull('ban_end_at')
                        ->where('ban_end_at', '>', time());
                    break;
            }
        }

        if (!empty($role_id)) {
            $query->where('role_id', $role_id);
        }

        if (!empty($organization_id)) {
            $query->where('organ_id', $organization_id);
        }

        return $query;
    }

    public function create()
    {
        try {
            $this->authorize('admin_users_create');

            $roles = Role::orderBy('created_at', 'desc')->get();
            $userGroups = Group::orderBy('created_at', 'desc')->where('status', 'active')->get();

            $data = [
                'pageTitle' => trans('admin/main.user_new_page_title'),
                'roles' => $roles,
                'userGroups' => $userGroups,
            ];

            return view('admin.users.create', $data);
        } catch (\Exception $e) {
            \Log::error('create error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function username($data)
    {
        $email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        $username = 'mobile';
        if (preg_match($email_regex, request('username', null))) {
            $username = 'email';
        }

        return $username;
    }
     public function importview()
    {
        try {
            return view('admin.users.import');
        } catch (\Exception $e) {
            \Log::error('importview error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

public function importExcel(Request $request)
    {
        try {
            $excels=  Excel::import(new ImportUsers, request()->file('file'));
            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => 'Student Added successfully',
                'status' => 'success'
            ];
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('importExcel error: ' . $e->getMessage(), [
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
            $this->authorize('admin_users_create');
            $data = $request->all();

            $username = $this->username($data);
            $data[$username] = $data['username'];
            $request->merge([$username => $data['username']]);
            unset($data['username']);

            $this->validate($request, [
                $username => ($username == 'mobile') ? 'required|numeric|unique:users' : 'required|string|email|max:255|unique:users',
                'full_name' => 'required|min:3|max:128',
                'role_id' => 'required|exists:roles,id',
                'password' => 'required|string|min:6',
                'status' => 'required',
            ]);

            if (!empty($data['role_id'])) {
                $role = Role::find($data['role_id']);

                if (!empty($role)) {
                    $referralSettings = getReferralSettings();
                    $usersAffiliateStatus = (!empty($referralSettings) and !empty($referralSettings['users_affiliate_status']));

            if(empty($data['consultant'])){

            $data['consultant']=0;
            }
                    $user = User::create([
                        'full_name' => $data['full_name'],
                        'role_name' => $role->name,
                        'role_id' => $data['role_id'],
                        'consultant' => $data['consultant'],
                        $username => $data[$username],
                        'password' => User::generatePassword($data['password']),
                        'status' => $data['status'],
                        'affiliate' => $usersAffiliateStatus,
                        'verified' => true,
                        'created_at' => time(),
                    ]);

                    if (!empty($data['group_id'])) {
                        $group = Group::find($data['group_id']);

                        if (!empty($group)) {
                            GroupUser::create([
                                'group_id' => $group->id,
                                'user_id' => $user->id,
                                'created_at' => time(),
                            ]);

                            $notifyOptions = [
                                '[u.g.title]' => $group->name,
                            ];
                            sendNotification("add_to_user_group", $notifyOptions, $user->id);
                        }
                    }

                    return redirect(getAdminPanelUrl() . '/users/' . $user->id . '/edit');
                }
            }

            $toastData = [
                'title' => '',
                'msg' => 'Role not find!',
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $this->authorize('admin_users_edit');
            $percent_webinar_id= '';
            $percent='';
             $sales = Sale::where('buyer_id', $id)
             ->get();

             $sales1 = Sale::where('buyer_id', $id)
             ->get();
             foreach ($sales1 as $sales2) {
                 if($sales2->installment_payment_id){
                    $installment_step_translations = DB::table('installment_order_payments')
            ->selectRaw(' * ')
            ->where('id', $sales2->installment_payment_id )
            ->get();
            foreach ($installment_step_translations as $installment_step_translations1) {
            $percent = $installment_step_translations1->installment_order_id;
            }
            $installment_step_translations1 = DB::table('installment_orders')
            ->selectRaw(' * ')
            ->where('id', $percent)
            ->get();
            foreach ($installment_step_translations1 as $installment_step_translations11) {
            $percent_webinar_id .='~'.$installment_step_translations11->webinar_id;
            }

             }

             }
            $user = User::where('id', $id)
                ->with([
                    'customBadges' => function ($query) {
                        $query->with('badge');
                    },
                    'occupations' => function ($query) {
                        $query->with('category');
                    },
                    'organization' => function ($query) {
                        $query->select('id', 'full_name');
                    },
                    'userRegistrationPackage'
                ])
                ->first();

            if (empty($user)) {
                abort(404);
            }

            if (!empty($user->location)) {
                $user->location = getST_AsTextFromBinary($user->location);

                $user->location = get_geo_array($user->location);
            }

            $userMetas = $user->userMetas;

            if (!empty($userMetas)) {
                foreach ($userMetas as $meta) {
                    $user->{$meta->name} = $meta->value;
                }
            }

            $becomeInstructor = null;
            if (!empty($request->get('type')) and $request->get('type') == 'check_instructor_request') {
                $becomeInstructor = BecomeInstructor::where('user_id', $user->id)
                    ->first();
            }

            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $occupations = $user->occupations->pluck('category_id')->toArray();

            $userBadges = $user->getBadges(false);

            $roles = Role::all();
            $badges = Badge::all();

            $userLanguages = getGeneralSettings('user_languages');
            if (!empty($userLanguages) and is_array($userLanguages)) {
                $userLanguages = getLanguages($userLanguages);
            } else {
                $userLanguages = [];
            }

            $provinces = null;
            $cities = null;
            $districts = null;

            $countries = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', Region::$country)
                ->get();

            if (!empty($user->country_id)) {
                $provinces = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                    ->where('type', Region::$province)
                    ->where('country_id', $user->country_id)
                    ->get();
            }

            if (!empty($user->province_id)) {
                $cities = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                    ->where('type', Region::$city)
                    ->where('province_id', $user->province_id)
                    ->get();
            }

            if (!empty($user->city_id)) {
                $districts = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                    ->where('type', Region::$district)
                    ->where('city_id', $user->city_id)
                    ->get();
            }

            $userBanks = UserBank::query()
                ->with([
                    'specifications'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            $data = [
                'pageTitle' => trans('admin/pages/users.edit_page_title'),
                'user' => $user,
                'userBadges' => $userBadges,
                'roles' => $roles,
                'badges' => $badges,
                'categories' => $categories,
                'occupations' => $occupations,
                'becomeInstructor' => $becomeInstructor,
                'userLanguages' => $userLanguages,
                'userRegistrationPackage' => $user->userRegistrationPackage,
                'countries' => $countries,
                'provinces' => $provinces,
                'cities' => $cities,
                'districts' => $districts,
                'userBanks' => $userBanks,
                'sales' => $sales,
                'percent_webinar_id' => $percent_webinar_id,

            ];

            $data = array_merge($data, $this->getPurchasedClassesData($user));

            $data = array_merge($data, $this->getPurchasedBundlesData($user));

            $data = array_merge($data, $this->getPurchasedProductsData($user));

            $data = array_merge($data, $this->getPurchasedInstaClassesData($user));

            if (auth()->user()->can('admin_forum_topics_lists')) {
                $data['topics'] = ForumTopic::where('creator_id', $user->id)
                    ->with([
                        'posts' => function ($query) {
                            $query->orderBy('created_at', 'desc');
                        },
                        'forum'
                    ])
                    ->withCount('posts')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            return view('admin.users.edit', $data);
        } catch (\Exception $e) {
            \Log::error('edit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function courseprogress(Request $request, $id,$slug)
    {
        try {
            $requestData = $request->all();

            $webinarController = new WebinarController();

            $data = $webinarController->course($slug, true);
            $data['directAccess']=0;

            $course = $data['course'];
            $user = $data['user'];
             $course_pricess = $course->price;
              $cchapt=count($course->chapters);

             $data['limit']=100;

            if ($course->creator_id != $user->id and $course->teacher_id != $user->id and !$user->isAdmin()) {
                $unReadCourseNoticeboards = CourseNoticeboard::where('webinar_id', $course->id)
                    ->whereDoesntHave('noticeboardStatus', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->count();

                if ($unReadCourseNoticeboards) {
                    $url = $course->getNoticeboardsPageUrl();
                    return redirect($url);
                }
            }

            return view('web.default.course.learningPage.course_progress', $data);
        } catch (\Exception $e) {
            \Log::error('courseprogress error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
       public function subcourseprogress(Request $request, $id,$slug)
    {
        try {
            $requestData = $request->all();

            $webinarController = new SubscriptionController();

            $data = $webinarController->course($slug, true);

            $course = $data['course'];
            $user = $data['user'];

             $data['limit']=100;

            if ($course->creator_id != $user->id and $course->teacher_id != $user->id and !$user->isAdmin()) {
                $unReadCourseNoticeboards = CourseNoticeboard::where('webinar_id', $course->id)
                    ->whereDoesntHave('noticeboardStatus', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->count();

                if ($unReadCourseNoticeboards) {
                    $url = $course->getNoticeboardsPageUrl();
                    return redirect($url);
                }
            }

            return view('web.default.subscription.learningPage.course_progress', $data);
        } catch (\Exception $e) {
            \Log::error('subcourseprogress error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function editinsta(Request $request, $id , $instaid)
    {
        try {
            if (!empty($instaid)) {
                $installmentClasses = Sale::whereNull('refund_at')
                ->where('buyer_id', $id)
                ->where('installment_payment_id', $instaid)
                ->whereNotNull('installment_payment_id')
                ->where('sales.access_to_purchased_item', true)
                ->whereHas('installment')
                ->with([
                    'installment'
                ])
                ->whereHas('installment.installmentOrder')
                ->with([
                    'installment.installmentOrder'
                ])

                ->get();

                if ($installmentClasses[0]->installment->type != 'step') {
                    $status = 'paying';
                    $id2=$installmentClasses[0]->installment->installmentOrder->id;

                    DB::delete('delete from installment_orders where id  = ?',[$id2]);

                }

                DB::delete('delete from sales where installment_payment_id  = ?',[$instaid]);
                DB::delete('delete from installment_order_payments where id  = ?',[$instaid]);

            }

            return redirect(getAdminPanelUrl() . '/users/' . $id . '/edit');
        } catch (\Exception $e) {
            \Log::error('editinsta error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function getPurchasedClassesData($user)
    {
        $manualAddedClasses = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->where('sales.manual_added', true)
            ->where('sales.access_to_purchased_item', true)
            ->whereHas('webinar')
            ->with([
                'webinar'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $manualDisabledClasses = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->where('sales.access_to_purchased_item', false)
            ->whereHas('webinar')
            ->with([
                'webinar'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $purchasedClasses = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->where('sales.access_to_purchased_item', true)
            ->where('sales.manual_added', false)
            ->whereHas('webinar')
            ->with([
                'webinar'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'manualAddedClasses' => $manualAddedClasses,
            'purchasedClasses' => $purchasedClasses,
            'manualDisabledClasses' => $manualDisabledClasses,
        ];
    }
    private function getPurchasedInstaClassesData($user)
    {

        $installmentClasses = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('installment_payment_id')
            ->where('sales.access_to_purchased_item', true)
            ->whereHas('installment')
            ->with([
                'installment'
            ])
            ->whereHas('installment.installmentOrder')
            ->with([
                'installment.installmentOrder'
            ])
            ->whereHas('installment.installmentOrder.webinar')
            ->with([
                'installment.installmentOrder.webinar'
            ])
            ->get();

        return [
            'installmentClasses' => $installmentClasses,

        ];
    }

    private function getPurchasedBundlesData($user)
    {
        $manualAddedBundles = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('bundle_id')
            ->where('sales.manual_added', true)
            ->where('sales.access_to_purchased_item', true)
            ->whereHas('bundle')
            ->with([
                'bundle'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $manualDisabledBundles = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('bundle_id')
            ->where('sales.access_to_purchased_item', false)
            ->whereHas('bundle')
            ->with([
                'bundle'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $purchasedBundles = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('bundle_id')
            ->where('sales.access_to_purchased_item', true)
            ->where('sales.manual_added', false)
            ->whereHas('bundle')
            ->with([
                'bundle'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'manualAddedBundles' => $manualAddedBundles,
            'purchasedBundles' => $purchasedBundles,
            'manualDisabledBundles' => $manualDisabledBundles,
        ];
    }

    private function getPurchasedProductsData($user)
    {
        $manualAddedProducts = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('product_order_id')
            ->where('sales.manual_added', true)
            ->where('sales.access_to_purchased_item', true)
            ->whereHas('productOrder')
            ->with([
                'productOrder' => function ($query) {
                    $query->with([
                        'product'
                    ]);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $manualDisabledProducts = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('product_order_id')
            ->where('sales.access_to_purchased_item', false)
            ->whereHas('productOrder')
            ->with([
                'productOrder' => function ($query) {
                    $query->with([
                        'product'
                    ]);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $purchasedProducts = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('product_order_id')
            ->where('sales.access_to_purchased_item', true)
            ->where('sales.manual_added', false)
            ->whereHas('productOrder')
            ->with([
                'productOrder' => function ($query) {
                    $query->with([
                        'product'
                    ]);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'manualAddedProducts' => $manualAddedProducts,
            'purchasedProducts' => $purchasedProducts,
            'manualDisabledProducts' => $manualDisabledProducts,
        ];
    }

    public function update(Request $request, $id)
    {
        try {
            $this->authorize('admin_users_edit');

            $user = User::findOrFail($id);

            $this->validate($request, [
                'full_name' => 'required|min:3|max:128',
                'role_id' => 'required|exists:roles,id',
                'email' => (!empty($user->email)) ? 'required|email|unique:users,email,' . $user->id . ',id,deleted_at,NULL' : 'nullable|email|unique:users',
                'mobile' => (!empty($user->mobile)) ? 'required|numeric|unique:users,mobile,' . $user->id . ',id,deleted_at,NULL' : 'nullable|numeric|unique:users',
                'password' => 'nullable|string',
                'bio' => 'nullable|string|min:3|max:48',
                'about' => 'nullable|string|min:3',
                'certificate_additional' => 'nullable|string|max:255',
                'status' => 'required|' . Rule::in(User::$statuses),
                'ban_start_at' => 'required_if:ban,on',
                'ban_end_at' => 'required_if:ban,on',
            ]);

            $data = $request->all();

            $role = Role::where('id', $data['role_id'])->first();
            $userOldRoleId = $user->role_id;

            if (empty($role)) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => 'Selected role not exist',
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            if ($user->role_id != $role->id and $role->name == Role::$teacher) {
                $becomeInstructor = BecomeInstructor::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->first();

                if (!empty($becomeInstructor)) {
                    $becomeInstructor->update([
                        'status' => 'accept'
                    ]);

                    $becomeInstructor->sendNotificationToUser('accept');
                }
            }

            $user->full_name = !empty($data['full_name']) ? $data['full_name'] : null;
            $user->role_name = $role->name;
            $user->role_id = $role->id;
            if (!empty($data['consultant'])){
            $user->consultant = $data['consultant'];
            }else{
                $user->consultant = 0;
            }
            $user->timezone = $data['timezone'] ?? null;
            $user->currency = $data['currency'] ?? null;
            $user->organ_id = !empty($data['organ_id']) ? $data['organ_id'] : null;
            $user->email = !empty($data['email']) ? $data['email'] : null;
            $user->mobile = !empty($data['mobile']) ? $data['mobile'] : null;
            $user->rating = !empty($data['rating']) ? $data['rating'] : null;
            $user->bio = !empty($data['bio']) ? $data['bio'] : null;
            $user->about = !empty($data['about']) ? $data['about'] : null;
            $user->status = !empty($data['status']) ? $data['status'] : null;
            $user->language = !empty($data['language']) ? $data['language'] : null;

            if (!empty($data['password'])) {
                $user->password = User::generatePassword($data['password']);
                $user->pwd_hint = $data['password'];
            }

            if (!empty($data['ban']) and $data['ban'] == '1') {
                $ban_start_at = strtotime($data['ban_start_at']);
                $ban_end_at = strtotime($data['ban_end_at']);

                $user->ban = true;
                $user->ban_start_at = $ban_start_at;
                $user->ban_end_at = $ban_end_at;
            } else {
                $user->ban = false;
                $user->ban_start_at = null;
                $user->ban_end_at = null;
            }

            $user->verified = (!empty($data['verified']) and $data['verified'] == '1');

            $user->affiliate = (!empty($data['affiliate']) and $data['affiliate'] == '1');

            $user->can_create_store = (!empty($data['can_create_store']) and $data['can_create_store'] == '1');

            $user->access_content = (!empty($data['access_content']) and $data['access_content'] == '1');

            $user->save();

            $this->handleUserCertificateAdditional($user->id, $data['certificate_additional']);

            if ($userOldRoleId != $role->id) {
                $notifyOptions = [
                    '[u.role]' => $role->caption,
                ];
                sendNotification("user_role_change", $notifyOptions, $user->id);
            }

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleUserCertificateAdditional($userId, $value)
    {
        $name = 'certificate_additional';

        if (empty($value)) {
            $checkMeta = UserMeta::where('user_id', $userId)
                ->where('name', $name)
                ->first();

            if (!empty($checkMeta)) {
                $checkMeta->delete();
            }
        } else {
            UserMeta::updateOrCreate([
                'user_id' => $userId,
                'name' => $name
            ], [
                'value' => $value
            ]);
        }
    }

    public function updateImage(Request $request, $id)
    {
        try {
            $this->authorize('admin_users_edit');

            $user = User::findOrFail($id);

            $user->avatar = $request->get('avatar', null);

            if (!empty($request->get('cover_img', null))) {
                $user->cover_img = $request->get('cover_img', null);
            }

            $user->save();

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('updateImage error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function financialUpdate(Request $request, $id)
    {
        try {
            $this->authorize('admin_users_edit');

            $user = User::findOrFail($id);
            $data = $request->all();

            $user->update([
                'identity_scan' => $data['identity_scan'],
                'address' => $data['address'],
                'commission' => $data['commission'] ?? null,
                'financial_approval' => (!empty($data['financial_approval']) and $data['financial_approval'] == 'on'),
                'installment_approval' => (!empty($data['installment_approval']) and $data['installment_approval'] == 'on'),
                'enable_installments' => (!empty($data['enable_installments']) and $data['enable_installments'] == 'on'),
                'disable_cashback' => (!empty($data['disable_cashback']) and $data['disable_cashback'] == 'on'),
                'enable_registration_bonus' => (!empty($data['enable_registration_bonus']) and $data['enable_registration_bonus'] == 'on'),
                'registration_bonus_amount' => !empty($data['registration_bonus_amount']) ? $data['registration_bonus_amount'] : null,
            ]);

            if (!empty($data['bank_id'])) {
                UserSelectedBank::query()->where('user_id', $user->id)->delete();

                $userSelectedBank = UserSelectedBank::query()->create([
                    'user_id' => $user->id,
                    'user_bank_id' => $data['bank_id']
                ]);

                if (!empty($data['bank_specifications'])) {
                    $specificationInsert = [];

                    foreach ($data['bank_specifications'] as $specificationId => $specificationValue) {
                        if (!empty($specificationValue)) {
                            $specificationInsert[] = [
                                'user_selected_bank_id' => $userSelectedBank->id,
                                'user_bank_specification_id' => $specificationId,
                                'value' => $specificationValue
                            ];
                        }
                    }

                    UserSelectedBankSpecification::query()->insert($specificationInsert);
                }
            }

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('financialUpdate error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function occupationsUpdate(Request $request, $id)
    {
        try {
            $this->authorize('admin_users_edit');

            $user = User::findOrFail($id);
            $data = $request->all();

            UserOccupation::where('user_id', $user->id)->delete();
            if (!empty($data['occupations'])) {

                foreach ($data['occupations'] as $category_id) {
                    UserOccupation::create([
                        'user_id' => $user->id,
                        'category_id' => $category_id
                    ]);
                }
            }

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('occupationsUpdate error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function badgesUpdate(Request $request, $id)
    {
        try {
            $this->authorize('admin_users_edit');

            $this->validate($request, [
                'badge_id' => 'required'
            ]);

            $data = $request->all();
            $user = User::findOrFail($id);
            $badge = Badge::findOrFail($data['badge_id']);

            UserBadge::create([
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'created_at' => time()
            ]);

            sendNotification('new_badge', ['[u.b.title]' => $badge->title], $user->id);

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('badgesUpdate error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function deleteBadge(Request $request, $id, $badge_id)
    {
        try {
            $this->authorize('admin_users_edit');

            $user = User::findOrFail($id);

            $badge = UserBadge::where('id', $badge_id)
                ->where('user_id', $user->id)
                ->first();

            if (!empty($badge)) {
                $badge->delete();
            }

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('deleteBadge error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->authorize('admin_users_delete');

            $user = User::find($id);

            if ($user) {
                $user->delete();
            }

            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function acceptRequestToInstructor($id)
    {
        try {
            $this->authorize('admin_users_edit');

            $user = User::findOrFail($id);

            $becomeInstructors = BecomeInstructor::where('user_id', $user->id)->first();

            if (!empty($becomeInstructors)) {
                $role = Role::where('name', $becomeInstructors->role)->first();

                if (!empty($role)) {
                    $user->update([
                        'role_id' => $role->id,
                        'role_name' => $role->name,
                    ]);

                    $becomeInstructors->update([
                        'status' => 'accept'
                    ]);

                    $becomeInstructors->sendNotificationToUser('accept');
                }

                return redirect(getAdminPanelUrl() . '/users/' . $user->id . '/edit')->with(['msg' => trans('admin/pages/users.user_role_updated')]);
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('acceptRequestToInstructor error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function search(Request $request)
    {
        try {
            $term = $request->get('term');
            $option = $request->get('option');

            $users = User::select('id', 'full_name as name')

                ->where(function ($query) use ($term) {
                    $query->where('full_name', 'like', '%' . $term . '%');
                });

            if ($option === "for_user_group") {
                $users->whereNotIn('id', GroupUser::all()->pluck('user_id'));
            }

            if ($option === "just_teacher_role") {
                $users->where('role_name', Role::$teacher);
            }

            if ($option === "just_student_role") {
                $users->where('role_name', Role::$user);
            }

            if ($option === "just_organization_role") {
                $users->where('role_name', Role::$organization);
            }

            if ($option === "just_organization_and_teacher_role") {
                $users->whereIn('role_name', [Role::$organization, Role::$teacher]);
            }

            if ($option === "except_user") {
                $users->where('role_name', '!=', Role::$user);
            }

            if ($option === "consultants") {
                $users->whereHas('meeting', function ($query) {
                    $query->where('disabled', false)
                        ->whereHas('meetingTimes');
                });
            }

            return response()->json($users->get(), 200);
        } catch (\Exception $e) {
            \Log::error('search error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function impersonate($user_id)
    {
        try {
            $this->authorize('admin_users_impersonate');

            $user = User::findOrFail($user_id);

            if ($user->isAdmin()) {
                return redirect(getAdminPanelUrl() . '');
            }

            session()->put(['impersonated' => $user->id]);

            return redirect('/panel');
        } catch (\Exception $e) {
            \Log::error('impersonate error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function exportExcelOrganizations(Request $request)
    {
        try {
            $this->authorize('admin_users_export_excel');

            $users = $this->organizations($request, true);

            $usersExport = new OrganizationsExport($users);

            return Excel::download($usersExport, 'organizations.xlsx');
        } catch (\Exception $e) {
            \Log::error('exportExcelOrganizations error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function exportExcelInstructors(Request $request)
    {
        try {
            $this->authorize('admin_users_export_excel');

            $users = $this->instructors($request, true);

            $usersExport = new OrganizationsExport($users);

            return Excel::download($usersExport, 'instructors.xlsx');
        } catch (\Exception $e) {
            \Log::error('exportExcelInstructors error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function exportExcelStudents(Request $request)
    {
        try {
            $this->authorize('admin_users_export_excel');

            $users = $this->students($request, true);

            $usersExport = new StudentsExport($users);

            return Excel::download($usersExport, 'students.xlsx');
        } catch (\Exception $e) {
            \Log::error('exportExcelStudents error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function userRegistrationPackage(Request $request, $id)
    {
        try {
            $this->authorize('admin_update_user_registration_package');

            $this->validate($request, [
                'instructors_count' => 'nullable|numeric',
                'students_count' => 'nullable|numeric',
                'courses_capacity' => 'nullable|numeric',
                'courses_count' => 'nullable|numeric',
                'meeting_count' => 'nullable|numeric',
            ]);

            $user = User::findOrFail($id);

            if ($user->isOrganization() or $user->isTeacher()) {
                $data = $request->all();

                UserRegistrationPackage::updateOrCreate([
                    'user_id' => $user->id,
                ], [
                    'instructors_count' => $data['instructors_count'] ?? null,
                    'students_count' => $data['students_count'] ?? null,
                    'courses_capacity' => $data['courses_capacity'] ?? null,
                    'courses_count' => $data['courses_count'] ?? null,
                    'meeting_count' => $data['meeting_count'] ?? null,
                    'status' => $data['status'],
                    'created_at' => time(),
                ]);

                return redirect()->back();
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('userRegistrationPackage error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function meetingSettings(Request $request, $id)
    {
        try {
            $this->authorize('admin_update_user_meeting_settings');

            $user = User::findOrFail($id);

            if ($user->isOrganization() or $user->isTeacher()) {
                $data = $request->all();

                $user->update([
                    "level_of_training" => !empty($data['level_of_training']) ? (new UserLevelOfTraining())->getValue($data['level_of_training']) : null,
                    "meeting_type" => $data['meeting_type'] ?? null,
                    "group_meeting" => (!empty($data['group_meeting']) and $data['group_meeting'] == 'on'),
                    "country_id" => $data['country_id'] ?? null,
                    "province_id" => $data['province_id'] ?? null,
                    "city_id" => $data['city_id'] ?? null,
                    "district_id" => $data['district_id'] ?? null,
                    "location" => (!empty($data['latitude']) and !empty($data['longitude'])) ? DB::raw("POINT(" . $data['latitude'] . "," . $data['longitude'] . ")") : null,
                ]);

                $updateUserMeta = [
                    "gender" => $data['gender'] ?? null,
                    "age" => $data['age'] ?? null,
                    "address" => $data['address'] ?? null,
                ];

                foreach ($updateUserMeta as $name => $value) {
                    $checkMeta = UserMeta::where('user_id', $user->id)
                        ->where('name', $name)
                        ->first();

                    if (!empty($checkMeta)) {
                        if (!empty($value)) {
                            $checkMeta->update([
                                'value' => $value
                            ]);
                        } else {
                            $checkMeta->delete();
                        }
                    } else if (!empty($value)) {
                        UserMeta::create([
                            'user_id' => $user->id,
                            'name' => $name,
                            'value' => $value
                        ]);
                    }
                }

                return redirect()->back();
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('meetingSettings error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function disableCashbackToggle($id)
    {
        try {
            $this->authorize('admin_users_edit');

            $user = User::query()->findOrFail($id);

            $user->update([
                'disable_cashback' => !$user->disable_cashback
            ]);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.cashback_was_disabled_for_the_user'),
                'status' => 'success'
            ];

            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('disableCashbackToggle error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function disableRegitrationBonusStatus($id)
    {
        try {
            $this->authorize('admin_users_edit');

            $user = User::query()->findOrFail($id);

            $user->update([
                'enable_registration_bonus' => false
            ]);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.registration_bonus_was_disabled_for_the_user'),
                'status' => 'success'
            ];

            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('disableRegitrationBonusStatus error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function disableInstallmentApproval($id)
    {
        try {
            $this->authorize('admin_users_edit');

            $user = User::query()->findOrFail($id);

            $user->update([
                'installment_approval' => false
            ]);

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.installment_was_disabled_for_the_user'),
                'status' => 'success'
            ];

            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('disableInstallmentApproval error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
