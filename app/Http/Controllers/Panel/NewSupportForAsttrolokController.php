<?php
namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Webinar;
use App\Models\SupportCategory;
use App\Models\NewSupportForAsttrolok;
use App\Models\NewSupportForAsttrolokLog;
use App\Models\Sale;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\InstallmentOrder;
use App\Models\CourseAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\SubStepInstallment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Admin\AdminSupportController;

class NewSupportForAsttrolokController extends Controller
{
    /**
     * Show support form
    */
    public function create()
    {
        $user = auth()->user();
        // print_r($user);die;
        
        // if (!$user) {
            $webinars = Webinar::select('id', 'creator_id')
                ->where('status', 'active')
                ->with(['creator' => function ($query) {
                    $query->select('id', 'full_name');
                }])
                ->get();

            $userPurchases = [];
    
            $webinarIds = $user->getPurchasedCoursesIds();
            
            // $expirewebinarIds = $user->Sale($webinarIds);
            $expiredCourses = [];

            if (!empty(count($webinarIds))) {
                
                $userPurchases = Webinar::select('id', 'creator_id','access_days')
                        ->whereIn('id', $webinarIds)
                        ->where('status', 'active')
                        ->with(['creator' => function ($query) {
                            $query->select('id', 'full_name');
                        }])
                        ->get();
            

            // Use UPE sales to find expired courses for extension dropdown
                foreach ($userPurchases as $item) {

                    // Try to get the latest UPE sale (active or not) for this user+course
                    $productTypes = ['course_video', 'webinar'];
                    $upeProduct = \App\Models\PaymentEngine\UpeProduct::whereIn('product_type', $productTypes)
                        ->where('external_id', $item->id)
                        ->first();

                    if (!$upeProduct) {
                        // Fallback: use legacy access_days check
                        if (!$item->access_days) continue;
                        $sale = $item->getSaleItem($user);
                        if (!$sale || !$sale->created_at) continue;
                        $purchaseTimestamp = $sale->created_at instanceof \Carbon\Carbon
                            ? $sale->created_at->timestamp
                            : (int) $sale->created_at;
                        if (!$item->checkHasExpiredAccessDays($purchaseTimestamp)) {
                            $item->expired_date = $purchaseTimestamp;
                            $expiredCourses[] = $item;
                        }
                        continue;
                    }

                    // Find the latest sale for this user+product (any non-cancelled status)
                    $upeSale = \App\Models\PaymentEngine\UpeSale::where('user_id', $user->id)
                        ->where('product_id', $upeProduct->id)
                        ->whereNotIn('status', ['cancelled'])
                        ->orderByDesc('id')
                        ->first();

                    if (!$upeSale) continue;

                    // Course is expired if valid_until is set and in the past
                    if ($upeSale->valid_until !== null && $upeSale->valid_until->isPast()) {
                        $item->expired_date = $upeSale->valid_until->timestamp;
                        $expiredCourses[] = $item;
                    } elseif ($item->access_days && $upeSale->valid_until === null) {
                        // Fallback: calculate from created_at + access_days
                        $purchaseTimestamp = $upeSale->created_at instanceof \Carbon\Carbon
                            ? $upeSale->created_at->timestamp
                            : (int) $upeSale->created_at;
                        if (!$item->checkHasExpiredAccessDays($purchaseTimestamp)) {
                            $item->expired_date = $purchaseTimestamp;
                            $expiredCourses[] = $item;
                        }
                    }
                }
            }


            $overdueCourses = collect();

            $getOverdueInstallmentsIDs = $this->getOverdueInstallmentsID($user);

            if (!empty(count($getOverdueInstallmentsIDs))) {
                
                $overdueCourses = Webinar::select('id', 'creator_id','access_days')
                        ->whereIn('id', $getOverdueInstallmentsIDs)
                        ->where('status', 'active')
                        ->with(['creator' => function ($query) {
                            $query->select('id', 'full_name');
                        }])
                        ->get();
            }

        
                $mentors = \App\User::where('role_name', 'teacher')
                ->where('status', 'active')
                ->select('id', 'full_name')
                ->orderBy('full_name')
                ->get();


                $query = InstallmentOrder::query()
                ->where('user_id', $user->id)
                ->where('status', '!=', 'paying');

                $installmentorders = $query->with([
                    'installment' => function ($query) {
                        $query->with([
                            'steps' => function ($query) {
                                $query->orderBy('deadline', 'asc');
                            }
                        ]);
                        $query->withCount([
                            'steps'
                        ]);
                    }
                ])->orderBy('created_at', 'desc')
                    ->get();

                    $installmentList =[];
                    foreach ($installmentorders as  $key => $order) {
                        $installmentList[$key] = $order->getItem();
                      
                    }
                
                
                $data = [
                    'pageTitle' => trans('panel.create_support_message'),
                    'webinars' => $webinars,
                    'mentors' => $mentors,
                    'expiredCourses' => $expiredCourses,
                    'overdueCourses' => $overdueCourses,
                    'userPurchasedCourses' => $userPurchases,
                    'userPurchases' => $userPurchases,
                    'installmentList' => $installmentList
                ];
                
                return view(getTemplate() . '.panel.support.new-suport', $data);
    }

        private function getOverdueInstallmentsID($user)
        {
            return InstallmentOrder::query()
                ->where('user_id', $user->id)
                ->where('installment_orders.status', 'open')
                ->get()
                ->filter(fn ($order) => $order->checkOrderHasOverdue())
                ->pluck('webinar_id')
                ->unique()
                ->values()
                ->toArray();
        }
        
        /**
         * Validate coupon code via AJAX
        */
        public function validateCoupon(Request $request)
        {
            $couponCode = strtoupper(trim($request->input('coupon_code')));
            $courseId = $request->input('webinar_id'); // Get selected course
            
            if (empty($couponCode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a coupon code'
                ]);
            }
            
            // Find discount by code
            $discount = \App\Models\Discount::where('code', $couponCode)
                ->where('status', 'active')
                ->where(function($query) {
                    $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', time());
                })
                ->first();
            
            if (!$discount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired coupon code.'
                ]);
            }
            
            // Check if discount is still valid (has remaining count)
            $remainingCount = $discount->discountRemain();
            if ($remainingCount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This coupon has been fully used. No more uses remaining.'
                ]);
            }
            
            // Check if coupon is valid for selected course
            if ($courseId) {
                $discountWebinarsIds = $discount->discountCourses()->pluck('course_id')->toArray();
                
                // If discount has specific courses, check if selected course is included
                if (!empty($discountWebinarsIds) && !in_array($courseId, $discountWebinarsIds)) {
                    // Get course names for which this coupon is valid
                    $validCourses = \App\Models\Webinar::whereIn('id', $discountWebinarsIds)
                        ->with(['translations' => function($query) {
                            $query->where('locale', app()->getLocale());
                        }])
                        ->get()
                        ->map(function($course) {
                            return $course->title ?? 'Course #' . $course->id;
                        })
                        ->take(3)
                        ->toArray();
                    
                    $courseList = implode(', ', $validCourses);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'This coupon is not valid for this course. Valid for: ' . $courseList
                    ]);
                }
            }
            
            // Validate discount type and value
            $discountType = $discount->percent > 0 ? 'percentage' : 'fixed_amount';
            $discountValue = $discount->percent > 0 ? $discount->percent : $discount->amount;
            
            if ($discountType === 'percentage') {
                $message = sprintf(
                    'Valid coupon! You will get %.2f%% discount on your course.',
                    $discountValue
                );
            } else {
                $message = sprintf(
                    'Valid coupon! You will get ₹%s discount on your course.',
                    number_format($discountValue, 2)
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'coupon_title' => $discount->title,
                'remaining_uses' => $remainingCount
            ]);
        }
    
    /**
     * Get support categories based on webinar and purchase status
    */
    public function getCategories(Request $request)
    {
        $webinarId = $request->webinar_id;
        $flowType = $this->determineFlowType($webinarId);
        
        $categories = SupportCategory::where('is_active', true)
        ->where('flow_type', $flowType)
        ->orderBy('sort_order')
        ->get();
        
        return response()->json([
            'categories' => $categories,
            'flow_type' => $flowType,
        ]);
    }
    
    /**
     * Store support request
    */
    public function store(Request $request)
    {
        // dd($request->all());
        if ($request->support_scenario === 'wrong_course_correction') {
            \Log::info('Wrong Course Correction Fields:', [
                'wrong_course_id' => $request->wrong_course_id,
                'correct_course_id' => $request->correct_course_id,
                'correction_reason' => $request->correction_reason,
                'has_correction_reason' => $request->has('correction_reason')
            ]);
        }
        
        if ($request->support_scenario === 'offline_cash_payment') {
            \Log::info('Offline Payment Fields:', [
                'cash_amount' => $request->cash_amount,
                'payment_receipt_number' => $request->payment_receipt_number,
                'payment_date' => $request->payment_date,
                'payment_location' => $request->payment_location,
                'payment_screenshot' => $request->file('payment_screenshot') ? 'File uploaded' : 'No file',
            ]);
        }
        
        if ($request->support_scenario === 'refund_payment') {
            \Log::info('Refund Payment Fields:', [
                'purchase_to_refund' => $request->purchase_to_refund,
                'refund_reason' => $request->refund_reason,
                'bank_account_number' => $request->bank_account_number,
                'ifsc_code' => $request->ifsc_code,
                'account_holder_name' => $request->account_holder_name,
            ]);
        }
        if (isset($request->webinar_id)) {
            $rules = [
                'title' => 'required',
                'support_scenario' => 'required|string',
                'webinar_id' => 'nullable|exists:webinars,id',
                'description' => 'nullable|string',
                'attachments.*' => 'nullable|file|max:5120',
            ];
        } else {
            $rules = [
                'title' => 'required',
                'support_scenario' => 'required|string',
                'description' => 'nullable|string',
                'attachments.*' => 'nullable|file|max:5120',
            ];
        }

        if (!Auth::check()) {
            $rules['guest_name'] = 'nullable|string|max:255';
            $rules['guest_email'] = 'nullable|email|max:255';
            $rules['guest_phone'] = 'nullable|string|max:20';
        }
        
        $scenario = $request->support_scenario;
        $attachmentPaths = [];
        
        switch ($scenario) {
               case 'course_extension':
                    $rules['extension_days'] = 'required|integer|min:1|max:365';
                    $rules['extension_reason'] = 'required|string';
                    break;
                
          
                case 'temporary_access':
                    $rules['webinar_id'] = 'required';
                    $rules['temporary_access_days'] = 'required';
                    $rules['temporary_access_reason'] = 'required';
                    break;
                    
                case 'mentor_access':
                    $rules['mentor_change_reason'] = 'required|string';
                    break;
                        
                case 'relatives_friends_access':
                    $rules['relative_description'] = 'required|string';
                    break;
                            
                case 'free_course_grant':
                    $rules['free_course_reason'] = 'required|string';
                    break;
                
                case 'offline_cash_payment':
                    $rules['cash_amount'] = 'required|numeric|min:0';
                    $rules['payment_date'] = 'required|date|before_or_equal:today';
                    $rules['payment_location'] = 'required|string';
                    $rules['payment_receipt_number'] = 'required|string|max:100';
                    $rules['payment_screenshot'] = 'required|file|image|mimes:jpeg,png,jpg|max:5120';
                    break;
                    
                case 'installment_restructure':
                    // $rules['webinar_id'] = 'required|exists:webinars,id';
                    // $rules['requested_installments'] = 'required|integer';
                    // $rules['installment_amount'] = 'nullable';
                    $rules['restructure_reason'] = 'nullable|string';
                    break;
                    
                case 'new_service_access':
                    $rules['requested_service'] = 'required|string';
                    $rules['service_details'] = 'required|string';
                    break;
                    
                case 'refund_payment':
                    $rules['refund_reason'] = 'nullable|string';
                    $rules['bank_account_number'] = 'required|string';
                    $rules['ifsc_code'] = 'required|string';
                    $rules['account_holder_name'] = 'required|string';
                    break;
                    
                case 'post_purchase_coupon':
                    // $rules['coupon_code'] = 'required|string';
                    // $rules['original_amount'] = 'nullable|numeric|min:0';
                    $rules['coupon_apply_reason'] = 'required|string';
                    break;
                    
                case 'wrong_course_correction':
                    $rules['wrong_course_id'] = 'required|exists:webinars,id';
                    $rules['correct_course_id'] = 'required|exists:webinars,id';
                    $rules['correction_reason'] = 'required|string';
                    break;
            }

// dd($request->all());

        $validated = $request->validate($rules);

        
        if (empty($request->webinar_id)) {
            $request->webinar_id = $request->selected_webinar_id;
        }

    if (
        Auth::check() &&
        $request->support_scenario === 'course_extension'
    ) {
        $approvedCount = NewSupportForAsttrolok::where('user_id', Auth::id())
            ->where('webinar_id', $request->webinar_id)
            ->where('support_scenario', 'course_extension')
            ->whereIn('status', ['approved', 'executed']) 
            ->count();

        if ($approvedCount >= 3) {
            return back()->with([
                'toast' => [
                    'title'  => 'Limit Reached',
                    'msg'    => 'You have already used the maximum 3 extensions for this course.',
                    'status' => 'error'
                ]
            ]);
        }
    }

            $webinar_id = $request->webinar_id ?? $request->selected_webinar_id ?? $request->wrong_course_id ?? null;
                if(isset($webinar_id)){
                    $flowType = $this->determineFlowType($webinar_id);
                    $purchaseInfo = $this->getPurchaseInfo($webinar_id);
                } else {
                    $flowType = 'flow_a';
                    $purchaseInfo = [
                        'status' => 'never_purchased',
                        'purchased_at' => null,
                        'expires_at' => null,
                    ];

                }
            try {
               
              
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $path = $file->store('support-attachments', 'public');
                        $attachmentPaths[] = $path;
                    }
                }
                
                $data = [
                    'user_id' => Auth::id(),
                    'guest_name' => $request->guest_name,
                    'guest_email' => $request->guest_email,
                    'guest_phone' => $request->guest_phone,
                    'support_scenario' => $request->support_scenario,
                    'webinar_id' => $request->webinar_id ?? $request->selected_webinar_id ?? $request->wrong_course_id ?? null, 
                    'title' => $request->title,
                    'description' => $request->description,
                    'attachments' => $attachmentPaths,
                    'flow_type' => $flowType,
                    'purchase_status' => $purchaseInfo['status'],
                    'course_purchased_at' => $purchaseInfo['purchased_at'],
                    'course_expires_at' => $purchaseInfo['expires_at'],
                    'status' => 'pending',
            ];
            if ($request->support_scenario === 'temporary_access') {
                $data['temporary_access_days'] = 7;
                $data['temporary_access_reason'] = $request->temporary_access_reason;
            }
            
            // Add description for mentor and relative scenarios
            if ($request->support_scenario === 'mentor_access') {
                $data['description'] = $request->mentor_change_reason;
                
                // Mentor access request will be created when admin completes the support request
                // Not creating entry here - will be handled in admin completion flow
                
                \Log::info('Mentor access support request created for user: ' . Auth::id() . ' with webinar_id: ' . ($request->webinar_id ?? $request->selected_webinar_id));
            }
            if ($request->support_scenario === 'relatives_friends_access') {
                // Use separate field for relative description
                $relativeDesc = $request->relative_description ?? $request->description ?? null;
                $data['relative_description'] = $relativeDesc;
                $data['description'] = null; 
                
                \Log::info('Relative access request created', [
                    'user_id' => Auth::id(),
                    'webinar_id' => $request->webinar_id ?? $request->selected_webinar_id,
                    'relative_description' =>  $relativeDesc
                ]);
            }
            
            $scenarioFields = [
                'extension_days', 'extension_reason', 'pending_amount', 'expected_payment_date',
                'mentor_change_reason', 'relative_description', 'temporary_access_days',
                'temporary_access_reason',
                'free_course_reason', 'is_special_case',
                'cash_amount', 'payment_date', 'payment_receipt_number', 'payment_location', 'payment_screenshot',
                'requested_installments', 'installment_amount', 'restructure_reason',
                'requested_service', 'service_details','refund_reason', 'purchase_to_refund',
                'bank_account_number', 'ifsc_code', 'account_holder_name', 'coupon_code',
                'original_amount', 'coupon_apply_reason', 'wrong_course_id', 'correct_course_id',
                'correction_reason'
            ];
            
            if ($request->support_scenario === 'offline_cash_payment' && $request->hasFile('payment_screenshot')) {
                $file = $request->file('payment_screenshot');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('support-payments/' . Auth::id(), $fileName, 'public');
                $data['payment_screenshot'] = $filePath;
            }
            
            foreach ($scenarioFields as $field) {
                if ($request->has($field)) {
                    // Only store temporary_access_days for temporary_access scenario
                    if ($field === 'temporary_access_days' && $request->support_scenario !== 'temporary_access') {
                        continue;
                    }
                    // Only store temporary_access_reason for temporary_access scenario
                    if ($field === 'temporary_access_reason' && $request->support_scenario !== 'temporary_access') {
                        continue;
                    }
                    $data[$field] = $request->$field;
                    if ($field === 'correction_reason') {
                        \Log::info('Processing correction_reason: ' . $request->$field);
                    }
                } else {
                    if ($field === 'correction_reason') {
                        \Log::info('correction_reason not found in request');
                        \Log::info('All request data: ' . json_encode($request->all()));
                    }
                }
            }
            
            \Log::info('Final data before saving: ' . json_encode($data));
            
            $supportRequest = NewSupportForAsttrolok::create($data);
            
            
            NewSupportForAsttrolokLog::create([
                    'support_request_id' => $supportRequest->id,
                    'user_id' => Auth::id(),
                    'action' => 'created',
                    'remarks' => 'Support request created by ' . ($supportRequest->isGuest() ? 'guest' : 'user'),
                    'new_data' => $supportRequest->toArray(),
                    'ip_address' => $request->ip(),
                ]);
                
            
            // if (!empty($validated['webinar_id'])) {
                //     $webinar = Webinar::find($validated['webinar_id']);
                //     if ($webinar) {
                    //         $notifyOptions = [
                        //             '[c.title]' => $webinar->title,
                        //             '[u.name]' => $supportRequest->getRequesterName()
                        //         ];
                        //         sendNotification('support_message', $notifyOptions, $webinar->teacher_id);
                        //         sendNotification('support_message', $notifyOptions, 1);
                        //     }
                        // }
                        
                        $toastData = [
                            'title' => trans('public.success'),
                            'msg' => trans('panel.support_request_submitted_successfully'),
                            'status' => 'success'
                        ];

            return redirect()->route('newsuportforasttrolok.show', $supportRequest->ticket_number)->with(['toast' => $toastData]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            foreach ($attachmentPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            
            \Log::error('Support request creation failed: ' . $e->getMessage());
            
            $toastData = [
                'title' => trans('public.error'),
                'msg' => $e->getMessage(),
                'status' => 'error'
            ];
            
            return back()->withInput()->with(['toast' => $toastData]);
        }
    
}
    
    /**
     * Show support request details
    */
    public function show($ticketNumber)
    {
        $supportRequest = NewSupportForAsttrolok::where('ticket_number', $ticketNumber)
        ->with(['webinar'])
        ->firstOrFail();
        
        if (!Auth::check() && !$supportRequest->isGuest()) {
            abort(403, 'Unauthorized access');
        }

        if (Auth::check() && $supportRequest->user_id && $supportRequest->user_id !== Auth::id()) {
            $user = Auth::user();
            $webinarIds = $user->webinars->pluck('id')->toArray();
            
            if (!in_array($supportRequest->webinar_id, $webinarIds)) {
                abort(403, 'Unauthorized access');
            }
        }
        
        $data = [
            'pageTitle' => trans('panel.support_ticket_details') . ' - ' . $supportRequest->ticket_number,
            'supportRequest' => $supportRequest
        ];
        
        return view(getTemplate() . '.panel.support.show', $data);
    }
    
    /**
     * List user's support requests
    */
    public function index()
    {
        $user = Auth::user();
        
        $query = NewSupportForAsttrolok::query();
        
        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        } else {
            $webinarIds = $user->webinars->pluck('id')->toArray();
            $query->where(function($q) use ($user, $webinarIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('webinar_id', $webinarIds);
            });
        }
        
        $supportRequests = $query->with(['webinar', 'category', 'user'])
        ->orderBy('created_at', 'desc')
        ->paginate(15);
        
        // Statistics — merge old (in_review/approved) + new (verified/executed) workflows
        $statsQuery = $user->isUser()
            ? NewSupportForAsttrolok::where('user_id', $user->id)
            : NewSupportForAsttrolok::where(function($q) use ($user, $webinarIds) {
                $q->where('user_id', $user->id)->orWhereIn('webinar_id', $webinarIds ?? []);
            });
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'in_review' => (clone $statsQuery)->whereIn('status', ['in_review', 'verified'])->count(),
            'approved' => (clone $statsQuery)->whereIn('status', ['approved', 'executed'])->count(),
        ];
        
        $data = [
            'pageTitle' => trans('panel.my_support_requests'),
            'supportRequests' => $supportRequests,
            'stats' => $stats
        ];
        
        return view(getTemplate() . '.panel.support.index', $data);
    }
    
    /**
     * Determine flow type based on purchase status
    */
    private function determineFlowType($webinarId)
    {
        if (!Auth::check()) {
            return 'flow_a'; 
        }
        
        $webinar = \App\Models\Webinar::find($webinarId);
        
        if (!$webinar) {
            return 'flow_a'; 
        }
        
        $access = $webinar->checkUserHasBought();
        
        if ($access) {
            return 'flow_c'; 
        }

        // Check if user ever had a sale (now expired) — look at ALL statuses, not just active
        $productTypes = ['course_video', 'webinar'];
        $upeProduct = \App\Models\PaymentEngine\UpeProduct::whereIn('product_type', $productTypes)
            ->where('external_id', $webinarId)
            ->first();

        if ($upeProduct) {
            $anySale = \App\Models\PaymentEngine\UpeSale::where('user_id', Auth::id())
                ->where('product_id', $upeProduct->id)
                ->whereNotIn('status', ['cancelled'])
                ->exists();

            if ($anySale) {
                return 'flow_b';
            }
        }

        // Fallback: check legacy getSaleItem (active sales with expired valid_until)
        $sale = $webinar->getSaleItem();
        if ($sale) {
            return 'flow_b';
        }
        
        return 'flow_a'; 
    }
    
    /**
     * Get purchase information for the course
    */
    private function getPurchaseInfo($webinarId)
    {
        if (!Auth::check()) {
            return [
                'status' => 'never_purchased',
                'purchased_at' => null,
                'expires_at' => null,
            ];
        }
        
        $webinar = \App\Models\Webinar::find($webinarId);
        
        if (!$webinar) {
            return [
                'status' => 'never_purchased',
                'purchased_at' => null,
                'expires_at' => null,
            ];
        }

        $sale = $webinar->getSaleItem();
        
        if (!$sale) {
            return [
                'status' => 'never_purchased',
                'purchased_at' => null,
                'expires_at' => null,
            ];
        }

        $access = $webinar->checkUserHasBought();
        $purchasedAt = $sale->created_at instanceof \Carbon\Carbon
            ? $sale->created_at->timestamp
            : (int) $sale->created_at;
        $expiresAt = $sale->valid_until
            ? ($sale->valid_until instanceof \Carbon\Carbon ? $sale->valid_until->timestamp : (int) $sale->valid_until)
            : null;
        
        if ($access) {
            return [
                'status' => 'active',
                'purchased_at' => $purchasedAt,
                'expires_at' => $expiresAt,
            ];
        }

        return [
            'status' => 'expired',
            'purchased_at' => $purchasedAt,
            'expires_at' => $expiresAt,
        ];
    }
    
    public function getCourseInstallmentPlans($courseId)
    {
        try {
            $userId = Auth::id();
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                    'data' => []
                ]);
            }
            
            $installmentOrder = \App\Models\InstallmentOrder::where('user_id', $userId)
            ->where('webinar_id', $courseId)
            ->whereIn('status', ['open', 'paying'])
            ->with(['installment.steps'])
            ->first();
            
            if ($installmentOrder) {
                $pendingSteps = [];
                
                foreach ($installmentOrder->installment->steps as $step) {
                    $payment = \App\Models\InstallmentOrderPayment::where('installment_order_id', $installmentOrder->id)
                        ->where('step_id', $step->id)
                        ->where('status', 'paid')
                        ->first();
                        
                        if (!$payment) {
                            $pendingSteps[] = [
                                'step_id' => $step->id,
                                'installment_id' => $installmentOrder->installment_id,
                                'installment_order_id' => $installmentOrder->id,
                                'webinar_id' => $courseId,
                                'amount' => $step->amount,
                                'amount_type' => $step->amount_type,
                                'deadline' => $step->deadline,
                                'order' => $step->order,
                                'status' => 'pending'
                        ];
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pending installment steps found',
                    'has_existing_plan' => true,
                    'data' => $pendingSteps
                ]);
            }
            
            // CASE 2: User DOESN'T have installment - Return upfront step
            // Find available installment plan for this course
            $availableInstallment = \App\Models\Installment::where('enable', 1)
            ->where(function($query) use ($courseId) {
                $query->where('target_type', 'courses')
                ->where(function($q) use ($courseId) {
                    $q->where('target', 'specific_courses')
                    ->orWhereRaw("FIND_IN_SET(?, target)", [$courseId]);
                        });
                })
                ->with('steps')
                ->first();
                
                if (!$availableInstallment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No installment plan available for this course',
                        'has_existing_plan' => false,
                        'data' => []
                    ]);
                }
                
                $upfrontStep = $availableInstallment->steps->first();
                
                if (!$upfrontStep) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No steps found in installment plan',
                        'has_existing_plan' => false,
                        'data' => []
                    ]);
                }

                $upfrontData = [
                    'step_id' => $upfrontStep->id,
                    'installment_id' => $availableInstallment->id,
                    'installment_order_id' => null, 
                    'webinar_id' => $courseId,
                    'amount' => $upfrontStep->amount,
                    'amount_type' => $upfrontStep->amount_type,
                    'deadline' => $upfrontStep->deadline,
                    'order' => $upfrontStep->order,
                    'status' => 'upfront'
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Upfront step loaded',
                'has_existing_plan' => false,
                'data' => [$upfrontData] 
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading installment steps: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading installment steps: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
    
    
}