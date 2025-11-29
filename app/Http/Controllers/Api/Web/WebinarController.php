<?php

namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\FileResource;
use App\Http\Resources\SessionResource;
use App\Http\Resources\TextLessonResource;
use App\Http\Resources\WebinarChapterResource;
use App\Models\Favorite;
use App\Models\CourseLearning;
use App\Models\Ticket;
use App\Models\Certificate;
use App\Models\InstallmentOrderPayment;
use App\Models\QuizzesResult;
use App\Models\Api\Webinar;
use App\Models\WebinarChapter;
use App\Models\WebinarFilterOption;
use App\Models\WebinarAccessControl;
use App\Models\InstallmentOrder;
use App\Models\WebinarReport;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\AdvertisingBanner;
use App\Mixins\Installment\InstallmentPlans;
use App\Http\Controllers\Web\traits\LearningPageAssignmentTrait;
use App\Http\Controllers\Web\traits\LearningPageForumTrait;
use App\Http\Controllers\Web\traits\LearningPageItemInfoTrait;
use App\Http\Controllers\Web\traits\LearningPageMixinsTrait;
use App\Http\Controllers\Web\traits\LearningPageNoticeboardsTrait;

class WebinarController extends Controller
{
use LearningPageMixinsTrait, LearningPageAssignmentTrait, LearningPageItemInfoTrait,
        LearningPageNoticeboardsTrait, LearningPageForumTrait;

public function index()
{
        try {
            $dynamic_rate_course = [
            '2025' => 4.1,
            '2026' => 4.5,
            '2027' => 4.75,
            '2028' => 4.8,
            '2029' => 4.6,
            '2030' => 4.5,
            '2031' => 4.9,
            '2033' => 4.5,
            '2034' => 4.75,
            '2035' => 4.8,
            '2036' => 4.1,
            '2038' => 4.5,
            '2045' => 4.4,
            '2046' => 4.5,
            '2047' => 4.75,
            '2048' => 4.8,
            '2049' => 4.4,
            '2050' => 4.5,
            '2052' => 4.1,
            '2053' => 4.5,
            '2055' => 4.75,
            '2056' => 4.8,
            '2057' => 4.3,
            '2058' => 4.5,
            '2062' => 4.2,
            '2063' => 4.5,
            '2064' => 4.75,
            '2065' => 4.8,
            '2066' => 4.9,
            '2067' => 4.5,
            '2068' => 4.1,
            '2069' => 4.7,
            '2070' => 4.9,
            ];

            $webinars = Webinar::with(['teacher'])
            ->select('*')
            ->where('status', 'active')
            ->where('private', false)
            ->whereHas('teacher', function ($query) {
                $query->where('status', 'active')
                    ->where(function ($query) {
                        $query->where('ban', false)
                            ->orWhere(function ($query) {
                                $query->whereNotNull('ban_end_at')
                                    ->where('ban_end_at', '<', now());
                            });
                    });
            })
            ->handleFilters()
            ->get()
            ->map(function ($webinar) use ($dynamic_rate_course) {
                $brief = $webinar->brief;

                if (isset($dynamic_rate_course[$webinar->id])) {
                    $brief['rate'] = $dynamic_rate_course[$webinar->id];
                } else {
                    $brief['rate'] = 0;
                }

                return $brief;
            });

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $webinars);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

  public function showdddd($id)
{
        try {
            $webinarsQuery = Webinar::where('status', 'active')
            ->where('private', false)
            ->where('id', $id);

            $webinarWithExtras = $webinarsQuery->with([
            'webinarExtraDescription' => function ($query) {
                $query->select('webinar_id', 'type', 'id')
                      ->orderBy('order', 'asc');
            }
            ])->first();

            if (!$webinarWithExtras) {
            return apiResponse2(0, 'retrieved', 'Course not found', []);
            }

            $webinar = $webinarsQuery
            ->orderBy('webinars.created_at', 'desc')
            ->orderBy('webinars.updated_at', 'desc')
            ->first();

            if (!$webinar) {
            return apiResponse2(0, 'retrieved', 'Course not found', []);
            }

            $brief = $webinar->brief;
            $brief['webinarExtraDescription'] = $webinarWithExtras->webinarExtraDescription ?? null;
            $brief['installment'] = $webinar->checkInstallment($webinar);

            return apiResponse2(1, 'retrieved', 'Course retrieved successfully', $brief);
        } catch (\Exception $e) {
            \Log::error('showdddd error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

public function show($id)
{
        try {
            $webinarsQuery = Webinar::where('status', 'active')
            ->where('private', false)
            ->where('id', $id);

            $webinarExtraDescription = $webinarsQuery->with([
            'webinarExtraDescription' => function ($query) {
                $query->select('webinar_id', 'type', 'id');
                $query->orderBy('order', 'asc');
            }
            ])->first();

            if ($webinarsQuery->doesntExist()) {
             return apiResponse2(0, 'retrieved', 'Course not found', []);
            }

            $webinar = $webinarsQuery
            ->orderBy('webinars.created_at', 'desc')
            ->orderBy('webinars.updated_at', 'desc')
            ->first();
            if (!$webinar) {
             return apiResponse2(0, 'retrieved', 'Course not found', []);
            }

            $brief = $webinar->brief;

            $brief['webinarExtraDescription'] = $webinarExtraDescription ?? null;
            $brief['installment'] = $webinar->checkInstallment($webinar);

            return apiResponse2(1, 'retrieved', 'Course retrieved successfully', $brief);
        } catch (\Exception $e) {
            \Log::error('show error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

 public function show_content($id)
    {
        try {
            $webinarsQuery = Webinar::where('status', 'active')
                ->where('private', false)->where('id', $id);

            abort_unless($webinarsQuery->count(), 404);

            $webinars = $webinarsQuery->orderBy('webinars.created_at', 'desc')
                ->orderBy('webinars.updated_at', 'desc')
                ->get()->map(function ($webinar) {
                    return $webinar->details;
                })->first();
            return $webinars;
        } catch (\Exception $e) {
            \Log::error('show_content error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function contentff($id)
    {
        try {
            $user = apiAuth();
            $webinar = Webinar::where('id', $id)
                ->with([
                    'chapters' => function ($query) use ($user) {
                        $query->where('status', WebinarChapter::$chapterActive);
                        $query->orderBy('order', 'asc');

                        $query->with([
                            'chapterItems' => function ($query) {
                                $query->orderBy('order', 'asc');
                            }
                        ]);
                    },
                    'quizzes' => function ($query) {
                        $query->where('status', 'active')
                            ->with(['quizResults', 'quizQuestions']);
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
                ])
                ->first();

            $chapters = collect(WebinarChapterResource::collection($webinar->chapters))->map(function ($item) {
                return array_merge(['type' => 'chapter'], $item);
            });
            $files = collect(FileResource::collection($webinar->files->whereNull('chapter_id')))->map(function ($item) {
                return array_merge(['type' => 'file'], $item);
            });
            $sessions = collect(SessionResource::collection($webinar->sessions->whereNull('chapter_id')))->map(function ($item) {
                return array_merge(['type' => 'session'], $item);
            });
            $textLessons = collect(TextLessonResource::collection($webinar->textLessons->whereNull('chapter_id')))->map(function ($item) {
                return array_merge(['type' => 'text_lesson'], $item);
            });

            $content = $chapters->merge($files)->merge($sessions)->merge($textLessons);
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $content);
        } catch (\Exception $e) {
            \Log::error('contentff error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function contentss($id)
    {
        try {
            $user = apiAuth();
            $webinar = Webinar::where('id', $id)
                ->with([
                    'chapters' => function ($query) use ($user) {
                        $query->where('status', WebinarChapter::$chapterActive);
                        $query->orderBy('order', 'asc');

                        $query->with([
                            'chapterItems' => function ($query) {
                                $query->orderBy('order', 'asc');
                            }
                        ]);
                    },
                    'quizzes' => function ($query) {
                        $query->where('status', 'active')
                            ->with(['quizResults', 'quizQuestions']);
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
                ])
                ->first();

            $chapters = collect(WebinarChapterResource::collection($webinar->chapters))->map(function ($item) {
                return array_merge(['type' => 'chapter'], $item);
            });
            $files = collect(FileResource::collection($webinar->files->whereNull('chapter_id')))->map(function ($item) {
                return array_merge(['type' => 'file'], $item);
            });
            $sessions = collect(SessionResource::collection($webinar->sessions->whereNull('chapter_id')))->map(function ($item) {
                return array_merge(['type' => 'session'], $item);
            });
            $textLessons = collect(TextLessonResource::collection($webinar->textLessons->whereNull('chapter_id')))->map(function ($item) {
                return array_merge(['type' => 'text_lesson'], $item);
            });

            $content = $chapters->merge($files)->merge($sessions)->merge($textLessons);
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $webinar);
        } catch (\Exception $e) {
            \Log::error('contentss error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

public function content(Request $request, $id)
{
        try {
            $requestData = $request->all();
            $user = apiAuth();

            $webinarsQuery = Webinar::where('status', 'active')
                ->where('private', false)->where('id', $id);

            abort_unless($webinarsQuery->count(), 404);

            $coursedeils = $webinarsQuery->orderBy('webinars.created_at', 'desc')
                ->orderBy('webinars.updated_at', 'desc')
                ->get()->map(function ($webinar) {
                    return $webinar->details;
                })->first();

                 $course=  $webinarsQuery->first();
            if (empty($course)) {
            return apiResponse2(1, 'retrieved', 'data not found', []);
            }

            if ($course->private) {
            $contentLimitation = $this->checkContentLimitation($user, true);
            if ($contentLimitation !== 'ok') return $contentLimitation;
            }

            $hasBought = $course->checkUserHasBought($user, true, true);
            $isPrivate = $course->private;

            if ($user && ($user->id === $course->creator_id || $user->organ_id === $course->creator_id || $user->isAdmin())) {
            $isPrivate = false;
            }

            if ($isPrivate && $hasBought) $isPrivate = false;
            if ($isPrivate) return back();

            $isFavorite = $user ? Favorite::where('webinar_id', $course->id)->where('user_id', $user->id)->isNotEmpty() : false;

            $advertisingBanners = AdvertisingBanner::where('published', true)
            ->whereIn('position', ['course', 'course_sidebar'])->get();

            $data = collect([
            'course'  =>$coursedeils,
            'isFavorite' => $isFavorite,
            'hasBought' => $hasBought,

            'user' => collect($user->toArray()),

            'activeSpecialOffer' => optional($course->activeSpecialOffer())->toArray(),
            'sessionsWithoutChapter' => collect($course->sessions)->whereNull('chapter_id')->values(),
            'filesWithoutChapter' => collect($course->files)->whereNull('chapter_id')->values(),
            'textLessonsWithoutChapter' => collect($course->textLessons)->whereNull('chapter_id')->values(),
            'quizzes' => collect($course->quizzes)->whereNull('chapter_id')->values(),
            ]);

            $installmentLimit = $this->installmentContentLimitation_limit($user, $course->id, 'webinar_id');
            $directAccess = WebinarAccessControl::where('webinar_id', $course->id)->where('user_id', $user->id)->first();

            $data['directAccess'] = 0;
            if ($directAccess && strtotime($directAccess->expire) > time() && $directAccess->percentage >= $installmentLimit) {
            $installmentLimit = $directAccess->percentage;
            $data['directAccess'] = 1;
            }

            $totalChapters = count($course->chapters);
            $data['limit'] = ($installmentLimit == 100) ? 100 : round(($installmentLimit / 100) * $totalChapters);

            if ((!$data || (!$data['hasBought'] && empty($course->getInstallmentOrder()))) && $data['directAccess'] === 0) {
            $data['limit'] =0;
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);

            return response()->json(['error' => 'Unauthorized', 'message' => trans('update.access_denied')], 403);
            }

            if (!empty($requestData['type']) && $requestData['type'] === 'assignment' && !empty($requestData['item'])) {
            $assignmentData = $this->getAssignmentData($course, $requestData);
            $data = $data->merge($assignmentData);
            }

            if ($course->certificate) {
            $data['courseCertificate'] = Certificate::where('type', 'course')
                ->where('student_id', $user->id)
                ->where('webinar_id', $course->id)
                ->first();
            }

            $order = InstallmentOrder::where('webinar_id', $course->id)->where('user_id', $user->id)->first();

            if ($order && !in_array($order->status, ['refunded', 'canceled'])) {
            $remained = $this->getRemainedInstallments($order);
            $overdue = $this->getOverdueOrderInstallments($order);

            $data['totalParts'] = $order->installment->steps->count();
            $data['remainedParts'] = $remained['total'];
            $data['remainedAmount'] = $remained['amount'];
            $data['overdueAmount'] = $overdue['amount'];
            $data['order'] = collect($order->toArray());
            }

            $data['duedate'] = time();

            if (isset($order->installment)) {
            $paid = 0;
            foreach ($order->installment->steps as $step) {
            $stepPayment = $order->payments->where('step_id', $step->id)->where('status', 'paid')->first();
            $dueAt = ($step->deadline * 86400) + $order->created_at;
            if ($dueAt < time() && empty($stepPayment)) break;
            $paid++;
            }
            if ($paid !== $order->installment->steps->count()) {
            $data['duedate'] = $dueAt;
            }
            }

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
        } catch (\Exception $e) {
            \Log::error('content error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

public function course($slug, $justReturnData = false)
{
        try {
            $user = apiAuth();

            $course = Webinar::where('slug', $slug)
            ->with([
                'quizzes' => function ($query) {
                    $query->where('status', 'active')->with(['quizResults', 'quizQuestions']);
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
                    $query->where('status', WebinarChapter::$chapterActive)
                        ->orderBy('order', 'asc')
                        ->with(['chapterItems' => function ($query) {
                            $query->orderBy('order', 'asc');
                        }]);
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
                'teacher',
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
                    $query->where('status', 'active')
                        ->whereNull('reply_id')
                        ->with([
                            'user' => function ($query) {
                                $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                            },
                            'replies' => function ($query) {
                                $query->where('status', 'active')
                                    ->with([
                                        'user' => function ($query) {
                                            $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                                        }
                                    ]);
                            }
                        ])
                        ->orderBy('created_at', 'desc');
                },
            ])
            ->withCount([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                },
                'noticeboards'
            ])
            ->where('status', 'active')
            ->get()->toarray();

            if (empty($course)) {
            return apiResponse2(0, 'retrieved', 'data not found', []);
            }

            if ($course->private == 1) {
            if (!$justReturnData) {
                $contentLimitation = $this->checkContentLimitation($user, true);
                if ($contentLimitation != "ok") {
                    return $contentLimitation;
                }
            }
            }

            $hasBought = $course->checkUserHasBought($user, true, true);
            $isPrivate = $course->private;

            if (!empty($user) && ($user->id == $course->creator_id || $user->organ_id == $course->creator_id || $user->isAdmin())) {
            $isPrivate = false;
            }

            if ($isPrivate && $hasBought) {
            $isPrivate = false;
            }

            if ($isPrivate) {
            return $justReturnData ? false : back();
            }

            $isFavorite = false;
            if (!empty($user)) {
            $isFavorite = Favorite::where('webinar_id', $course->id)
                ->where('user_id', $user->id)
                ->first();
            }

            $webinarContentCount = 0;
            $webinarContentCount += optional($course->sessions)->count();
            $webinarContentCount += optional($course->files)->count();
            $webinarContentCount += optional($course->textLessons)->count();
            $webinarContentCount += optional($course->quizzes)->count();
            $webinarContentCount += optional($course->assignments)->count();

            $advertisingBanners = AdvertisingBanner::where('published', true)
            ->whereIn('position', ['course', 'course_sidebar'])
            ->get();

            $sessionsWithoutChapter = $course->sessions->whereNull('chapter_id');
            $filesWithoutChapter = $course->files->whereNull('chapter_id');
            $textLessonsWithoutChapter = $course->textLessons->whereNull('chapter_id');
            $quizzes = $course->quizzes->whereNull('chapter_id');

            if ($user) {
            $quizzes = $this->checkQuizzesResults($user, $quizzes);

            if (!empty($course->chapters) && count($course->chapters)) {
                foreach ($course->chapters as $chapter) {
                    if (!empty($chapter->chapterItems) && count($chapter->chapterItems)) {
                        foreach ($chapter->chapterItems as $chapterItem) {
                            if (!empty($chapterItem->quiz)) {
                                $chapterItem->quiz = $this->checkQuizResults($user, $chapterItem->quiz);
                            }
                        }
                    }
                }
            }

            if (!empty($course->quizzes) && count($course->quizzes)) {
                $course->quizzes = $this->checkQuizzesResults($user, $course->quizzes);
            }
            }

            $pageRobot = getPageRobot('course_show');
            $canSale = ($course->canSale() && !$hasBought);

            $showInstallments = true;
            $overdueInstallmentOrders = $this->checkUserHasOverdueInstallment($user);

            if ($overdueInstallmentOrders->isNotEmpty() && getInstallmentsSettings('disable_instalments_when_the_user_have_an_overdue_installment')) {
            $showInstallments = false;
            }

            if ($canSale && !empty($course->price) && $course->price > 0 && $showInstallments && getInstallmentsSettings('status') && (empty($user) || $user->enable_installments)) {
            $installmentPlans = new InstallmentPlans($user);
            $installments = $installmentPlans->getPlans('courses', $course->id, $course->type, $course->category_id, $course->teacher_id);
            }

            if ($canSale && !empty($course->price) && getFeaturesSettings('cashback_active') && (empty($user) || !$user->disable_cashback)) {
            $cashbackRulesMixin = new CashbackRules($user);
            $cashbackRules = $cashbackRulesMixin->getRules('courses', $course->id, $course->type, $course->category_id, $course->teacher_id);
            }

            $course->hasBought = $hasBought;

            if (isset($user->id)) {
            if ($course->id == 2069) {
                $webhookurl = 'https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTZiMDYzMzA0M2Q1MjY5NTUzNzUxMzUi_pc';
                $webhookdata = [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                ];
                $webhookcurl = curl_init($webhookurl);
                curl_setopt($webhookcurl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($webhookcurl, CURLOPT_POST, true);
                curl_setopt($webhookcurl, CURLOPT_POSTFIELDS, json_encode($webhookdata));
                $webhookresponse = curl_exec($webhookcurl);
                curl_close($webhookcurl);
            }
            }
            print_r($course);die;
            $data = [
            'pageTitle' => $course->title,
            'pageDescription' => $course->seo_description,
            'pageRobot' => $pageRobot,
            'course' => $course,
            'isFavorite' => $isFavorite,
            'hasBought' => $hasBought,
            'user' => $user,
            'webinarContentCount' => $webinarContentCount,
            'advertisingBanners' => $advertisingBanners->where('position', 'course'),
            'advertisingBannersSidebar' => $advertisingBanners->where('position', 'course_sidebar'),
            'activeSpecialOffer' => $course->activeSpecialOffer(),
            'sessionsWithoutChapter' => $sessionsWithoutChapter,
            'filesWithoutChapter' => $filesWithoutChapter,
            'textLessonsWithoutChapter' => $textLessonsWithoutChapter,
            'quizzes' => $quizzes,
            'installments' => $installments ?? null,
            'cashbackRules' => $cashbackRules ?? null,
            'astromani_23' => $course_Astromani_2023,
            'course_Professional' => $course_Professional,
            ];

            if ($justReturnData) {
            return $data;
            }

            return response()->json([
            'status' => true,
            'message' => 'Course data fetched successfully.',
            'data' => $data,
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
        } catch (\Exception $e) {
            \Log::error('course error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

   private function checkQuizzesResults($user, $quizzes)
    {
        $canDownloadCertificate = false;

        foreach ($quizzes as $quiz) {
            $quiz = $this->checkQuizResults($user, $quiz);
        }

        return $quizzes;
    }

    private function checkQuizResults($user, $quiz)
    {
        $canDownloadCertificate = false;

        $canTryAgainQuiz = false;
        $userQuizDone = QuizzesResult::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if (count($userQuizDone)) {
            $quiz->user_grade = $userQuizDone->first()->user_grade;
            $quiz->result_count = $userQuizDone->count();
            $quiz->result = $userQuizDone->first();

            $status_pass = false;
            foreach ($userQuizDone as $result) {
                if ($result->status == QuizzesResult::$passed) {
                    $status_pass = true;
                }
            }

            $quiz->result_status = $status_pass ? QuizzesResult::$passed : $userQuizDone->first()->status;

            if ($quiz->certificate and $quiz->result_status == QuizzesResult::$passed) {
                $canDownloadCertificate = true;
            }
        }

        if (!isset($quiz->attempt) or (count($userQuizDone) < $quiz->attempt and $quiz->result_status !== QuizzesResult::$passed)) {
            $canTryAgainQuiz = true;
        }

        $quiz->can_try = $canTryAgainQuiz;
        $quiz->can_download_certificate = $canDownloadCertificate;

        return $quiz;
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

    public function checkUserHasOverdueInstallment($user = null)
    {
        try {
            if (empty($user)) {
                $user = apiAuth();
            }

            $orders = collect();

            if (!empty($user)) {
                $time = time();
                $overdueIntervalDays = getInstallmentsSettings('overdue_interval_days') ?? 0;
                $overdueIntervalDays = $overdueIntervalDays * 86400;
                $time = $time - $overdueIntervalDays;

                $orders = InstallmentOrder::query()
                    ->join('installments', 'installment_orders.installment_id', 'installments.id')
                    ->join('installment_steps', 'installments.id', 'installment_steps.installment_id')
                    ->leftJoin('installment_order_payments', 'installment_order_payments.step_id', 'installment_steps.id')
                    ->select('installment_orders.*', 'installment_steps.amount', 'installment_steps.amount_type',
                        DB::raw('((installment_steps.deadline * 86400) + installment_orders.created_at) as overdue_date')
                    )
                    ->where('user_id', $user->id)
                    ->whereRaw("((installment_steps.deadline * 86400) + installment_orders.created_at) < {$time}")
                    ->where(function ($query) {
                        $query->whereRaw("installment_order_payments.id < 1");
                        $query->orWhereRaw("installment_order_payments.id is null");
                    })
                    ->where('installment_orders.status', 'open')
                    ->get();

            }

            return $orders;
        } catch (\Exception $e) {
            \Log::error('checkUserHasOverdueInstallment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

   public function installmentContentLimitation_limit($user, $itemId = null, $itemName = null)
   {
        try {
            $percent = 0;

            if (empty($user)) {
            $user = apiAuth();
            }

            $installmentsSettings = getInstallmentsSettings();

            if (!empty($user) && !empty($installmentsSettings['status'])) {
            if (!empty($itemId) && !empty($itemName)) {
                $installments = DB::table('installment_orders')
                    ->where('user_id', $user->id)
                    ->where('webinar_id', $itemId)
                    ->where('status', 'open')
                    ->get();

                if ($installments->count() >= 1) {
                    foreach ($installments as $installment) {
                        $price = $installment->item_price;

                        $payments = DB::table('installment_order_payments')
                            ->where('installment_order_id', $installment->id)
                            ->where('status', 'paid')
                            ->get();

                        foreach ($payments as $payment) {
                            $percent += $payment->amount;
                        }

                        $final_percent = ($percent / $price) * 100;
                    }

                    return round($final_percent);
                }

                return 100;
            }
            }

            return 100;
        } catch (\Exception $e) {
            \Log::error('installmentContentLimitation_limit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function installmentContentLimitationCheck($itemId = null)
    {
        try {
            $user = apiAuth();
            $installments = DB::table('installment_specification_items')
                ->join('installment_translations', 'installment_specification_items.installment_id', '=', 'installment_translations.installment_id')
                ->join('installments', 'installments.id', '=', 'installment_translations.installment_id')
                ->select('installment_translations.main_title', 'installment_translations.installment_id')
                ->where('installment_specification_items.webinar_id', $itemId)
                ->where('installments.enable', 1)
                ->get();

            if ($installments->isNotEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Installment available'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No installment available'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('installmentContentLimitationCheck error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function installmentContentLimitation($user, $itemId = null, $itemName = null)
    {
        try {
            if (empty($user)) {
                $user = apiAuth();
            }

            $installmentsSettings = getInstallmentsSettings();

            if (!empty($user) && !empty($installmentsSettings['status'])) {
                $overdueInstallments = $this->checkUserHasOverdueInstallment($user);
                $denied = false;

                if ($overdueInstallments->isNotEmpty() && $installmentsSettings['disable_all_courses_access_when_user_have_an_overdue_installment']) {
                $denied = true;
            }

            if (!empty($itemId) && !empty($itemName)) {
                $itemOrders = $overdueInstallments->where($itemName, $itemId);

                if ($itemOrders->isNotEmpty() && $installmentsSettings['disable_course_access_when_user_have_an_overdue_installment']) {
                    $denied = true;
                }

                $subscribeOrders = $overdueInstallments->whereNotNull('subscribe_id');

                foreach ($subscribeOrders as $subscribeOrder) {
                    $subscribed = SubscribeUse::whereNotNull('sale_id')
                        ->where('user_id', $user->id)
                        ->where($itemName, $itemId)
                        ->where('installment_order_id', $subscribeOrder->id)
                        ->first();

                    if (!empty($subscribed)) {
                        $denied = true;
                    }
                }
            }

            if ($denied) {
                $data = [
                    'pageTitle' => trans('update.access_denied'),
                    'pageRobot' => getPageRobotNoIndex(),
                ];

                return $data;
            }
            }

            return "ok";
        } catch (\Exception $e) {
            \Log::error('installmentContentLimitation error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
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

    public function learningStatus(Request $request, $webinar_id)
    {
        try {
            switch ($request->input('item')) {
                case 'file_id':
                    $table = 'files';
                    break;

                case 'session_id':
                    $table = 'sessions';
                    break;

                case 'text_lesson_id':
                    $table = 'text_lessons';
                    break;
                default :
                    $table = null;

            }

            validateParam($request->all(), [
                'item' => 'required|in:file_id,session_id,text_lesson_id',
                'item_id' => ['required', Rule::exists($table, 'id')],
                'status' => 'required|boolean',
            ]);

            $user = apiAuth();
            $data = $request->all();

            $item = $data['item'];
            $item_id = $data['item_id'];
            $status = $data['status'];

            $course = Webinar::where('id', $webinar_id)->first();

            if (empty($course)) {
                abort(404);
            }

            if (!$course->checkUserHasBought($user)) {

                return apiResponse2(0, 'not_purchased', trans('api.webinar.not_purchased'));
            }

            $courseLearning = CourseLearning::where('user_id', $user->id)
                ->where($item, $item_id)->delete();

            if ($status) {

                CourseLearning::create([
                    'user_id' => $user->id,
                    $item => $item_id,
                    'created_at' => time()
                ]);

                return apiResponse2(1, 'read', trans('api.learning_status.read'));

            }
            return apiResponse2(1, 'unread', trans('api.learning_status.unread'));
        } catch (\Exception $e) {
            \Log::error('learningStatus error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
  public function installmentContentLimitation_limit1($user, $itemId = null, $itemName = null)
    {
        try {
            if (empty($user)) {
                $user = apiAuth();
            }
            $installmentsSettings = getInstallmentsSettings();

            if (!empty($user) and !empty($installmentsSettings['status'])) {
                $denied = false;
                  if (!empty($itemId) and !empty($itemName)) {
                      $installments1 =DB::table('installment_orders')
                       ->selectRaw(' * ')
                      ->where('user_id', $user->id )
                 ->where('webinar_id', $itemId)
                 ->where('status', 'open')
                ->get();

                if(count($installments1)>=1){
              foreach ($installments1 as $installments) {
               $installmentsid=   $installments->id;

            }
            return $installmentsid;

              }
                  }
                  }
            return 0;
        } catch (\Exception $e) {
            \Log::error('installmentContentLimitation_limit1 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function report(Request $request, $id)
    {
        try {
            $user = apiAuth();
            validateParam($request->all(), [
                'reason' => 'required|string',
                'message' => 'required|string',
            ]);

            $webinar = Webinar::select('id', 'status')
                ->where('id', $id)
                ->where('status', 'active')
                ->first();
            if (!$webinar) {
                abort(404);
            }

            WebinarReport::create([
                'user_id' => $user->id,
                'webinar_id' => $webinar->id,
                'reason' => $request->post('reason'),
                'message' => $request->post('message'),
                'created_at' => time()
            ]);
            return apiResponse2(1, 'reported', trans('courses.reported'));
        } catch (\Exception $e) {
            \Log::error('report error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public static function brief($webinars, $single = false)
    {
        if ($single) {
            $webinars = collect([$webinars]);
        }

        $user = apiAuth();
        $webinars = $webinars->map(function ($webinar) use ($user) {

            $hasBought = $webinar->checkUserHasBought($user);

            $progress = self::progress($webinar);

            $is_favorite = self::isFavorite($webinar);

            $live_webinar_status = self::liveWebinarStatus($webinar);

            return [
                'auth' => ($user) ? true : false,
                'id' => $webinar->id,
                'status' => $webinar->status,
                'title' => $webinar->title,
                'type' => $webinar->type,
                'live_webinar_status' => $live_webinar_status,
                'auth_has_bought' => ($user) ? $hasBought : null,

                'price' => $webinar->price,
                'price_with_discount' => ($webinar->activeSpecialOffer()) ? (
                number_format($webinar->price - ($webinar->price * $webinar->activeSpecialOffer()->percent / 100), 2)) : false,
                'active_special_offer' => $webinar->activeSpecialOffer(),

                'duration' => $webinar->duration,
                'teacher' => [
                    'full_name' => $webinar->teacher->full_name,
                    'avatar' => $webinar->teacher->getAvatar(),
                    'rate' => $webinar->teacher->rates(),
                ],
                'rate' => $webinar->getRate(),
                'discount' => $webinar->getDiscount(),
                'created_at' => $webinar->created_at,
                'start_date' => $webinar->start_date,
                'progress' => $webinar->getProgress(),
                'category' => $webinar->category->title,

            ];
        });

        if ($single) {
            return $webinars->first();
        }

        return [
            'count' => count($webinars),
            'webinars' => $webinars,
        ];
    }

    public function details($webinars)
    {
        try {
            $user = apiAuth();

            $webinars = $webinars->map(function ($webinar) use ($user) {
                $hasBought = $webinar->checkUserHasBought($user);

                $progress = $this->progress($webinar);

                $is_favorite = $this->isFavorite($webinar);

                $live_webinar_status = $this->liveWebinarStatus($webinar);

                return [
                    'auth' => ($user) ? true : false,
                    'id' => $webinar->id,
                    'title' => $webinar->title,
                    'type' => $webinar->type,
                    'live_webinar_status' => $live_webinar_status,
                    'auth_has_bought' => ($user) ? $hasBought : null,
                    'price' => $webinar->price,
                    'price_with_discount' => ($webinar->activeSpecialOffer()) ? (
                    number_format($webinar->price - ($webinar->price * $webinar->activeSpecialOffer()->percent / 100), 2)) : false,
                    'active_special_offer' => $webinar->activeSpecialOffer(),

                    'duration' => $webinar->duration,
                    'teacher' => [
                        'full_name' => $webinar->teacher->full_name,
                        'avatar' => $webinar->teacher->getAvatar(),
                        'rate' => $webinar->teacher->rates(),
                    ],

                    'sessions_count' => $webinar->sessions->count(),
                    'text_lessons_count' => $webinar->textLessons->count(),
                    'files_count' => $webinar->files->count(),

                    'sessions_without_chapter' => $webinar->sessions->whereNull('chapter_id')->map(function ($session) {
                        return [
                            'id' => $session->id,
                            'title' => $session->title,
                            'description' => $session->description,
                            'date' => dateTimeFormat($session->date, 'j M Y | H:i')
                        ];

                    }),
                    'sessions_with_chapter' => $webinar->chapters->where('type', WebinarChapter::$chapterSession)->map(function ($chapter) {
                        $chapter->sessions->map(function ($session) {
                            return [
                                'id' => $session->id,
                                'title' => $session->title,
                                'description' => $session->description,
                                'date' => dateTimeFormat($session->date, 'j M Y | H:i')
                            ];
                        });

                    }),

                    'rate' => $webinar->getRate(),
                    'rate_type' => [
                        'content_quality' => $webinar->reviews->isNotEmpty() ? round($webinar->reviews->avg('content_quality'), 1) : 0,
                        'instructor_skills' => $webinar->reviews->isNotEmpty() ? round($webinar->reviews->avg('instructor_skills'), 1) : 0,
                        'purchase_worth' => $webinar->reviews->isNotEmpty() ? round($webinar->reviews->avg('purchase_worth'), 1) : 0,
                        'support_quality' => $webinar->reviews->isNotEmpty() ? round($webinar->reviews->avg('support_quality'), 1) : 0,

                    ],
                    'reviews_count' => $webinar->reviews->count(),
                    'reviews' => $webinar->reviews->map(function ($review) {
                        return [
                            'user' => [
                                'full_name' => $review->creator->full_name,
                                'avatar' => $review->creator->getAvatar(),
                            ],
                            'create_at' => $review->created_at,
                            'description' => $review->description,
                            'replies' => $review->comments->map(function ($reply) {
                                return [
                                    'user' => [
                                        'full_name' => $reply->user->full_name,
                                        'avatar' => $reply->user->getAvatar(),
                                    ],
                                    'create_at' => $reply->created_at,
                                    'comment' => $reply->comment,
                                ];

                            })

                        ];
                    }),
                    'comments' => $webinar->comments->map(function ($item) {
                        return [
                            'user' => [
                                'full_name' => $item->user->full_name,
                                'avatar' => $item->user->getAvatar(),
                            ],
                            'create_at' => $item->created_at,
                            'comment' => $item->comment,
                            'replies' => $item->replies->map(function ($reply) {
                                return [
                                    'user' => [
                                        'full_name' => $reply->user->full_name,
                                        'avatar' => $reply->user->getAvatar(),
                                    ],
                                    'create_at' => $reply->created_at,
                                    'comment' => $reply->comment,
                                ];

                            })
                        ];
                    }),
                    'discount' => $webinar->getDiscount(),
                    'created_at' => $webinar->created_at,
                    'start_date' => $webinar->start_date,

                    'progress' => $progress,

                    'category' => $webinar->category->title,
                    'video_demo' => $webinar->video_demo,
                    'image' => $webinar->getImage(),
                    'description' => $webinar->description,
                    'isDownloadable' => $webinar->isDownloadable(),
                    'support' => $webinar->support ? true : false,
                    'certificate' => ($webinar->quizzes->where('certificate', 1)->isNotEmpty()) ? true : false,
                    'quizzes_count' => $webinar->quizzes->where('status', \App\models\Quiz::ACTIVE)->count(),
                    'is_favorite' => $is_favorite,
                    'students_count' => $webinar->sales->count(),
                    'tags' => $webinar->tags,
                    'tickets' => $webinar->tickets->map(function ($ticket) {
                        return [
                            'id' => $ticket->id,
                            'title' => $ticket->title,
                            'sub_title' => $ticket->getSubTitle(),
                            'discount' => $ticket->discount,

                            'is_valid' => $ticket->isValid(),

                        ];
                    }),
                    'prerequisites' => $webinar->prerequisites->map(function ($prerequisite) {
                        return [
                            'required' => $prerequisite->required,
                            'webinar' => self::brief($prerequisite->prerequisiteWebinar, true)
                        ];
                    }),
                    'faqs' => $webinar->faqs

                ];
            });
            return [
                'count' => count($webinars),
                'webinars' => $webinars,
            ];
        } catch (\Exception $e) {
            \Log::error('details error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public static function getSingle($id)
    {
        $webinar = Webinar::where('status', 'active')
            ->where('private', false)->where('id', $id)->first();

        if (!$webinar) {
            return null;
        }
        return self::brief($webinar, true);

    }

    public function handleFilters($request, $query)
    {
        try {
            $offset = $request->get('offset', null);
            $limit = $request->get('limit', null);
            $upcoming = $request->get('upcoming', null);
            $isFree = $request->get('free', null);
            $withDiscount = $request->get('discount', null);
            $isDownloadable = $request->get('downloadable', null);
            $sort = $request->get('sort', null);
            $filterOptions = $request->get('filter_option', null);
            $typeOptions = $request->get('type', []);
            $moreOptions = $request->get('moreOptions', []);
            $category = $request->get('cat', null);

            if (!empty($category) and is_numeric($category)) {
                $query->where('category_id', $category);
            }
            if (!empty($upcoming) and $upcoming == 1) {
                $query->whereNotNull('start_date')
                    ->where('start_date', '>=', time());
            }

            if (!empty($isFree) and $isFree == 1) {
                $query->where(function ($qu) {
                    $qu->whereNull('price')
                        ->orWhere('price', '0');
                });
            }

            if (!empty($isDownloadable) and $isDownloadable == 1) {
                $query->where('downloadable', 1);
            }

            if (!empty($withDiscount) and $withDiscount == 1) {
                $now = time();
                $webinarIdsHasDiscount = [];

                $tickets = Ticket::where('start_date', '<', $now)
                    ->where('end_date', '>', $now)
                    ->get();

                foreach ($tickets as $ticket) {
                    if ($ticket->isValid()) {
                        $webinarIdsHasDiscount[] = $ticket->webinar_id;
                    }
                }

                $webinarIdsHasDiscount = array_unique($webinarIdsHasDiscount);

                $query->whereIn('webinars.id', $webinarIdsHasDiscount);
            }

            if (!empty($sort)) {
                if ($sort == 'expensive') {
                    $query->orderBy('price', 'desc');
                }

                if ($sort == 'newest') {
                    $query->orderBy('created_at', 'desc');
                }

                if ($sort == 'inexpensive') {
                    $query->orderBy('price', 'asc');
                }

                if ($sort == 'bestsellers') {
                    $query->whereHas('sales')
                        ->with('sales')
                        ->get()
                        ->sortBy(function ($qu) {
                            return $qu->sales->count();
                        });
                }

                if ($sort == 'best_rates') {
                    $query->whereHas('reviews', function ($query) {
                        $query->where('status', 'active');
                    })->with('reviews')
                        ->get()
                        ->sortBy(function ($qu) {
                            return $qu->reviews->avg('rates');
                        });
                }
            }

            if (!empty($filterOptions)) {
                $webinarIdsFilterOptions = WebinarFilterOption::where('filter_option_id', $filterOptions)
                    ->pluck('webinar_id')
                    ->toArray();

                $query->whereIn('webinars.id', $webinarIdsFilterOptions);
            }

            if (!empty($typeOptions) and is_array($typeOptions)) {
                $query->whereIn('type', $typeOptions);
            }

            if (!empty($moreOptions) and is_array($moreOptions)) {
                if (in_array('subscribe', $moreOptions)) {
                    $query->where('subscribe', 1);
                }

                if (in_array('certificate_included', $moreOptions)) {
                    $query->whereHas('quizzes', function ($query) {
                        $query->where('certificate', 1)
                            ->where('status', 'active');
                    });
                }

                if (in_array('with_quiz', $moreOptions)) {
                    $query->whereHas('quizzes', function ($query) {
                        $query->where('status', 'active');
                    });
                }

                if (in_array('featured', $moreOptions)) {
                    $query->whereHas('feature', function ($query) {
                        $query->whereIn('page', ['home_categories', 'categories'])
                            ->where('status', 'publish');
                    });
                }
            }

            if (!empty($offset) && !empty($limit)) {
                $query->skip($offset);
            }
            if (!empty($limit)) {
                $query->take($limit);
            }
            return $query;
        } catch (\Exception $e) {
            \Log::error('handleFilters error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private static function liveWebinarStatus($webinar)
    {
        $live_webinar_status = false;
        if ($webinar->type == 'webinar') {
            if ($webinar->start_date > time()) {
                $live_webinar_status = 'not_conducted';
            } elseif ($webinar->isProgressing()) {
                $live_webinar_status = 'in_progress';
            } else {
                $live_webinar_status = 'finished';
            }
        }
        return $live_webinar_status;

    }

    private static function progress($webinar)
    {
        $user = apiAuth();

        $hasBought = $webinar->checkUserHasBought($user);
        $progress = null;
        if ($hasBought or $webinar->isWebinar()) {
            if ($webinar->isWebinar()) {
                if ($hasBought and $webinar->isProgressing()) {
                    $progress = $webinar->getProgress();
                } else {
                    $progress = $webinar->sales()->count() . '/' . $webinar->capacity;
                }
            } else {
                $progress = $webinar->getProgress();
            }
        }

        return $progress;
    }

    private static function isFavorite($webinar)
    {
        $user = apiAuth();
        $isFavorite = false;
        if (!empty($user)) {
            $isFavorite = Favorite::where('webinar_id', $webinar->id)
                ->where('user_id', $user->id)
                ->first();
        }
        return ($isFavorite) ? true : false;
    }

}
