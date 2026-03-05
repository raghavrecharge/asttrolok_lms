<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Exports\WebinarStudents;
use App\Http\Controllers\Controller;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\BundleWebinar;
use App\Models\Category;
use App\Models\Faq;
use App\Models\File;
use App\Models\Gift;
use App\Models\Prerequisite;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Session;
use App\Models\Tag;
use App\Models\TextLesson;
use App\Models\Ticket;
use App\Models\Translation\WebinarTranslation;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use App\Models\WebinarExtraDescription;
use App\User;
use App\Models\Webinar;
use App\Models\WebinarPartnerTeacher;
use App\Models\WebinarFilterOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeProduct;
use App\Services\PaymentEngine\AccessEngine;
use App\Services\PaymentEngine\PaymentLedgerService;

class WebinarController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            if ($user->isUser()) {
                abort(404);
            }

            $query = Webinar::where(function ($query) use ($user) {
                if ($user->isTeacher()) {
                    $query->where('teacher_id', $user->id);
                } elseif ($user->isOrganization()) {
                    $query->where('creator_id', $user->id);
                }
            });

            $data = $this->makeMyClassAndInvitationsData($query, $user, $request);
            $data['pageTitle'] = trans('webinars.webinars_list_page_title');

            return view(getTemplate() . '.panel.webinar.index', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
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
            $user = auth()->user();

            $invitedWebinarIds = WebinarPartnerTeacher::where('teacher_id', $user->id)->pluck('webinar_id')->toArray();

            $query = Webinar::query();

            if ($user->isUser()) {
                abort(404);
            }

            $query->whereIn('id', $invitedWebinarIds);

            $data = $this->makeMyClassAndInvitationsData($query, $user, $request);
            $data['pageTitle'] = trans('panel.invited_classes');

            return view(getTemplate() . '.panel.webinar.index', $data);
        } catch (\Exception $e) {
            \Log::error('invitations error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function organizationClasses(Request $request)
    {
        try {
            $user = auth()->user();

            if (!empty($user->organ_id)) {
                $query = Webinar::where('creator_id', $user->organ_id)
                    ->where('status', 'active');

                $query = $this->organizationClassesFilters($query, $request);

                $webinars = $query
                    ->orderBy('created_at', 'desc')
                    ->orderBy('updated_at', 'desc')
                    ->paginate(10);

                $data = [
                    'pageTitle' => trans('panel.organization_classes'),
                    'webinars' => $webinars,
                ];

                return view(getTemplate() . '.panel.webinar.organization_classes', $data);
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('organizationClasses error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function organizationClassesFilters($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $type = $request->get('type', null);
        $sort = $request->get('sort', null);
        $free = $request->get('free', null);

        $query = fromAndToDateFilter($from, $to, $query, 'start_date');

        if (!empty($type) and $type != 'all') {
            $query->where('type', $type);
        }

        if (!empty($sort) and $sort != 'all') {
            if ($sort == 'expensive') {
                $query->orderBy('price', 'desc');
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
                $query->with([
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                    }
                ])->get()
                    ->sortBy(function ($qu) {
                        return $qu->reviews->avg('rates');
                    });
            }
        }

        if (!empty($free) and $free == 'on') {
            $query->where(function ($qu) {
                $qu->whereNull('price')
                    ->orWhere('price', '<', '0');
            });
        }

        return $query;
    }

    private function makeMyClassAndInvitationsData($query, $user, $request)
    {
        $webinarHours = deepClone($query)->sum('duration');

        $onlyNotConducted = $request->get('not_conducted');
        if (!empty($onlyNotConducted)) {
            $query->where('status', 'active')
                ->where('start_date', '>', time());
        }

        $query->with([
            'reviews' => function ($query) {
                $query->where('status', 'active');
            },
            'category',
            'teacher'
        ])->orderBy('updated_at', 'desc');

        $webinarsCount = $query->count();

        $webinars = $query->paginate(10);

        $webinarSales = Sale::where('seller_id', $user->id)
            ->where('type', 'webinar')
            ->whereNotNull('webinar_id')
            ->whereNull('refund_at')
            ->with('webinar')
            ->get();

        $webinarSalesAmount = 0;
        $courseSalesAmount = 0;
        foreach ($webinarSales as $webinarSale) {
            if (!empty($webinarSale->webinar) and $webinarSale->webinar->type == 'webinar') {
                $webinarSalesAmount += $webinarSale->amount;
            } else {
                $courseSalesAmount += $webinarSale->amount;
            }
        }

        foreach ($webinars as $webinar) {
            $giftsIds = Gift::query()->where('webinar_id', $webinar->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')
                ->toArray();

            $sales = Sale::query()
                ->where(function ($query) use ($webinar, $giftsIds) {
                    $query->where('webinar_id', $webinar->id);
                    $query->orWhereIn('gift_id', $giftsIds);
                })
                ->whereNull('refund_at')
                ->get();

            $webinar->sales = $sales;
        }

        return [
            'webinars' => $webinars,
            'webinarsCount' => $webinarsCount,
            'webinarSalesAmount' => $webinarSalesAmount,
            'courseSalesAmount' => $courseSalesAmount,
            'webinarHours' => $webinarHours,
        ];
    }

    function array_replace_key($search, $replace, array $subject)
    {
        $updatedArray = [];

        foreach ($subject as $key => $value) {
            if (!is_array($value) && $key == $search) {
                $updatedArray = array_merge($updatedArray, [$replace => $value]);

                continue;
            }

            $updatedArray = array_merge($updatedArray, [$key => $value]);
        }

        return $updatedArray;
    }

    public function create(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user->isTeacher() and !$user->isOrganization()) {
                abort(404);
            }

            $userPackage = new UserPackage();
            $userCoursesCountLimited = $userPackage->checkPackageLimit('courses_count');

            if ($userCoursesCountLimited) {
                session()->put('registration_package_limited', $userCoursesCountLimited);

                return redirect()->back();
            }

            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $teachers = null;
            $isOrganization = $user->isOrganization();

            if ($isOrganization) {
                $teachers = User::where('role_name', Role::$teacher)
                    ->where('organ_id', $user->id)->get();
            }

            $stepCount = empty(getGeneralOptionsSettings('direct_publication_of_courses')) ? 8 : 7;

            $data = [
                'pageTitle' => trans('webinars.new_page_title'),
                'teachers' => $teachers,
                'categories' => $categories,
                'isOrganization' => $isOrganization,
                'currentStep' => 1,
                'stepCount' => $stepCount,
                'userLanguages' => getUserLanguagesLists(),
            ];

            return view(getTemplate() . '.panel.webinar.create', $data);
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
        try {
            $user = auth()->user();

            if (!$user->isTeacher() and !$user->isOrganization()) {
                abort(404);
            }

            $userPackage = new UserPackage();
            $userCoursesCountLimited = $userPackage->checkPackageLimit('courses_count');

            if ($userCoursesCountLimited) {
                session()->put('registration_package_limited', $userCoursesCountLimited);

                return redirect()->back();
            }

            $currentStep = $request->get('current_step', 1);

            $rules = [
                'type' => 'required|in:webinar,course,text_lesson',
                'title' => 'required|max:255',
                'thumbnail' => 'required',
                'image_cover' => 'required',
                'description' => 'required',
            ];

            $this->validate($request, $rules);

            $data = $request->all();

            if (empty($data['video_demo'])) {
                $data['video_demo_source'] = null;
            }

            if (!empty($data['video_demo_source']) and !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
                $data['video_demo_source'] = 'upload';
            }

            $webinar = Webinar::create([
                'lang' => $data['lang'],
                'teacher_id' => $user->isTeacher() ? $user->id : (!empty($data['teacher_id']) ? $data['teacher_id'] : $user->id),
                'creator_id' => $user->id,

                'slug' => preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']),
                'type' => $data['type'],
                'private' => (!empty($data['private']) and $data['private'] == 'on') ? true : false,
                'thumbnail' => $data['thumbnail'],
                'image_cover' => $data['image_cover'],
                'video_demo' => $data['video_demo'],
                'video_demo_source' => $data['video_demo'] ? $data['video_demo_source'] : null,
                'status' => ((!empty($data['draft']) and $data['draft'] == 1) or (!empty($data['get_next']) and $data['get_next'] == 1)) ? Webinar::$isDraft : Webinar::$pending,
                'created_at' => time(),
            ]);

            if ($webinar) {
                WebinarTranslation::updateOrCreate([
                    'webinar_id' => $webinar->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'seo_description' => $data['seo_description'],
                ]);
            }

            $notifyOptions = [
                '[u.name]' => $user->full_name,
                '[item_title]' => $webinar->title,
                '[content_type]' => trans('admin/main.course'),
            ];
            sendNotification("new_item_created", $notifyOptions, 1);

            $url = '/panel/webinars';
            if ($data['get_next'] == 1) {
                $url = '/panel/webinars/' . $webinar->id . '/step/2';
            }

            return redirect($url);
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function edit(Request $request, $id, $step = 1)
    {
        try {
            $user = auth()->user();
            $isOrganization = $user->isOrganization();

            if (!$user->isTeacher() and !$user->isOrganization()) {
                abort(404);
            }
            $locale = $request->get('locale', app()->getLocale());

            $stepCount = empty(getGeneralOptionsSettings('direct_publication_of_courses')) ? 8 : 7;

            $data = [
                'pageTitle' => trans('webinars.new_page_title_step', ['step' => $step]),
                'currentStep' => $step,
                'isOrganization' => $isOrganization,
                'userLanguages' => getUserLanguagesLists(),
                'locale' => mb_strtolower($locale),
                'defaultLocale' => getDefaultLocale(),
                'stepCount' => $stepCount
            ];

            $query = Webinar::where('id', $id)
                ->where(function ($query) use ($user) {
                    $query->where(function ($query) use ($user) {
                        $query->where('creator_id', $user->id)
                            ->orWhere('teacher_id', $user->id);
                    });

                    $query->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                        $query->where('teacher_id', $user->id);
                    });
                });

            if ($step == '1') {
                $data['teachers'] = $user->getOrganizationTeachers()->get();
            } elseif ($step == 2) {
                $query->with([
                    'category' => function ($query) {
                        $query->with(['filters' => function ($query) {
                            $query->with('options');
                        }]);
                    },
                    'filterOptions',
                    'webinarPartnerTeacher' => function ($query) {
                        $query->with(['teacher' => function ($query) {
                            $query->select('id', 'full_name');
                        }]);
                    },
                    'tags',
                ]);

                $categories = Category::where('parent_id', null)
                    ->with('subCategories')
                    ->get();

                $data['categories'] = $categories;
            } elseif ($step == 3) {
                $query->with([
                    'tickets' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                ]);
            } elseif ($step == 4) {
                $query->with([
                    'chapters' => function ($query) {
                        $query->orderBy('order', 'asc');
                        $query->with([
                            'chapterItems' => function ($query) {
                                $query->orderBy('order', 'asc');

                                $query->with([
                                    'quiz' => function ($query) {
                                        $query->with([
                                            'quizQuestions' => function ($query) {
                                                $query->orderBy('order', 'asc');
                                            }
                                        ]);
                                    }
                                ]);
                            }
                        ]);
                    },
                ]);
            } elseif ($step == 5) {
                $query->with([
                    'prerequisites' => function ($query) {
                        $query->with(['prerequisiteWebinar' => function ($qu) {
                            $qu->with(['teacher' => function ($q) {
                                $q->select('id', 'full_name');
                            }]);
                        }])->orderBy('order', 'asc');
                    }
                ]);
            } elseif ($step == 6) {
                $query->with([
                    'faqs' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'webinarExtraDescription' => function ($query) {
                        $query->orderBy('order', 'asc');
                    }
                ]);
            } elseif ($step == 7) {
                $query->with([
                    'quizzes',
                    'chapters' => function ($query) {
                        $query->where('status', WebinarChapter::$chapterActive)
                            ->orderBy('order', 'asc');
                    }
                ]);

                $teacherQuizzes = Quiz::where('webinar_id', null)
                    ->where('creator_id', $user->id)
                    ->whereNull('webinar_id')
                    ->get();

                $data['teacherQuizzes'] = $teacherQuizzes;
            }

            $webinar = $query->first();

            if (empty($webinar)) {
                abort(404);
            }

            $data['webinar'] = $webinar;

            $data['pageTitle'] = trans('public.edit') . ' ' . $webinar->title;

            $definedLanguage = [];
            if ($webinar->translations) {
                $definedLanguage = $webinar->translations->pluck('locale')->toArray();
            }

            $data['definedLanguage'] = $definedLanguage;

            if ($step == 2) {
                $data['webinarTags'] = $webinar->tags->pluck('title')->toArray();

                $webinarCategoryFilters = !empty($webinar->category) ? $webinar->category->filters : [];

                if (empty($webinar->category) and !empty($request->old('category_id'))) {
                    $category = Category::where('id', $request->old('category_id'))->first();

                    if (!empty($category)) {
                        $webinarCategoryFilters = $category->filters;
                    }
                }

                $data['webinarCategoryFilters'] = $webinarCategoryFilters;
            }

            if ($step == 3) {
                $data['sumTicketsCapacities'] = $webinar->tickets->sum('capacity');
            }

            return view(getTemplate() . '.panel.webinar.create', $data);
        } catch (\Exception $e) {
            \Log::error('edit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = auth()->user();

            if (!$user->isTeacher() and !$user->isOrganization()) {
                abort(404);
            }

            $rules = [];
            $data = $request->all();
            $currentStep = $data['current_step'];
            $getStep = $data['get_step'];
            $getNextStep = (!empty($data['get_next']) and $data['get_next'] == 1);
            $isDraft = (!empty($data['draft']) and $data['draft'] == 1);

            $webinar = Webinar::where('id', $id)
                ->where(function ($query) use ($user) {
                    $query->where(function ($query) use ($user) {
                        $query->where('creator_id', $user->id)
                            ->orWhere('teacher_id', $user->id);
                    });

                    $query->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                        $query->where('teacher_id', $user->id);
                    });
                })->first();

            if (empty($webinar)) {
                abort(404);
            }

            if ($currentStep == 1) {
                $rules = [
                    'type' => 'required|in:webinar,course,text_lesson',
                    'title' => 'required|max:255',
                    'thumbnail' => 'required',
                    'image_cover' => 'required',
                    'description' => 'required',
                ];
            }

            if ($currentStep == 2) {
                $rules = [
                    'category_id' => 'required',
                    'duration' => 'required|numeric',
                    'partners' => 'required_if:partner_instructor,on',
                    'capacity' => 'nullable|integer'
                ];

                if ($webinar->isWebinar()) {
                    $rules['start_date'] = 'required|date';
                }
            }

            $webinarRulesRequired = false;
            $directPublicationOfCourses = !empty(getGeneralOptionsSettings('direct_publication_of_courses'));

            if (!$directPublicationOfCourses and (($currentStep == 8 and !$getNextStep and !$isDraft) or (!$getNextStep and !$isDraft))) {
                $webinarRulesRequired = empty($data['rules']);
            }

            $this->validate($request, $rules);

            $status = ($isDraft or $webinarRulesRequired) ? Webinar::$isDraft : Webinar::$pending;

            if ($directPublicationOfCourses and !$getNextStep and !$isDraft) {
                $status = Webinar::$active;
            }

            $data['status'] = $status;
            $data['updated_at'] = time();

            if ($currentStep == 1) {
                $data['private'] = (!empty($data['private']) and $data['private'] == 'on');

                if (empty($data['video_demo'])) {
                    $data['video_demo_source'] = null;
                }

                if (!empty($data['video_demo_source']) and !in_array($data['video_demo_source'], ['upload', 'youtube', 'vimeo', 'external_link'])) {
                    $data['video_demo_source'] = 'upload';
                }
            }

            if ($currentStep == 2) {

                $userPackage = new UserPackage($webinar->creator);
                $userCoursesCapacityLimited = $userPackage->checkPackageLimit('courses_capacity', $data['capacity']);

                if ($userCoursesCapacityLimited) {
                    session()->put('registration_package_limited', $userCoursesCapacityLimited);

                    return redirect()->back()->withInput($data);
                }

                if ($webinar->isWebinar()) {
                    if (empty($data['timezone']) or !getFeaturesSettings('timezone_in_create_webinar')) {
                        $data['timezone'] = getTimezone();
                    }

                    $startDate = convertTimeToUTCzone($data['start_date'], $data['timezone']);

                    $data['start_date'] = $startDate->getTimestamp();
                }

                $data['forum'] = !empty($data['forum']) ? true : false;
                $data['support'] = !empty($data['support']) ? true : false;
                $data['certificate'] = !empty($data['certificate']) ? true : false;
                $data['downloadable'] = !empty($data['downloadable']) ? true : false;
                $data['partner_instructor'] = !empty($data['partner_instructor']) ? true : false;

                if (empty($data['partner_instructor'])) {
                    WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();
                    unset($data['partners']);
                }

                if ($data['category_id'] !== $webinar->category_id) {
                    WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
                }
            }

            if ($currentStep == 3) {
                $data['subscribe'] = !empty($data['subscribe']) ? true : false;
                $data['price'] = !empty($data['price']) ? convertPriceToDefaultCurrency($data['price']) : null;
                $data['organization_price'] = !empty($data['organization_price']) ? convertPriceToDefaultCurrency($data['organization_price']) : null;
            }

            $filters = $request->get('filters', null);
            if (!empty($filters) and is_array($filters)) {
                WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
                foreach ($filters as $filter) {
                    WebinarFilterOption::create([
                        'webinar_id' => $webinar->id,
                        'filter_option_id' => $filter
                    ]);
                }
            }

            if (!empty($request->get('tags'))) {
                $tags = explode(',', $request->get('tags'));
                Tag::where('webinar_id', $webinar->id)->delete();

                foreach ($tags as $tag) {
                    Tag::create([
                        'webinar_id' => $webinar->id,
                        'title' => $tag,
                    ]);
                }
            }

            if (!empty($request->get('partner_instructor')) and !empty($request->get('partners'))) {
                WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();

                foreach ($request->get('partners') as $partnerId) {
                    WebinarPartnerTeacher::create([
                        'webinar_id' => $webinar->id,
                        'teacher_id' => $partnerId,
                    ]);
                }
            }

            if ($webinar and $currentStep == 1) {
                WebinarTranslation::updateOrCreate([
                    'webinar_id' => $webinar->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'seo_description' => $data['seo_description'],
                ]);
            }

            unset($data['_token'],
                $data['current_step'],
                $data['draft'],
                $data['get_next'],
                $data['partners'],
                $data['tags'],
                $data['filters'],
                $data['ajax'],
                $data['title'],
                $data['description'],
                $data['seo_description']
            );

            if (empty($data['teacher_id']) and $user->isOrganization() and $webinar->creator_id == $user->id) {
                $data['teacher_id'] = $user->id;

            }

            $webinar->update($data);

            $stepCount = empty(getGeneralOptionsSettings('direct_publication_of_courses')) ? 8 : 7;

            $url = '/panel/webinars';
            if ($getNextStep) {
                $nextStep = (!empty($getStep) and $getStep > 0) ? $getStep : $currentStep + 1;

                $url = '/panel/webinars/' . $webinar->id . '/step/' . (($nextStep <= $stepCount) ? $nextStep : $stepCount);
            }

            if ($webinarRulesRequired) {
                $url = '/panel/webinars/' . $webinar->id . '/step/8';

                return redirect($url)->withErrors(['rules' => trans('validation.required', ['attribute' => 'rules'])]);
            }

            if ($status != Webinar::$active and !$getNextStep and !$isDraft and !$webinarRulesRequired) {
                sendNotification('course_created', ['[c.title]' => $webinar->title], $user->id);

                $notifyOptions = [
                    '[u.name]' => $user->full_name,
                    '[item_title]' => $webinar->title,
                    '[content_type]' => trans('admin/main.course'),
                ];
                sendNotification("content_review_request", $notifyOptions, 1);
            }

            return redirect($url);
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
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
            $user = auth()->user();

            if (!$user->isTeacher() and !$user->isOrganization()) {
                abort(404);
            }

            $webinar = Webinar::where('id', $id)
                ->where('creator_id', $user->id)
                ->first();

            if (!$webinar) {
                abort(404);
            }

            $webinar->delete();

            return response()->json([
                'code' => 200,
                'redirect_to' => $request->get('redirect_to')
            ], 200);
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function duplicate($id)
    {
        try {
            $user = auth()->user();
            if (!$user->isTeacher() and !$user->isOrganization()) {
                abort(404);
            }

            $webinar = Webinar::where('id', $id)
                ->where(function ($query) use ($user) {
                    $query->where(function ($query) use ($user) {
                        $query->where('creator_id', $user->id)
                            ->orWhere('teacher_id', $user->id);
                    });

                    $query->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                        $query->where('teacher_id', $user->id);
                    });
                })
                ->first();

            if (!empty($webinar)) {
                $new = $webinar->toArray();

                $title = $webinar->title . ' ' . trans('public.copy');
                $description = $webinar->description;
                $seo_description = $webinar->seo_description;

                $new['created_at'] = time();
                $new['updated_at'] = time();
                $new['status'] = Webinar::$pending;

                $new['slug'] = Webinar::makeSlug($title);

                foreach ($webinar->translatedAttributes as $attribute) {
                    unset($new[$attribute]);
                }

                unset($new['translations']);

                $newWebinar = Webinar::create($new);

                WebinarTranslation::updateOrCreate([
                    'webinar_id' => $newWebinar->id,
                    'locale' => mb_strtolower($webinar->locale),
                ], [
                    'title' => $title,
                    'description' => $description,
                    'seo_description' => $seo_description,
                ]);

                return redirect('/panel/webinars/' . $newWebinar->id . '/edit');
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('duplicate error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function exportStudentsList($id)
    {
        try {
            $user = auth()->user();

            if (!$user->isTeacher() and !$user->isOrganization()) {
                abort(404);
            }

            $webinar = Webinar::where('id', $id)
                ->where(function ($query) use ($user) {
                    $query->where(function ($query) use ($user) {
                        $query->where('creator_id', $user->id)
                            ->orWhere('teacher_id', $user->id);
                    });

                    $query->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                        $query->where('teacher_id', $user->id);
                    });
                })
                ->first();

            if (!empty($webinar)) {
                $giftsIds = Gift::query()->where('webinar_id', $webinar->id)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('date');
                        $query->orWhere('date', '<', time());
                    })
                    ->whereHas('sale')
                    ->pluck('id')
                    ->toArray();

                $sales = Sale::query()
                    ->where(function ($query) use ($webinar, $giftsIds) {
                        $query->where('webinar_id', $webinar->id);
                        $query->orWhereIn('gift_id', $giftsIds);
                    })
                    ->whereNull('refund_at')
                    ->with([
                        'buyer' => function ($query) {
                            $query->select('id', 'full_name', 'email', 'mobile');
                        }
                    ])->get();

                if (!empty($sales) and !$sales->isEmpty()) {

                    foreach ($sales as $sale) {
                        if (!empty($sale->gift_id)) {
                            $gift = $sale->gift;

                            $receipt = $gift->receipt;

                            if (!empty($receipt)) {
                                $sale->buyer = $receipt;
                            } else {
                                $newUser = new User();
                                $newUser->full_name = $gift->name;
                                $newUser->email = $gift->email;

                                $sale->buyer = $newUser;
                            }
                        }
                    }

                    $export = new WebinarStudents($sales);
                    return Excel::download($export, trans('panel.users') . '.xlsx');
                }

                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('webinars.export_list_error_not_student'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('exportStudentsList error: ' . $e->getMessage(), [
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
            $user = auth()->user();

            if (!$user->isTeacher() and !$user->isOrganization()) {
                return response('', 422);
            }

            $term = $request->get('term', null);
            $webinarId = $request->get('webinar_id', null);
            $option = $request->get('option', null);

            if (!empty($term)) {
                $webinars = Webinar::select('id', 'teacher_id')
                    ->whereTranslationLike('title', '%' . $term . '%')
                    ->where('id', '<>', $webinarId)
                    ->with(['teacher' => function ($query) {
                        $query->select('id', 'full_name');
                    }])

                    ->get();

                foreach ($webinars as $webinar) {
                    $webinar->title .= ' - ' . $webinar->teacher->full_name;
                }
                return response()->json($webinars, 200);
            }

            return response('', 422);
        } catch (\Exception $e) {
            \Log::error('search error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getTags(Request $request, $id)
    {
        try {
            $webinarId = $request->get('webinar_id', null);

            if (!empty($webinarId)) {
                $tags = Tag::select('id', 'title')
                    ->where('webinar_id', $webinarId)
                    ->get();

                return response()->json($tags, 200);
            }

            return response('', 422);
        } catch (\Exception $e) {
            \Log::error('getTags error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

 public function invoice($webinarId = null, $saleId)
{
        try {
            $user = auth()->user();

            $giftIds = Gift::query()
            ->where(function ($query) use ($user) {
                $query->where('email', $user->email);
                $query->orWhere('user_id', $user->id);
            })
            ->where('status', 'active')
            ->when($webinarId, function ($query) use ($webinarId) {
                $query->where('webinar_id', $webinarId);
            })
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->pluck('id')
            ->toArray();

            $sale = Sale::query()
            ->where('id', $saleId)
            ->where(function ($query) use ($webinarId, $user, $giftIds) {

                if ($webinarId) {
                    $query->where(function ($query) use ($webinarId, $user) {
                        $query->where('buyer_id', $user->id)
                            ->where('webinar_id', $webinarId);
                    });
                }

                $query->orWhere(function ($query) use ($user) {
                    $query->where('buyer_id', $user->id)
                        ->whereNotNull('subscription_id');
                });

                if (!empty($giftIds)) {
                    $query->orWhereIn('gift_id', $giftIds);
                }

                   $query->orWhere(function ($query) use ($user) {
                    $query->where('buyer_id', $user->id)
                      ->whereNotNull('bundle_id');
                });
            })
            ->whereNull('refund_at')
            ->with([
                'order',
                 'orderAddress',
                'buyer' => function ($query) {
                    $query->select('id', 'full_name');
                },
                'gift'
            ])
            ->first();

            if (empty($sale)) {
            abort(404);
            }

            if (!empty($sale->gift_id) && $sale->gift) {
            $gift = $sale->gift;
            $sale->gift_recipient = !empty($gift->receipt) ? $gift->receipt->full_name : $gift->name;
            }

            $webinar = null;
            if ($webinarId) {
            $webinar = Webinar::where('status', 'active')
                ->where('id', $webinarId)
                ->with([
                    'teacher' => function ($query) {
                        $query->select('id', 'full_name');
                    },
                    'creator' => function ($query) {
                        $query->select('id', 'full_name');
                    },
                    'webinarPartnerTeacher.teacher' => function ($query) {
                        $query->select('id', 'full_name');
                    }
                ])
                ->first();
            }

            $data = [
            'pageTitle' => trans('webinars.invoice_page_title'),
            'sale' => $sale,
            'webinar' => $webinar,
            ];

            return view(getTemplate() . '.panel.webinar.invoice', $data);
        } catch (\Exception $e) {
            \Log::error('invoice error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
     public function invoice_part($webinarId, $saleId)
    {
        try {
            $user = auth()->user();

            $giftIds = Gift::query()
                ->where(function ($query) use ($user) {
                    $query->where('email', $user->email);
                    $query->orWhere('user_id', $user->id);
                })
                ->where('status', 'active')
                ->where('webinar_id', $webinarId)
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')->toArray();

            $sale = WebinarPartPayment::query()
                ->where('id', $saleId)
                ->where(function ($query) use ($webinarId, $user, $giftIds) {
                    $query->where(function ($query) use ($webinarId, $user) {
                        $query->where('user_id', $user->id);
                        $query->where('webinar_id', $webinarId);
                    });

                    if (!empty($giftIds)) {
                        $query->orWhereIn('gift_id', $giftIds);
                    }
                })
                ->with([

                    'buyer' => function ($query) {
                        $query->select('id', 'full_name');
                    },
                ])
                ->first();

            if (!empty($sale)) {

                if (!empty($sale->gift_id)) {
                    $gift = $sale->gift;

                    $sale->gift_recipient = !empty($gift->receipt) ? $gift->receipt->full_name : $gift->name;
                }

                $webinar = Webinar::where('status', 'active')
                    ->where('id', $webinarId)
                    ->with([
                        'teacher' => function ($query) {
                            $query->select('id', 'full_name');
                        },
                        'creator' => function ($query) {
                            $query->select('id', 'full_name');
                        },
                        'webinarPartnerTeacher' => function ($query) {
                            $query->with([
                                'teacher' => function ($query) {
                                    $query->select('id', 'full_name');
                                },
                            ]);
                        }
                    ])
                    ->first();

                if (!empty($webinar)) {
                    $data = [
                        'pageTitle' => trans('webinars.invoice_page_title'),
                        'sale' => $sale,
                        'webinar' => $webinar
                    ];

                    return view(getTemplate() . '.panel.webinar.invoicepart', $data);
                }
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('invoice_part error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
 public function invoice_meeting($webinarId, $saleId)
    {
        try {
            $user = auth()->user();

            $giftIds = Gift::query()
                ->where(function ($query) use ($user) {
                    $query->where('email', $user->email);
                    $query->orWhere('user_id', $user->id);
                })
                ->where('status', 'active')
                ->where('webinar_id', $webinarId)
                ->where(function ($query) {
                    $query->whereNull('date');
                    $query->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')->toArray();

            $sale = Sale::query()
                ->where('id', $saleId)
                ->where(function ($query) use ($webinarId, $user, $giftIds) {
                    $query->where(function ($query) use ($webinarId, $user) {
                        $query->where('buyer_id', $user->id);
                        $query->where('meeting_id', $webinarId);
                    });

                    if (!empty($giftIds)) {
                        $query->orWhereIn('gift_id', $giftIds);
                    }
                })
                ->with([

                    'buyer' => function ($query) {
                        $query->select('id', 'full_name');
                    },
                ])
                ->first();

            if (!empty($sale)) {

                if (!empty($sale->gift_id)) {
                    $gift = $sale->gift;

                    $sale->gift_recipient = !empty($gift->receipt) ? $gift->receipt->full_name : $gift->name;
                }

                $webinar = ReserveMeeting::where('meeting_id', $webinarId)
                ->where('sale_id', $saleId)
                ->with([
                'meeting' => function ($query) {
                    $query->with([
                        'creator' => function ($query) {
                            $query->select('id', 'full_name');
                        }
                    ]);
                },
                'user' => function ($query) {
                    $query->select('id', 'full_name');
                },

                    ])
                    ->first();

                if (!empty($webinar)) {
                    $data = [
                        'pageTitle' => trans('webinars.invoice_page_title'),
                        'sale' => $sale,
                        'webinar' => $webinar
                    ];

                    return view(getTemplate() . '.panel.webinar.invoicemeeting', $data);
                }
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('invoice_meeting error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function purchases(Request $request, PaymentLedgerService $ledger, AccessEngine $access)
    {
        try {
            $user = auth()->user();

            // Subquery: pick the best (most relevant) sale per product
            // Priority: active > partially_refunded > pending_payment > others; then newest id
            $bestSaleIds = UpeSale::where('user_id', $user->id)
                ->selectRaw('MAX(CASE 
                    WHEN status = "active" THEN 4
                    WHEN status = "partially_refunded" THEN 3
                    WHEN status = "pending_payment" THEN 2
                    ELSE 1
                END) as priority')
                ->selectRaw('product_id')
                ->groupBy('product_id')
                ->pluck('product_id');

            // For each product, get the single best sale (EXCLUDE refunded + support-granted free access)
            $deduped = collect();
            foreach ($bestSaleIds as $productId) {
                $sale = UpeSale::where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->whereNotIn('status', ['refunded', 'cancelled', 'expired'])
                    ->whereHas('product', function ($q) {
                        $q->whereIn('product_type', ['webinar', 'course_video', 'course_live', 'bundle']);
                    })
                    // Exclude support-granted free access (free_course_grant / mentor / relative)
                    ->where(function ($q) {
                        $q->where('sale_type', '!=', 'free')
                          ->orWhereNull('support_request_id');
                    })
                    ->orderByRaw("FIELD(status, 'active', 'partially_refunded', 'pending_payment', 'completed') ASC")
                    ->orderByDesc('id')
                    ->first();
                if ($sale) {
                    $deduped->push($sale->id);
                }
            }

            $query = UpeSale::whereIn('id', $deduped)->with(['product', 'installmentPlan']);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $sales = $query->orderByDesc('id')->paginate(10);

            // Fetch items for paginated sales to avoid N+1 queries
            $webinarIds = $sales->where('product.product_type', '!=', 'bundle')->pluck('product.external_id');
            $bundleIds = $sales->where('product.product_type', 'bundle')->pluck('product.external_id');
            
            $webinars = \App\Models\Webinar::whereIn('id', $webinarIds)->with(['teacher', 'category'])->get()->keyBy('id');
            $bundles = \App\Models\Bundle::whereIn('id', $bundleIds)->with(['teacher', 'category'])->get()->keyBy('id');

            foreach ($sales as $sale) {
                if ($sale->product->product_type === 'bundle') {
                    $sale->item = $bundles->get($sale->product->external_id);
                } else {
                    $sale->item = $webinars->get($sale->product->external_id);
                }
            }

            $time = time();

            // Calculate stats for the dashboard info widgets (EXCLUDE refunded)
            // We use the full deduped query (not paginated) for stats
            $allMySales = UpeSale::whereIn('id', $deduped)->with('product')->get();
            
            $purchasedCount = $allMySales->count();
            $upComing = 0;
            $hours = 0;

            foreach ($allMySales as $sale) {
                $product = $sale->product;
                if ($product) {
                    if ($product->product_type === 'bundle') {
                        $bundle = \App\Models\Bundle::find($product->external_id);
                        if ($bundle) {
                            $hours += $bundle->getBundleDuration();
                        }
                    } else {
                        $webinar = \App\Models\Webinar::find($product->external_id);
                        if ($webinar) {
                            $hours += $webinar->duration;
                            if ($webinar->start_date > $time) {
                                $upComing++;
                            }
                        }
                    }
                }
            }

            $data = [
                'pageTitle' => trans('webinars.webinars_purchases_page_title'),
                'sales' => $sales,
                'purchasedCount' => $purchasedCount,
                'upComing' => $upComing,
                'hours' => $hours,
                'ledger' => $ledger,
                'access' => $access,
            ];

            return view(getTemplate() . '.panel.webinar.purchases', $data);
        } catch (\Exception $e) {
            \Log::error('purchases error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function refundedPurchases()
    {
        try {
            $user = auth()->user();

            // UPE refunded sales
            $upeSales = UpeSale::where('user_id', $user->id)
                ->where('status', 'refunded')
                ->whereHas('product', function ($q) {
                    $q->whereIn('product_type', ['webinar', 'course_video', 'course_live', 'bundle']);
                })
                ->with('product')
                ->orderByDesc('id')
                ->get();

            // Legacy refunded sales (not already in UPE set)
            $legacySales = \App\Models\Sale::where('buyer_id', $user->id)
                ->whereNotNull('refund_at')
                ->with('webinar')
                ->orderByDesc('id')
                ->get();

            // Merge: build unified list (UPE primary, fallback to legacy)
            $upeWebinarIds = $upeSales->map(function ($s) {
                return optional($s->product)->external_id;
            })->filter()->toArray();

            $refunds = collect();

            foreach ($upeSales as $sale) {
                $product = $sale->product;
                if (!$product) continue;
                $item = \App\Models\Webinar::find($product->external_id)
                     ?? \App\Models\Bundle::find($product->external_id);
                if (!$item) continue;
                $refunds->push([
                    'item'        => $item,
                    'sale'        => $sale,
                    'refunded_at' => $sale->updated_at,
                    'amount'      => $sale->base_fee_snapshot ?? 0,
                    'source'      => 'upe',
                ]);
            }

            foreach ($legacySales as $sale) {
                if (!$sale->webinar) continue;
                // Skip if already included via UPE
                if (in_array($sale->webinar_id, $upeWebinarIds)) continue;
                $refunds->push([
                    'item'        => $sale->webinar,
                    'sale'        => $sale,
                    'refunded_at' => $sale->refund_at ? \Carbon\Carbon::createFromTimestamp($sale->refund_at) : null,
                    'amount'      => $sale->total_amount ?? 0,
                    'source'      => 'legacy',
                ]);
            }

            $data = [
                'pageTitle'      => 'Refunded Courses',
                'refunds'        => $refunds,
                'refundedCount'  => $refunds->count(),
            ];

            return view(getTemplate() . '.panel.webinar.refunded_purchases', $data);
        } catch (\Exception $e) {
            \Log::error('refundedPurchases error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
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

    public function getJoinInfo(Request $request)
    {
        try {
            $data = $request->all();
            if (!empty($data['webinar_id'])) {
                $user = auth()->user();

                $checkSale = Sale::where('buyer_id', $user->id)
                    ->where('webinar_id', $data['webinar_id'])
                    ->where('type', 'webinar')
                    ->whereNull('refund_at')
                    ->first();

                if (!empty($checkSale)) {
                    $webinar = Webinar::where('status', 'active')
                        ->where('id', $data['webinar_id'])
                        ->first();

                    if (!empty($webinar)) {
                        $session = Session::select('id', 'creator_id', 'date', 'link', 'zoom_start_link', 'session_api', 'api_secret')
                            ->where('webinar_id', $webinar->id)
                            ->where('date', '>=', time())
                            ->orderBy('date', 'asc')
                            ->whereDoesntHave('agoraHistory', function ($query) {
                                $query->whereNotNull('end_at');
                            })
                            ->first();

                        if (!empty($session)) {
                            $session->date = dateTimeFormat($session->date, 'Y-m-d H:i', false);

                            $session->link = $session->getJoinLink(true);

                            return response()->json([
                                'code' => 200,
                                'session' => $session
                            ], 200);
                        }
                    }
                }
            }

            return response()->json([], 422);
        } catch (\Exception $e) {
            \Log::error('getJoinInfo error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getNextSessionInfo($id)
    {
        try {
            $user = auth()->user();

            $webinar = Webinar::where('id', $id)
                ->where(function ($query) use ($user) {
                    $query->where(function ($query) use ($user) {
                        $query->where('creator_id', $user->id)
                            ->orWhere('teacher_id', $user->id);
                    });

                    $query->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                        $query->where('teacher_id', $user->id);
                    });
                })->first();

            if (!empty($webinar)) {
                $session = Session::where('webinar_id', $webinar->id)
                    ->where('date', '>=', time())
                    ->orderBy('date', 'asc')
                    ->where('status', Session::$Active)
                    ->whereDoesntHave('agoraHistory', function ($query) {
                        $query->whereNotNull('end_at');
                    })
                    ->first();

                if (!empty($session) and $session->title) {
                    $session->date = dateTimeFormat($session->date, 'Y-m-d H:i', false);

                    $session->link = $session->getJoinLink(true);

                    if (!empty($session->agora_settings)) {
                        $session->agora_settings = json_decode($session->agora_settings);
                    }
                }

                $chapters = WebinarChapter::query()
                    ->where('user_id', $user->id)
                    ->where('webinar_id', $webinar->id)
                    ->orderBy('order', 'asc')
                    ->get();

                return response()->json([
                    'code' => 200,
                    'session' => $session,
                    'webinar_id' => $webinar->id,
                    'chapters' => $chapters
                ], 200);
            }

            return response()->json([], 422);
        } catch (\Exception $e) {
            \Log::error('getNextSessionInfo error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function orderItems(Request $request)
    {
        try {
            $user = auth()->user();
            $data = $request->all();

            $validator = Validator::make($data, [
                'items' => 'required',
                'table' => 'required',
            ]);

            if ($validator->fails()) {
                return response([
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $tableName = $data['table'];
            $itemIds = explode(',', $data['items']);

            if (!is_array($itemIds) and !empty($itemIds)) {
                $itemIds = [$itemIds];
            }

            if (!empty($itemIds) and is_array($itemIds) and count($itemIds)) {
                switch ($tableName) {
                    case 'tickets':
                        foreach ($itemIds as $order => $id) {
                            Ticket::where('id', $id)
                                ->where('creator_id', $user->id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                    case 'sessions':
                        foreach ($itemIds as $order => $id) {
                            Session::where('id', $id)
                                ->where('creator_id', $user->id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                    case 'files':
                        foreach ($itemIds as $order => $id) {
                            File::where('id', $id)
                                ->where('creator_id', $user->id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                    case 'text_lessons':
                        foreach ($itemIds as $order => $id) {
                            TextLesson::where('id', $id)
                                ->where('creator_id', $user->id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                    case 'prerequisites':
                        $webinarIds = $user->webinars()->pluck('id')->toArray();

                        foreach ($itemIds as $order => $id) {
                            Prerequisite::where('id', $id)
                                ->whereIn('webinar_id', $webinarIds)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                    case 'faqs':
                        foreach ($itemIds as $order => $id) {
                            Faq::where('id', $id)
                                ->where('creator_id', $user->id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                    case 'webinar_chapters':
                        foreach ($itemIds as $order => $id) {
                            WebinarChapter::where('id', $id)
                                ->where('user_id', $user->id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;
                    case 'webinar_chapter_items':
                        foreach ($itemIds as $order => $id) {
                            WebinarChapterItem::where('id', $id)
                                ->where('user_id', $user->id)
                                ->update(['order' => ($order + 1)]);
                        }
                    case 'bundle_webinars':
                        foreach ($itemIds as $order => $id) {
                            BundleWebinar::where('id', $id)
                                ->where('creator_id', $user->id)
                                ->update(['order' => ($order + 1)]);
                        }
                        break;

                    case 'webinar_extra_descriptions_learning_materials':
                        foreach ($itemIds as $order => $id) {
                            WebinarExtraDescription::where('id', $id)
                                ->where('creator_id', $user->id)
                                ->where('type', 'learning_materials')
                                ->update(['order' => ($order + 1)]);
                        }
                        break;

                    case 'webinar_extra_descriptions_company_logos':
                        foreach ($itemIds as $order => $id) {
                            WebinarExtraDescription::where('id', $id)
                                ->where('creator_id', $user->id)
                                ->where('type', 'company_logos')
                                ->update(['order' => ($order + 1)]);
                        }
                        break;

                    case 'webinar_extra_descriptions_requirements':
                        foreach ($itemIds as $order => $id) {
                            WebinarExtraDescription::where('id', $id)
                                ->where('creator_id', $user->id)
                                ->where('type', 'requirements')
                                ->update(['order' => ($order + 1)]);
                        }
                        break;

                }
            }

            return response()->json([
                'title' => trans('public.request_success'),
                'msg' => trans('update.items_sorted_successful')
            ]);
        } catch (\Exception $e) {
            \Log::error('orderItems error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getContentItemByLocale(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'item_id' => 'required',
            'locale' => 'required',
            'relation' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where(function ($query) use ($user) {
                    $query->where('creator_id', $user->id)
                        ->orWhere('teacher_id', $user->id);
                });

                $query->orWhereHas('webinarPartnerTeacher', function ($query) use ($user) {
                    $query->where('teacher_id', $user->id);
                });
            })->first();

        if (!empty($webinar)) {

            $itemId = $data['item_id'];
            $locale = $data['locale'];
            $relation = $data['relation'];

            if (!empty($webinar->$relation)) {
                $item = $webinar->$relation->where('id', $itemId)->first();

                if (!empty($item)) {
                    foreach ($item->translatedAttributes as $attribute) {
                        try {
                            $item->$attribute = $item->translate(mb_strtolower($locale))->$attribute;
                        } catch (\Exception $e) {
                            $item->$attribute = null;
                        }
                    }

                    return response()->json([
                        'item' => $item
                    ], 200);
                }
            }
        }

        abort(403);
    }

    public function storeWatchedDuration(Request $request)
    {
        try {
            $user = auth()->user();
            $data = $request->all();

            if($data['item_id'] || $data['chapter_id'] ==0){
                    $ChapterRecord = DB::table('webinar_chapter_items')
                ->where('item_id', $data['item_id'])
                ->where('type', 'file')
                ->first();
                if($ChapterRecord){
                $data['chapter_id'] =$ChapterRecord->chapter_id;
                }
                }
            $existingRecord = DB::table('course_progress')
                ->where('item_id', $data['item_id'])
                ->where('user_id', $user->id)
                ->where('webinar_id', $data['webinar_id'])
                ->where('chapter_id', $data['chapter_id'])

                ->first();

            if ($existingRecord) {
                if ($data['watched_duration'] > $existingRecord->watched_duration && $data['total_duration'] >= $existingRecord->total_duration) {

                    DB::table('course_progress')->where('id', $existingRecord->id)->update([
                        'watched_duration' => $data['watched_duration'],
                        'watch_percentage' => $data['watch_percentage'],
                        'total_duration' => $data['total_duration'],
                        'updated_at' => now(),
                    ]);
                }
                 return response()->json(['message' => 'Watched duration updated successfully'], 200);
                return true;
            } else {
              DB::table('course_progress')->insert([
                        'item_id' => $data['item_id'],
                        'user_id' => $user->id,
                        'webinar_id' => $data['webinar_id'],
                        'chapter_id' => $data['chapter_id'],
                        'watched_duration' => $data['watched_duration'],
                        'watch_percentage' => $data['watch_percentage'],
                        'total_duration' => $data['total_duration'],
                        'status' =>1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
              return response()->json(['message' => 'Watched duration stored successfully'], 200);
            return true;
            }
        } catch (\Exception $e) {
            \Log::error('storeWatchedDuration error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
      public function storeWatchedDurationSubscription(Request $request)
    {
        try {
            $user = auth()->user();
            $data = $request->all();

            $existingRecord = DB::table('subscription_course_progress')
                ->where('item_id', $data['item_id'])
                ->where('user_id', $user->id)
                ->where('subscription_id', $data['webinar_id'])
                ->first();

            if ($existingRecord) {

                if ($data['watched_duration'] > $existingRecord->watched_duration && $data['total_duration'] >= $existingRecord->total_duration) {

                    DB::table('subscription_course_progress')->where('id', $existingRecord->id)->update([
                        'watched_duration' => $data['watched_duration'],
                        'watch_percentage' => $data['watch_percentage'],
                        'total_duration' => $data['total_duration'],
                        'updated_at' => now(),
                    ]);
                }
                 return response()->json(['message' => 'Watched duration updated successfully'], 200);
                return true;
            } else
            {

              DB::table('subscription_course_progress')->insert([
                        'item_id' => $data['item_id'],
                        'user_id' => $user->id,
                        'subscription_id' => $data['webinar_id'],
                        'watched_duration' => $data['watched_duration'],
                        'watch_percentage' => $data['watch_percentage'],
                        'total_duration' => $data['total_duration'],
                        'status' =>1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
              return response()->json(['message' => 'Watched duration stored successfully'], 200);
            return true;
            }
        } catch (\Exception $e) {
            \Log::error('storeWatchedDurationSubscription error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}