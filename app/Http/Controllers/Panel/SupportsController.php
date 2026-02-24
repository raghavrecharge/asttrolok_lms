<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Support;
use App\Models\SupportConversation;
use App\Models\SupportDepartment;
use App\Models\Webinar;
use App\Models\InstallmentOrder;
use App\Models\InstallmentStep;
use App\Models\InstallmentRestructureRequest;
use App\User;
use Illuminate\Http\Request;
use App\Mail\SendNotifications;
use App\Vbout\VboutService;

class SupportsController extends Controller
{
    protected $vboutService;
    protected $vboutService1;
    
    public function index(Request $request, $id = null)
    {
        try {
            $user = auth()->user();

            $userWebinarsIds = $user->webinars->pluck('id')->toArray();
            $purchasedWebinarsIds = $user->getPurchasedCoursesIds();
            $webinarIds = array_merge($purchasedWebinarsIds, $userWebinarsIds);

            $query = Support::whereNull('department_id')
                ->where(function ($query) use ($user, $userWebinarsIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('webinar_id', $userWebinarsIds);
                });

            $supportsCount = deepClone($query)->count();
            $openSupportsCount = deepClone($query)->where('status', '!=', 'close')->count();
            $closeSupportsCount = deepClone($query)->where('status', 'close')->count();

            $query = $this->filters($query, $request, $userWebinarsIds);

            $supports = $query->orderBy('created_at', 'desc')
                ->orderBy('status', 'asc')
                ->with([
                    'user' => function ($query) {
                        $query->select('id', 'full_name', 'avatar', 'avatar_settings', 'role_name');
                    },
                    'webinar' => function ($query) {
                        $query->with(['teacher' => function ($query) {
                            $query->select('id', 'full_name', 'avatar');
                        }]);
                    },
                    'conversations' => function ($query) {
                        $query->orderBy('created_at', 'desc')
                            ->first();
                    }
                ])->get();

            $webinars = Webinar::select('id')
                ->whereIn('id', array_unique($webinarIds))
                ->where('status', 'active')
                ->get();

            $teacherIds = $webinars->pluck('teacher_id')->toArray();

            $teachers = User::select('id', 'full_name')
                ->where('id', '!=', $user->id)
                ->whereIn('id', array_unique($teacherIds))
                ->where('status', 'active')
                ->get();

            $studentsIds = Sale::whereIn('webinar_id', $userWebinarsIds)
                ->whereNull('refund_at')
                ->pluck('buyer_id')
                ->toArray();

            $students = [];
            if (!$user->isUser()) {
                $students = User::select('id', 'full_name')
                    ->whereIn('id', array_unique($studentsIds))
                    ->where('status', 'active')
                    ->get();
            }

            $data = [
                'pageTitle' => trans('panel.send_new_support'),
                'supports' => $supports,
                'supportsCount' => $supportsCount,
                'openSupportsCount' => $openSupportsCount,
                'closeSupportsCount' => $closeSupportsCount,
                'purchasedWebinarsIds' => $purchasedWebinarsIds,
                'students' => $students,
                'teachers' => $teachers,
                'webinars' => $webinars,
            ];

            if (!empty($id) and is_numeric($id)) {
                $selectSupport = Support::where('id', $id)
                    ->where(function ($query) use ($user, $userWebinarsIds) {
                        $query->where('user_id', $user->id)
                            ->orWhereIn('webinar_id', $userWebinarsIds);
                    })
                    ->with([
                        'department',
                        'conversations' => function ($query) {
                            $query->with([
                                'sender' => function ($qu) {
                                    $qu->select('id', 'full_name', 'avatar', 'role_name');
                                },
                                'supporter' => function ($qu) {
                                    $qu->select('id', 'full_name', 'avatar', 'role_name');
                                }
                            ]);
                            $query->orderBy('created_at', 'asc');
                        },
                        'webinar' => function ($query) {
                            $query->with(['teacher' => function ($query) {
                                $query->select('id', 'full_name', 'avatar', 'role_name');
                            }
                            ]);
                        }])->first();

                if (empty($selectSupport)) {
                    return back();
                }

                $data['selectSupport'] = $selectSupport;
            }

            return view(getTemplate() . '.panel.support.conversations', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function tickets(Request $request, $id = null)
    {
        try {
            $user = auth()->user();

            $query = Support::whereNotNull('department_id')
                ->where('user_id', $user->id);

            $supportsCount = deepClone($query)->count();
            $openSupportsCount = deepClone($query)->where('status', 'open')->count();
            $closeSupportsCount = deepClone($query)->where('status', 'close')->count();

            $query = $this->filters($query, $request);

            $supports = $query->orderBy('created_at', 'desc')
                ->orderBy('status', 'asc')
                ->with([
                    'user' => function ($query) {
                        $query->select('id', 'full_name', 'avatar', 'avatar_settings', 'role_name');
                    },
                    'department',
                    'conversations' => function ($query) {
                        $query->orderBy('created_at', 'desc')
                            ->first();
                    }
                ])->get();

            $departments = SupportDepartment::all();

            $data = [
                'pageTitle' => trans('panel.send_new_support'),
                'departments' => $departments,
                'supports' => $supports,
                'supportsCount' => $supportsCount,
                'openSupportsCount' => $openSupportsCount,
                'closeSupportsCount' => $closeSupportsCount,
            ];

            if (!empty($id) and is_numeric($id)) {
                $selectSupport = Support::where('id', $id)
                    ->whereNotNull('department_id')
                    ->where('user_id', $user->id)
                    ->with([
                        'department',
                        'conversations' => function ($query) {
                            $query->with([
                                'sender' => function ($qu) {
                                    $qu->select('id', 'full_name', 'avatar', 'role_name');
                                },
                                'supporter' => function ($qu) {
                                    $qu->select('id', 'full_name', 'avatar', 'role_name');
                                }
                            ]);
                            $query->orderBy('created_at', 'asc');
                        }
                    ])->first();

                if (empty($selectSupport)) {
                    return back();
                }

                $data['selectSupport'] = $selectSupport;
            }

            return view(getTemplate() . '.panel.support.ticket_conversations', $data);
        } catch (\Exception $e) {
            \Log::error('tickets error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function filters($query, $request, $userWebinarsIds = [])
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $role = $request->get('role');
        $student_id = $request->get('student');
        $teacher_id = $request->get('teacher');
        $webinar_id = $request->get('webinar');
        $department = $request->get('department');
        $status = $request->get('status');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($role) and $role == 'student' and (empty($student_id) or $student_id == 'all')) {
            $studentsIds = Sale::whereIn('webinar_id', $userWebinarsIds)
                ->whereNull('refund_at')
                ->pluck('buyer_id')
                ->toArray();

            $query->whereIn('user_id', $studentsIds);
        }

        if (!empty($student_id) and $student_id != 'all') {
            $query->where('user_id', $student_id);
        }

        if (!empty($teacher_id) and $teacher_id != 'all') {
            $teacher = User::where('id', $teacher_id)
                ->where('status', 'active')
                ->first();

            $teacherWebinarIds = $teacher->webinars->pluck('id')->toArray();

            $query->whereIn('webinar_id', $teacherWebinarIds);
        }

        if (!empty($webinar_id) and $webinar_id != 'all') {
            $query->where('webinar_id', $webinar_id);
        }

        if (!empty($status) and $status != 'all') {
            $query->where('status', $status);
        }

        if (!empty($department) and $department != 'all') {
            $query->where('department_id', $department);
        }

        return $query;
    }

    public function create()
    {
        try {
            $departments = SupportDepartment::all();
            $user = auth()->user();

            $webinarIds = $user->getPurchasedCoursesIds();

            $webinars = Webinar::select('id', 'creator_id')
                ->whereIn('id', $webinarIds)
                ->where('support', true)
                ->with(['creator' => function ($query) {
                    $query->select('id', 'full_name');
                }])->get();

            $data = [
                'pageTitle' => trans('panel.send_new_support'),
                'departments' => $departments,
                'webinars' => $webinars
            ];

            return view(getTemplate() . '.panel.support.new', $data);
        } catch (\Exception $e) {
            \Log::error('create error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function createnewsuport()
    {
        try {
            $departments = SupportDepartment::all();
            $user = auth()->user();

            $webinarIds = $user->getPurchasedCoursesIds();

            $webinars = Webinar::select('id', 'creator_id')
                ->whereIn('id', $webinarIds)
                ->where('support', true)
                ->with(['creator' => function ($query) {
                    $query->select('id', 'full_name');
                }])->get();

            $data = [
                'pageTitle' => trans('panel.send_new_support'),
                'departments' => $departments,
                'webinars' => $webinars
            ];

            return view(getTemplate() . '.panel.support.new-suport', $data);
        } catch (\Exception $e) {
            \Log::error('create error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }


    public function store(Request $request)
    {
        $user = auth()->user();

        $this->validate($request, [
            'title' => 'required|min:2',
            'type' => 'required',
            'department_id' => 'required_if:type,platform_support|exists:support_departments,id',
            'webinar_id' => 'required_if:type,course_support|exists:webinars,id',
            'installment_order_id' => 'required_if:type,installment_restructure|exists:installment_orders,id',
            'installment_step_id' => 'required_if:type,installment_restructure|exists:installment_steps,id',
            'reason' => 'required_if:type,installment_restructure|min:10',
            'message' => 'required|min:2',
            'attach' => 'nullable|string',
        ]);

        $data = $request->all();
        $type = $data['type'];
        unset($data['type']);

        // Get webinar_id for installment restructure
        $webinarId = null;
        if ($type === 'installment_restructure' && !empty($data['installment_order_id'])) {
            $installmentOrder = InstallmentOrder::find($data['installment_order_id']);
            if ($installmentOrder) {
                $webinarId = $installmentOrder->webinar_id;
            }
        } elseif (!empty($data['webinar_id'])) {
            $webinarId = $data['webinar_id'];
        }

        $support = Support::create([
            'user_id' => $user->id,
            'department_id' => !empty($data['department_id']) ? $data['department_id'] : null,
            'webinar_id' => $webinarId,
            'title' => $data['title'],
            'status' => 'open',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        SupportConversation::create([
            'support_id' => $support->id,
            'sender_id' => $user->id,
            'message' => $data['message'],
            'attach' => $data['attach'],
            'created_at' => time(),
        ]);

        // Handle installment restructure request
        if ($type === 'installment_restructure') {
            try {
                $restructureRequest = $this->createInstallmentRestructureRequest($data, $user, $support);
                
                Log::info('Installment restructure request created successfully', [
                    'request_id' => $restructureRequest->id,
                    'support_id' => $support->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create restructure request: ' . $e->getMessage());
                // Continue with support ticket creation even if restructure fails
            }
        }

        if (!empty($webinarId)) {
            $webinar = Webinar::find($webinarId);
            
            if ($webinar) {
                $notifyOptions = [
                    '[c.title]' => $webinar->title,
                    '[u.name]' => $user->full_name
                ];
                sendNotification('support_message', $notifyOptions, $webinar->teacher_id);
                sendNotification('support_message', $notifyOptions, 1);
            }
        }

        if (!empty($data['department_id'])) {
            $notifyOptions = [
                '[s.t.title]' => $support->title,
            ];
            sendNotification('support_message_admin', $notifyOptions, 1);
        }

        $url = '/panel/support';

        if (!empty($data['department_id'])) {
            $url = '/panel/support/tickets';
        }
        
        try {
            $vboutService = new VboutService();
            $listId = '140644';
            $contactData = [
                'email' => $user->email,
                'fields' => [
                    '934792' => $data['title'],
                    '934793' => $data['message'],
                ],
            ];
            $result = $vboutService->addContactToList($listId, $contactData);

            $vboutService1 = new VboutService();
            $listId1 = '140647';
            $contactData1 = [
                'email' => 'hitesh@rechargestudio.com',
                'fields' => [
                    '934796' => $data['title'],
                    '934797' => $data['message'],
                    '962472' => $user->email,
                ],
            ];
            $result1 = $vboutService1->addContactToList($listId1, $contactData1);

        } catch (\Exception $e) {
            // Log but don't stop execution
            Log::warning('VBout service error: ' . $e->getMessage());
        }
        
        return redirect($url);
    }

    public function storeConversations(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'message' => 'required|string|min:2',
            ]);

            $data = $request->all();
            $user = auth()->user();

            $userWebinarsIds = $user->webinars->pluck('id')->toArray();

            $support = Support::where('id', $id)
                ->where(function ($query) use ($user, $userWebinarsIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('webinar_id', $userWebinarsIds);
                })->first();

            if (empty($support)) {
                abort(404);
            }

            $support->update([
                'status' => ($support->user_id == $user->id) ? 'open' : 'replied',
                'updated_at' => time()
            ]);

            SupportConversation::create([
                'support_id' => $support->id,
                'sender_id' => $user->id,
                'message' => $data['message'],
                'attach' => $data['attach'],
                'created_at' => time(),
            ]);

            if (!empty($support->webinar_id)) {
                $webinar = Webinar::findOrFail($support->webinar_id);

                $notifyOptions = [
                    '[c.title]' => $webinar->title,
                ];
                sendNotification('support_message_replied', $notifyOptions, ($support->user_id == $user->id) ? $webinar->teacher_id : $user->id);
                sendNotification('support_message_replied', $notifyOptions, ($support->user_id == $user->id) ? 1 : $user->id);
            }

            if (!empty($support->department_id)) {
                $notifyOptions = [
                    '[s.t.title]' => $support->title,
                ];
                sendNotification('support_message_replied_admin', $notifyOptions, 1);
            }

            return back();
        } catch (\Exception $e) {
            \Log::error('storeConversations error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function close($id)
    {
        try {
            $user = auth()->user();
            $userWebinarsIds = $user->webinars->pluck('id')->toArray();

            $support = Support::where('id', $id)
                ->where(function ($query) use ($user, $userWebinarsIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereIn('webinar_id', $userWebinarsIds);
                })->first();

            if (empty($support)) {
                abort(404);
            }

            $support->update([
                'status' => 'close',
                'updated_at' => time()
            ]);

            return back();
        } catch (\Exception $e) {
            \Log::error('close error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function createInstallmentRestructureRequest($data, $user, $support)
    {
        try {
            $installmentOrder = InstallmentOrder::findOrFail($data['installment_order_id']);
            $installmentStep = InstallmentStep::findOrFail($data['installment_step_id']);

            Log::info('Creating installment restructure request', [
                'user_id' => $user->id,
                'order_id' => $installmentOrder->id,
                'step_id' => $installmentStep->id,
                'support_id' => $support->id,
            ]);

            // Get webinar price for amount calculation
            $webinar = Webinar::find($installmentOrder->webinar_id);
            $webinarPrice = $webinar->price ?? 0;

            // Calculate step amount
            if ($installmentStep->amount_type == 'percent') {
                $stepAmount = ($webinarPrice * $installmentStep->amount) / 100;
            } else {
                $stepAmount = $installmentStep->amount;
            }

            // Calculate original deadline (in unix timestamp)
            $orderCreatedAt = strtotime($installmentOrder->created_at);
            $originalDeadline = $orderCreatedAt + ($installmentStep->deadline * 86400);

            Log::info('Calculated restructure values', [
                'step_amount' => $stepAmount,
                'original_deadline' => date('Y-m-d', $originalDeadline),
                'webinar_price' => $webinarPrice,
            ]);

            // Create restructure request
            $restructureRequest = InstallmentRestructureRequest::create([
                'installment_order_id' => $data['installment_order_id'],
                'installment_step_id' => $data['installment_step_id'],
                'user_id' => $user->id,
                'webinar_id' => $installmentOrder->webinar_id,
                'reason' => $data['reason'] ?? 'User requested split payment',
                'original_amount' => $stepAmount,
                'original_deadline' => $originalDeadline,
                'number_of_sub_steps' => 2, // Default split into 2 parts
                'status' => InstallmentRestructureRequest::STATUS_PENDING,
                'support_ticket_id' => $support->id,
            ]);

            // Generate sub-steps data (50-50 split by default)
            $restructureRequest->generateSubStepsData(2, [50, 50]);
            $restructureRequest->save();

            Log::info('Installment restructure request created successfully', [
                'request_id' => $restructureRequest->id,
                'user_id' => $user->id,
                'installment_order_id' => $data['installment_order_id'],
                'installment_step_id' => $data['installment_step_id'],
                'sub_steps_data' => $restructureRequest->sub_steps_data,
            ]);

            return $restructureRequest;

        } catch (\Exception $e) {
            Log::error('createInstallmentRestructureRequest error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);
            
            throw $e;
        }
    }

    public function getInstallmentOrders(Request $request)
    {
        try {
            $user = auth()->user();
            
            $orders = InstallmentOrder::where('user_id', $user->id)
                ->whereIn('status', ['open', 'paying'])
                ->with(['webinar', 'installment.steps'])
                ->get()
                ->map(function ($order) use ($user) {
                    // Get paid steps count
                    $paidStepsCount = \App\Models\InstallmentOrderPayment::where('installment_order_id', $order->id)
                        ->where('status', 'paid')
                        ->count();
                    
                    $totalSteps = $order->installment ? $order->installment->steps->count() : 0;
                    
                    return [
                        'id' => $order->id,
                        'source' => 'legacy',
                        'course_title' => $order->webinar ? $order->webinar->title : 'Unknown Course',
                        'total_amount' => $order->total_amount,
                        'paid_steps' => $paidStepsCount,
                        'total_steps' => $totalSteps,
                        'created_at' => dateTimeFormat($order->created_at, 'j M Y'),
                    ];
                });

            // Also include UPE installment plans (e.g. Quick Pay purchases)
            $seenWebinarIds = $orders->map(function ($o) {
                return $o['course_title']; // used for dedup below
            })->toArray();

            try {
                $upePlans = \App\Models\PaymentEngine\UpeInstallmentPlan::whereHas('sale', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })
                    ->whereIn('status', ['active', 'completed'])
                    ->with(['sale.product', 'schedules'])
                    ->get();

                $legacyWebinarIds = InstallmentOrder::where('user_id', $user->id)
                    ->whereIn('status', ['open', 'paying'])
                    ->pluck('webinar_id')
                    ->toArray();

                foreach ($upePlans as $upePlan) {
                    $product = $upePlan->sale->product ?? null;
                    if (!$product || !in_array($product->product_type, ['course_video', 'webinar'])) {
                        continue;
                    }
                    $webinarId = $product->external_id;
                    if (in_array($webinarId, $legacyWebinarIds)) {
                        continue;
                    }

                    $webinar = \App\Models\Webinar::find($webinarId);
                    $paidCount = $upePlan->schedules->where('status', 'paid')->count();
                    $totalCount = $upePlan->schedules->count();

                    $orders->push([
                        'id' => 'upe_' . $upePlan->id,
                        'source' => 'upe',
                        'course_title' => $webinar ? $webinar->title : ('Course #' . $webinarId),
                        'total_amount' => (float) $upePlan->total_amount,
                        'paid_steps' => $paidCount,
                        'total_steps' => $totalCount,
                        'created_at' => $upePlan->created_at->format('j M Y'),
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to load UPE installment plans in getInstallmentOrders', ['error' => $e->getMessage()]);
            }

            return response()->json(['success' => true, 'orders' => $orders]);

        } catch (\Exception $e) {
            Log::error('getInstallmentOrders error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json(['success' => false, 'error' => 'Failed to load installment orders'], 500);
        }
    }

    public function getInstallmentSteps($orderId)
    {
        try {
            $user = auth()->user();
            
            $order = InstallmentOrder::where('id', $orderId)
                ->where('user_id', $user->id)
                ->with(['installment.steps'])
                ->firstOrFail();

            // Get paid step IDs
            $paidStepIds = \App\Models\InstallmentOrderPayment::where('installment_order_id', $order->id)
                ->where('status', 'paid')
                ->pluck('step_id')
                ->toArray();

            $steps = [];
            $previousStepPaid = true;
            
            if ($order->installment && $order->installment->steps) {
                foreach ($order->installment->steps as $step) {
                    $isStepPaid = in_array($step->id, $paidStepIds);
                    
                    // Check if step has approved sub-steps
                    $hasSubSteps = \App\Models\SubStepInstallment::where('installment_step_id', $step->id)
                        ->where('user_id', $user->id)
                        ->where('status', 'approved')
                        ->exists();
                    
                    // Only show unpaid steps that come after paid steps and don't have sub-steps
                    $isEligible = !$isStepPaid && $previousStepPaid && !$hasSubSteps;
                    
                    // Calculate step amount
                    $webinar = $order->webinar;
                    $webinarPrice = $webinar->price ?? 0;
                    
                    if ($step->amount_type == 'percent') {
                        $stepAmount = ($webinarPrice * $step->amount) / 100;
                    } else {
                        $stepAmount = $step->amount;
                    }
                    
                    $steps[] = [
                        'id' => $step->id,
                        'title' => $step->title ?? 'Step ' . $step->order,
                        'order' => $step->order,
                        'amount' => $stepAmount,
                        'formatted_amount' => handlePrice($stepAmount),
                        'deadline' => $step->deadline,
                        'is_paid' => $isStepPaid,
                        'has_substeps' => $hasSubSteps,
                        'is_eligible' => $isEligible,
                    ];
                    
                    $previousStepPaid = $isStepPaid;
                }
            }

            return response()->json([
                'success' => true, 
                'steps' => $steps,
                'order_info' => [
                    'id' => $order->id,
                    'webinar_title' => $order->webinar ? $order->webinar->title : 'Unknown',
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('getInstallmentSteps error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json(['success' => false, 'error' => 'Failed to load installment steps'], 500);
        }
    }
}