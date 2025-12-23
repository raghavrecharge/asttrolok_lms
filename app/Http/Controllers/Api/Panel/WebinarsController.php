<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\BundleResource;
use App\Http\Resources\WebinarResource;
use App\Models\Api\Sale;
use App\Models\Api\Webinar;
use App\Models\WebinarChapter;
use App\Models\WebinarPartnerTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mixins\Cashback\CashbackRules;
use App\Mixins\Installment\InstallmentPlans;
use App\Http\Controllers\Api\Traits\CheckContentLimitationTrait;
use App\Http\Controllers\Api\Traits\InstallmentsTrait;

use App\Exports\WebinarStudents;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\BundleWebinar;
use App\Models\Category;
use App\Models\Faq;
use App\Models\File;
use App\Models\Gift;
use App\Models\Prerequisite;
use App\Models\Quiz;
use App\Models\Role;

use App\Models\Session;
use App\Models\Tag;
use App\Models\TextLesson;
use App\Models\Ticket;
use App\Models\Translation\WebinarTranslation;

use App\Models\WebinarChapterItem;
use App\Models\WebinarExtraDescription;
use App\User;

use App\Models\WebinarFilterOption;

use Maatwebsite\Excel\Facades\Excel;
use Validator;
use App\Mixins\Installment\InstallmentAccounting;
use App\Models\Cart;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Models\InstallmentStep;
use App\Models\WebinarPartPayment;
use App\Models\Meeting;
use App\Models\ReserveMeeting;
use App\Http\Controllers\Web\SubscriptionController;
use App\Models\SubscriptionAccess;
use App\Models\Subscription;


class WebinarsController extends Controller
{
    use CheckContentLimitationTrait;
    use InstallmentsTrait;

    public function show($id)
    {
        try {
            die;
            $user = apiAuth();
            $webinar = Webinar::where('id', $id)
                ->with([
                    'quizzes' => function ($query) {
                        $query->where('status', 'active')
                            ->with(['quizResults', 'quizQuestions']);
                    },
                    'tags',
                    'prerequisites' => function ($query) {
                        $query->with(['prerequisiteWebinar' => function ($query) {
                            $query->with(['teacher' => function ($qu) {
                                $qu->select('id', 'full_name', 'avatar');
                            }]);
                        }]);
                        $query->orderBy('order', 'asc');
                    },
                    'faqs' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'webinarExtraDescription' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'chapters' => function ($query) use ($user) {
                        $query->where('status', WebinarChapter::$chapterActive);
                        $query->orderBy('order', 'asc');

                        $query->with([
                            'chapterItems' => function ($query) {
                                $query->orderBy('order', 'asc');
                            }
                        ]);
                    },
                    'files' => function ($query) use ($user) {
                        $query->join('webinar_chapters', 'webinar_chapters.id', '=', 'files.chapter_id')
                            ->select('files.*', DB::raw('webinar_chapters.order as chapterOrder'))
                            ->where('files.status', WebinarChapter::$chapterActive)
                            ->orderBy('chapterOrder', 'asc')
                            ->orderBy('files.order', 'asc')
                            ->with([
                                'learningStatus' => function ($query) use ($user) {
                                    $query->where('user_id', !empty($user) ? $user->id : null);
                                }
                            ]);
                    },
                    'textLessons' => function ($query) use ($user) {
                        $query->where('status', WebinarChapter::$chapterActive)
                            ->withCount(['attachments'])
                            ->orderBy('order', 'asc')
                            ->with([
                                'learningStatus' => function ($query) use ($user) {
                                    $query->where('user_id', !empty($user) ? $user->id : null);
                                }
                            ]);
                    },
                    'sessions' => function ($query) use ($user) {
                        $query->where('status', WebinarChapter::$chapterActive)
                            ->orderBy('order', 'asc')
                            ->with([
                                'learningStatus' => function ($query) use ($user) {
                                    $query->where('user_id', !empty($user) ? $user->id : null);
                                }
                            ]);
                    },
                    'assignments' => function ($query) {
                        $query->where('status', WebinarChapter::$chapterActive);
                    },
                    'tickets' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'filterOptions',
                    'category',

                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                        $query->with([
                            'comments' => function ($query) {
                                $query->where('status', 'active');
                            },
                            'creator' => function ($qu) {
                                $qu->select('id', 'full_name', 'avatar');
                            }
                        ]);
                    },
                    'comments' => function ($query) {
                        $query->where('status', 'active');
                        $query->whereNull('reply_id');
                        $query->with([
                            'user' => function ($query) {
                                $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                            },
                            'replies' => function ($query) {
                                $query->where('status', 'active');
                                $query->with([
                                    'user' => function ($query) {
                                        $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                                    }
                                ]);
                            }
                        ]);
                        $query->orderBy('created_at', 'desc');
                    },
                ])
                ->withCount([
                    'sales' => function ($query) {
                        $query->whereNull('refund_at');
                    },
                    'noticeboards'
                ])
                ->where('status', 'active')
                ->first();

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [$webinar]);
        } catch (\Exception $e) {
            \Log::error('show error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function list(Request $request, $id = null)
    {
        try {
            return [
                'my_classes' => $this->myClasses($request),
                'purchases' => $this->purchases($request),
                'organizations' => $this->organizations($request),
                'invitations' => $this->invitations($request),
            ];
        } catch (\Exception $e) {
            \Log::error('list error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function myClasses(Request $request)
    {
        try {
            $user = apiAuth();

            $webinars = Webinar::where(function ($query) use ($user) {

                if ($user->isTeacher()) {
                    $query->where('teacher_id', $user->id);
                } elseif ($user->isOrganization()) {
                    $query->where('creator_id', $user->id);
                }
            })->handleFilters()->orderBy('updated_at', 'desc')->get()->map(function ($webinar) {
                return $webinar->brief;
            });

            return $webinars;
        } catch (\Exception $e) {
            \Log::error('myClasses error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function indexPurchases()
    {
        try {
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                $this->purchases());
        } catch (\Exception $e) {
            \Log::error('indexPurchases error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function free(Request $request, $id)
    {
        try {
            $user = apiAuth();

            $course = Webinar::where('id', $id)
                ->where('status', 'active')
                ->first();
            abort_unless($course, 404);

            $checkCourseForSale = $course->checkCourseForSale($user);

            if ($checkCourseForSale != 'ok') {
                return apiResponse2(0, $checkCourseForSale, trans('api.course.purchase.' . $checkCourseForSale));
            }

            if (!empty($course->price) and $course->price > 0) {
                return apiResponse2(0, 'not_free', trans('api.cart.not_free'));

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

            return apiResponse2(1, 'enrolled', trans('api.webinar.enrolled'));
        } catch (\Exception $e) {
            \Log::error('free error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function PurchaseCourseSummary()
    {
        try {
            $user = apiAuth();

            $giftsIds = Gift::query()->where('email', $user->email)
                ->where('status', 'active')
                ->whereNull('product_id')
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')
                ->toArray();

            $query = Sale::query()
                ->where(function ($query) use ($user, $giftsIds) {
                    $query->where('sales.buyer_id', $user->id);
                    $query->orWhereIn('sales.gift_id', $giftsIds);
                })
                ->whereNull('sales.refund_at')
                ->where('access_to_purchased_item', true)
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->whereNotNull('sales.webinar_id')
                            ->where('sales.type', 'webinar')
                            ->whereHas('webinar', function ($query) {
                                $query->where('status', 'active');
                            });
                    });
                    $query->orWhere(function ($query) {
                        $query->whereNotNull('sales.bundle_id')
                            ->where('sales.type', 'bundle')
                            ->whereHas('bundle', function ($query) {
                                $query->where('status', 'active');
                            });
                    });
                    $query->orWhere(function ($query) {
                        $query->whereNotNull('gift_id');
                        $query->whereHas('gift');
                    });
                });

            $sales = deepClone($query)
            ->with([
            'webinar' => function ($query) {
                $query->with([
                    'files',
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'category',
                    'teacher' => function ($query) {
                        $query->select('id', 'full_name', 'avatar');
                    },
                    'quizzes' => function ($query) {
                        $query->where('status', 'active')
                              ->with(['quizQuestions']);
                    },
                ]);

                $query->withCount([
                    'sales' => function ($query) {
                        $query->whereNull('refund_at');
                    }
                ]);
            },
            'bundle' => function ($query) {
                $query->with([
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                    },
                    'category',
                    'teacher' => function ($query) {
                        $query->select('id', 'full_name');
                    },
                ]);
            }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

            $time = time();

            $giftDurations = 0;
            $giftUpcoming = 0;
            $giftPurchasedCount = 0;

            foreach ($sales as $sale) {
            if (!empty($sale->gift_id)) {
            $gift = $sale->gift;

            $sale->webinar_id = $gift->webinar_id;
            $sale->bundle_id  = $gift->bundle_id;

            $sale->webinar = !empty($gift->webinar_id) ? $gift->webinar : null;
            $sale->bundle  = !empty($gift->bundle_id) ? $gift->bundle : null;

            $sale->gift_recipient = !empty($gift->receipt)
                ? $gift->receipt->full_name
                : $gift->name;

            $sale->gift_sender = $sale->buyer->full_name;
            $sale->gift_date   = $gift->date;

            $giftPurchasedCount++;

            if (!empty($sale->webinar)) {
                $giftDurations += $sale->webinar->duration;

                if ($sale->webinar->start_date > $time) {
                    $giftUpcoming++;
                }
            }

            if (!empty($sale->bundle)) {
                $bundleWebinars = $sale->bundle->bundleWebinars;

                foreach ($bundleWebinars as $bundleWebinar) {
                    $giftDurations += $bundleWebinar->webinar->duration;
                }
            }
            }
            $sale->progress = $sale->webinar->progress();
            $sale->progress_percent = $sale->webinar->getProgress();
            }

            $purchasedCount = deepClone($query)
                ->where(function ($query) {
                    $query->whereHas('webinar');
                    $query->orWhereHas('bundle');
                })
                ->count();

            $webinarsHours = deepClone($query)->join('webinars', 'webinars.id', 'sales.webinar_id')
                ->select(DB::raw('sum(webinars.duration) as duration'))
                ->sum('duration');
            $bundlesHours = deepClone($query)->join('bundle_webinars', 'bundle_webinars.bundle_id', 'sales.bundle_id')
                ->join('webinars', 'webinars.id', 'bundle_webinars.webinar_id')
                ->select(DB::raw('sum(webinars.duration) as duration'))
                ->sum('duration');

            $hours1 = $webinarsHours + $bundlesHours + $giftDurations;

            $upComing = deepClone($query)->join('webinars', 'webinars.id', 'sales.webinar_id')
                ->where('webinars.start_date', '>', $time)
                ->count();

            $query = InstallmentOrder::query()
                ->where('user_id', $user->id)
                ->where('status', '!=', 'paying');

            $openInstallmentsCount = deepClone($query)->where('status', 'open')->count();
            $pendingVerificationCount = deepClone($query)->where('status', 'pending_verification')->count();
            $finishedInstallmentsCount = $this->getFinishedInstallments($user);

            $orders = $query->with([
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
                ->paginate(10);
            $webcont=0;
            $ordescout=count($orders);
            foreach ($orders as $order) {
                $webinarIdsd=$order->webinar_id;
                $webinarsHours1 = Webinar::select('duration')
                    ->where('id', '=', $webinarIdsd)
                    ->first();
                  $webcont = $webcont+ $webinarsHours1->duration;
                $getRemainedInstallments = $this->getRemainedInstallments($order);

                $order->remained_installments_count = $getRemainedInstallments['total'];
                $order->remained_installments_amount = $getRemainedInstallments['amount'];

                $order->upcoming_installment = $this->getUpcomingInstallment($order);

                $hasOverdue = $order->checkOrderHasOverdue();
                $order->has_overdue = $hasOverdue;
                $order->overdue_count = 0;
                $order->overdue_amount = 0;

                if ($hasOverdue) {
                    $getOrderOverdueCountAndAmount = $order->getOrderOverdueCountAndAmount();
                    $order->overdue_count = $getOrderOverdueCountAndAmount['count'];
                    $order->overdue_amount = $getOrderOverdueCountAndAmount['amount'];
                }

            }

            $overdueInstallmentsCount = $this->getOverdueInstallments($user);

              $hours = $hours1 + $webcont;

                $data['webinars'] =  $sales;
                $data['upComing'] = $upComing + $giftUpcoming;
                $data['openInstallmentsCount'] = $openInstallmentsCount;
                $data['pendingVerificationCount'] = $pendingVerificationCount;
                $data['finishedInstallmentsCount'] = $finishedInstallmentsCount;
                $data['overdueInstallmentsCount'] = $overdueInstallmentsCount;
                $data['hours'] = $hours;
                $data['purchasedCount'] = $purchasedCount + $giftPurchasedCount +$ordescout;

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                [$data]);
        } catch (\Exception $e) {
            \Log::error('PurchaseCourseSummary error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function SubscriptionData()
    {
        try {
           $subscriptions = Subscription::select('id', 'slug', 'price', 'thumbnail', 'image_cover','status')
            ->orderBy('created_at', 'desc')
            ->get();
            
            // API Response
            return response()->json([
                'success' => true,
                'data' => $subscriptions,
                'message' => 'Subscription details retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('API index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching subscription details',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function SubscriptionDataSummary()
    {
        try {
           $slug ="asttrolok-pathshala";
            $subscriptionController = new SubscriptionController();
            $data = $subscriptionController->subscription($slug, true);

           

            $subscription = $data['subscription'];
            $user = apiAuth();
            
            $subscription_prices = $subscription->price;
            $cchapt = count($data['chapterItems']);

            $Access = SubscriptionAccess::where('user_id', $user->id)
                ->first();

            $access_content_count = 0;

            if ($Access) {
                if ($Access->access_till_date > time()) {
                    if ($Access->access_content_count > 0) {
                        $access_content_count = $Access->access_content_count;
                    }
                    if ($subscription->free_video_count) {
                        $access_content_count = $access_content_count + $subscription->free_video_count;
                    }
                    $data["duedate"] = $Access->access_till_date;
                } else {
                    $access_content_count = 0;
                }
            }

            if ($user->id == 1) {
                $access_content_count = $cchapt;
            }

            $data['limit'] = $access_content_count;
            $data['install_url'] = '/subscriptions/direct-payment/' . $subscription->slug;

            
            // API Response
            return response()->json([
                'success' => true,
                'data' => [
                    'subscription' => [
                        'id' => $subscription->id,
                        'slug' => $subscription->slug,
                        'price' => $subscription_prices,
                        'free_video_count' => $subscription->free_video_count,
                        'thumbnail' => $subscription->thumbnail,
                        'image_cover' => $subscription->image_cover,
                        'access_days' => $subscription->access_days,
                        'message_for_reviewer' => $subscription->message_for_reviewer,
                        'status' => $subscription->status,
                        'created_at' => $subscription->created_at,
                        'locale' => $subscription->locale,
                        
                    ],
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->full_name ?? $user->name,
                    ],
                    'access' => [
                        'limit' => $access_content_count,
                        'due_date' => $data['duedate'] ?? null,
                        'has_bought' => $data['hasBought'],
                    ],
                    'chapters' => $data['chapterItems'] ?? [],
                    'chapter_count' => $cchapt,
                    
                ],
                'message' => 'Subscription details retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('API index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching subscription details',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function getRemainedInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $itemPrice = $order->getItemPrice();

        foreach ($order->installment->steps as $step) {
            $payment = InstallmentOrderPayment::query()
                ->where('installment_order_id', $order->id)
                ->where('step_id', $step->id)
                ->where('status', 'paid')
                ->whereHas('sale', function ($query) {
                    $query->whereNull('refund_at');
                })
                ->first();

            if (empty($payment)) {
                $total += 1;
                $amount += $step->getPrice($itemPrice);
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
    }

    private function getOverdueOrderInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $time = time();
        $itemPrice = $order->getItemPrice();

        foreach ($order->installment->steps as $step) {
            $dueAt = ($step->deadline * 86400) + $order->created_at;

            if ($dueAt < $time) {
                $payment = InstallmentOrderPayment::query()
                    ->where('installment_order_id', $order->id)
                    ->where('step_id', $step->id)
                    ->where('status', 'paid')
                    ->first();

                if (empty($payment)) {
                    $total += 1;
                    $amount += $step->getPrice($itemPrice);
                }
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
    }

    private function getUpcomingInstallment($order)
    {
        $result = null;
        $deadline = 0;

        foreach ($order->installment->steps as $step) {
            $payment = InstallmentOrderPayment::query()
                ->where('installment_order_id', $order->id)
                ->where('step_id', $step->id)
                ->where('status', 'paid')
                ->first();

            if (empty($payment) and ($deadline == 0 or $deadline > $step->deadline)) {
                $deadline = $step->deadline;
                $result = $step;
            }
        }

        return $result;
    }

    private function getOverdueInstallments($user)
    {
        $orders = InstallmentOrder::query()
            ->where('user_id', $user->id)
            ->where('installment_orders.status', 'open')
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            if ($order->checkOrderHasOverdue()) {
                $count += 1;
            }
        }

        return $count;
    }

    private function getFinishedInstallments($user)
    {
        $orders = InstallmentOrder::query()
            ->where('user_id', $user->id)
            ->where('installment_orders.status', 'open')
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            $steps = $order->installment->steps;
            $paidAllSteps = true;

            foreach ($steps as $step) {
                $payment = InstallmentOrderPayment::query()
                    ->where('installment_order_id', $order->id)
                    ->where('step_id', $step->id)
                    ->where('status', 'paid')
                    ->whereHas('sale', function ($query) {
                        $query->whereNull('refund_at');
                    })
                    ->first();

                if (empty($payment)) {
                    $paidAllSteps = false;
                }
            }

            if ($paidAllSteps) {
                $count += 1;
            }
        }

        return $count;
    }

    public function purchases()
    {
        try {
            $user = apiAuth();
            $webinarIds = $user->getPurchasedCoursesIds();

            $webinars = Sale::where('sales.buyer_id', $user->id)
                ->whereNull('sales.refund_at')
                ->where('access_to_purchased_item', true)
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->whereNotNull('sales.webinar_id')
                            ->where('sales.type', 'webinar')
                            ->whereHas('webinar', function ($query) {
                                $query->where('status', 'active');
                            });
                    });
                    $query->orWhere(function ($query) {
                        $query->whereNotNull('sales.bundle_id')
                            ->where('sales.type', 'bundle')
                            ->whereHas('bundle', function ($query) {
                                $query->where('status', 'active');
                            });
                    });
                })->with([
                    'webinar' => function ($query) {
                        $query->with([
                            'files',
                            'reviews' => function ($query) {
                                $query->where('status', 'active');
                            },
                            'category',
                            'teacher' => function ($query) {
                                $query->select('id', 'full_name');
                            },

                        ]);
                        $query->withCount([
                            'sales' => function ($query) {
                                $query->whereNull('refund_at');
                            }
                        ]);
                    },
                    'bundle' => function ($query) {
                        $query->with([
                            'reviews' => function ($query) {
                                $query->where('status', 'active');
                            },
                            'category',
                            'teacher' => function ($query) {
                                $query->select('id', 'full_name');
                            },
                        ]);
                    }
                ])

                ->orderBy('created_at', 'desc')
                ->get()->map(function ($sale) {
                    return ($sale->webinar) ? new WebinarResource($sale->webinar) : new BundleResource($sale->bundle);
                });

            return $webinars;
        } catch (\Exception $e) {
            \Log::error('purchases error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function invitations(Request $request)
    {
        try {
            $user = apiAuth();

            $invitedWebinarIds = WebinarPartnerTeacher::where('teacher_id', $user->id)->pluck('webinar_id')->toArray();
            $webinars = Webinar::where('status', 'active')
                ->whereIn('id', $invitedWebinarIds)
                ->handleFilters()
                ->orderBy('updated_at', 'desc')->get()->map(function ($webinar) {
                    return $webinar->brief;
                });

            return $webinars;
        } catch (\Exception $e) {
            \Log::error('invitations error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function organizations()
    {
        try {
            $user = apiAuth();

            $webinars = Webinar::where('creator_id', $user->organ_id)
                ->where('status', 'active')->handleFilters()
                ->orderBy('created_at', 'desc')
                ->orderBy('updated_at', 'desc')->get()->map(function ($webinar) {
                    return $webinar->brief;
                });

            return $webinars;
        } catch (\Exception $e) {
            \Log::error('organizations error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function indexOrganizations()
    {
        try {
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                [
                    'webinars' => $this->organizations()
                ]);
        } catch (\Exception $e) {
            \Log::error('indexOrganizations error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
