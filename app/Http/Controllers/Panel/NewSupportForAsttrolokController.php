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
        $data = [
            'pageTitle' => trans('panel.create_support_message'),
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
     * Store support request (simplified — student submits title + description + attachments only)
     * Scenario selection is now handled by admin/support via processTicket()
    */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'webinar_id' => 'nullable|exists:webinars,id',
            'attachments.*' => 'nullable|file|max:5120',
        ];

        if (!Auth::check()) {
            $rules['guest_name'] = 'nullable|string|max:255';
            $rules['guest_email'] = 'nullable|email|max:255';
            $rules['guest_phone'] = 'nullable|string|max:20';
        }

        $validated = $request->validate($rules);

        $attachmentPaths = [];

        try {
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('support-attachments', 'public');
                    $attachmentPaths[] = $path;
                }
            }

            $webinarId = $validated['webinar_id'] ?? null;
            $flowType = $webinarId ? $this->determineFlowType($webinarId) : null;
            $purchaseInfo = $webinarId ? $this->getPurchaseInfo($webinarId) : [
                'status' => null,
                'purchased_at' => null,
                'expires_at' => null,
            ];

            $data = [
                'user_id' => Auth::id(),
                'guest_name' => $request->guest_name,
                'guest_email' => $request->guest_email,
                'guest_phone' => $request->guest_phone,
                'webinar_id' => $webinarId,
                'support_scenario' => null,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'attachments' => $attachmentPaths,
                'status' => 'pending',
                'flow_type' => $flowType,
                'purchase_status' => $purchaseInfo['status'],
                'course_purchased_at' => $purchaseInfo['purchased_at'],
                'course_expires_at' => $purchaseInfo['expires_at'],
            ];

            $supportRequest = NewSupportForAsttrolok::create($data);

            NewSupportForAsttrolokLog::create([
                'support_request_id' => $supportRequest->id,
                'user_id' => Auth::id(),
                'action' => 'created',
                'remarks' => 'Support request created by ' . ($supportRequest->isGuest() ? 'guest' : 'user'),
                'new_data' => $supportRequest->toArray(),
                'ip_address' => $request->ip(),
            ]);

            $toastData = [
                'title' => trans('public.success'),
                'msg' => trans('panel.support_request_submitted_successfully'),
                'status' => 'success'
            ];

            return redirect()->route('newsuportforasttrolok.show', $supportRequest->ticket_number)->with(['toast' => $toastData]);

        } catch (\Exception $e) {
            foreach ($attachmentPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            \Log::error('Support request creation failed: ' . $e->getMessage());

            return back()->withInput()->with(['toast' => [
                'title' => trans('public.error'),
                'msg' => $e->getMessage(),
                'status' => 'error'
            ]]);
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
        $userWebinarsIds = $user->webinars->pluck('id')->toArray();
        
        // If ticket has a webinar, check if user is the teacher or buyer
        if ($supportRequest->webinar_id && !in_array($supportRequest->webinar_id, $userWebinarsIds)) {
            // Check if user is the buyer (for legacy support logic maybe)
            // But usually user_id check above handles most cases
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
    public function index(Request $request)
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

        $from = $request->get('from');
        $to = $request->get('to');
        $webinarId = $request->get('webinar_id');
        $scenario = $request->get('support_scenario');
        $status = $request->get('status');

        if (!empty($from)) {
            $query->where('created_at', '>=', strtotime($from));
        }

        if (!empty($to)) {
            $query->where('created_at', '<=', strtotime($to));
        }

        if (!empty($webinarId) and $webinarId !== 'all') {
            $query->where('webinar_id', $webinarId);
        }

        if (!empty($scenario) and $scenario !== 'all') {
            $query->where('support_scenario', $scenario);
        }

        if (!empty($status) and $status !== 'all') {
            $query->where('status', $status);
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
            'approved' => (clone $statsQuery)->whereIn('status', ['approved', 'executed', 'verified'])->count(),
            'completed' => (clone $statsQuery)->where('status', 'completed')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];
        
        $data = [
            'pageTitle' => trans('panel.my_support_requests'),
            'supportRequests' => $supportRequests,
            'stats' => $stats,
            'userPurchasedCourses' => $user->getPurchasedCourses(),
            'scenarios' => SupportCategory::where('is_active', true)->get()
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

        // Also check installment orders — user may have purchased via installment plan
        if (!$sale) {
            $installmentOrder = \App\Models\InstallmentOrder::where('user_id', Auth::id())
                ->where('webinar_id', $webinarId)
                ->whereIn('status', ['open', 'paying', 'pending_verification'])
                ->first();

            if ($installmentOrder) {
                return [
                    'status' => 'active',
                    'purchased_at' => $installmentOrder->created_at,
                    'expires_at' => null,
                ];
            }
        }
        
        if (!$sale) {
            return [
                'status' => 'never_purchased',
                'purchased_at' => null,
                'expires_at' => null,
            ];
        }

        $access = $webinar->checkUserHasBought();
        $purchasedAt = $sale->created_at;
        $expiresAt = $sale->valid_until;

        if (is_numeric($purchasedAt)) {
            $purchasedAt = \Carbon\Carbon::createFromTimestamp($purchasedAt);
        }
        if (is_numeric($expiresAt)) {
            $expiresAt = \Carbon\Carbon::createFromTimestamp($expiresAt);
        }
        
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