<?php

namespace App\Models;

use App\User;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Jorenvh\Share\ShareFacade;
use Spatie\CalendarLinks\Link;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Webinar extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'webinars';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    static $active = 'active';
    static $pending = 'pending';
    static $isDraft = 'is_draft';
    static $inactive = 'inactive';

    static $webinar = 'webinar';
    static $course = 'course';
    static $textLesson = 'text_lesson';

    static $statuses = [
        'active', 'pending', 'is_draft', 'inactive'
    ];

    static $videoDemoSource = ['upload', 'youtube', 'vimeo', 'external_link'];

    public $translatedAttributes = ['h1','title', 'description', 'seo_description', 'seo_title'];
    public function getH1Attribute()
    {
        return getTranslateAttributeValue($this, 'h1');
    }
    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'description');
    }

    public function getSeoTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'seo_title');
    }
    public function getSeoDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'seo_description');
    }

    public function getPriceAttribute()
    {
        $result = $this->attributes['price'] ?? null;

        $user = auth()->user();

        if (!empty($this->attributes['organization_price']) and !empty($user) and $this->creator->isOrganization() and $user->organ_id == $this->creator_id) {
            $result = $this->attributes['organization_price'];
        }

        return $result;
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo('App\User', 'teacher_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id', 'id');
    }

    public function filterOptions()
    {
        return $this->hasMany('App\Models\WebinarFilterOption', 'webinar_id', 'id');
    }

    public function tickets()
    {
        return $this->hasMany('App\Models\Ticket', 'webinar_id', 'id');
    }

    public function chapters()
    {
        return $this->hasMany('App\Models\WebinarChapter', 'webinar_id', 'id');
    }

    public function sessions()
    {
        return $this->hasMany('App\Models\Session', 'webinar_id', 'id');
    }

    public function files()
    {
        return $this->hasMany('App\Models\File', 'webinar_id', 'id');
    }

    public function assignments()
    {
        return $this->hasMany('App\Models\WebinarAssignment', 'webinar_id', 'id');
    }

    public function textLessons()
    {
        return $this->hasMany('App\Models\TextLesson', 'webinar_id', 'id');
    }

    public function faqs()
    {
        return $this->hasMany('App\Models\Faq', 'webinar_id', 'id');
    }
public function faqItems()
{
    return $this->faqs()->where('type', 'faq');
}

public function whyChooseUsItems()
{
    return $this->faqs()->where('type', 'why_choose_us');
}

    public function webinarExtraDescription()
    {
        return $this->hasMany('App\Models\WebinarExtraDescription', 'webinar_id', 'id');
    }

    public function prerequisites()
    {
        return $this->hasMany('App\Models\Prerequisite', 'webinar_id', 'id');
    }

    public function quizzes()
    {
        return $this->hasMany('App\Models\Quiz', 'webinar_id', 'id');
    }

    public function webinarPartnerTeacher()
    {
        return $this->hasMany('App\Models\WebinarPartnerTeacher', 'webinar_id', 'id');
    }

    public function tags()
    {
        return $this->hasMany('App\Models\Tag', 'webinar_id', 'id');
    }

    public function purchases()
    {
        return $this->hasMany('App\Models\Purchase', 'webinar_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'webinar_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\WebinarReview', 'webinar_id', 'id');
    }

    public function sales()
    {
        return $this->hasMany('App\Models\Sale', 'webinar_id', 'id')
            ->whereNull('refund_at')
            ->where('type', 'webinar');
    }

    public function feature()
    {
        return $this->hasOne('App\Models\FeatureWebinar', 'webinar_id', 'id');
    }

    public function noticeboards()
    {
        return $this->hasMany('App\Models\CourseNoticeboard', 'webinar_id', 'id');
    }

    public function forums()
    {
        return $this->hasMany('App\Models\CourseForum', 'webinar_id', 'id');
    }

    public function getRate()
    {
        $rate = 0;

        if (!empty($this->avg_rates)) {
            $rate = $this->avg_rates;
        } else {
            $reviews = $this->reviews()
                ->where('status', 'active')
                ->get();

            if (!empty($reviews) and $reviews->isNotEmpty()) {
                $rate = number_format($reviews->avg('rates'), 2);
            }
        }

        if ($rate > 5) {
            $rate = 5;
        }

        return $rate > 0 ? number_format($rate, 2) : 0;
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public static function makeSlug($title)
    {
        return SlugService::createSlug(self::class, 'slug', $title);
    }

    public function bestTicket($with_percent = false)
    {
        $ticketPercent = 0;
        $bestTicket = $this->price;

        $activeSpecialOffer = $this->activeSpecialOffer();

        if ($activeSpecialOffer) {
            $bestTicket = $this->price - ($this->price * $activeSpecialOffer->percent / 100);
            $ticketPercent = $activeSpecialOffer->percent;
        } else {
            foreach ($this->tickets as $ticket) {

                if ($ticket->isValid()) {
                    $discount = $this->price - ($this->price * $ticket->discount / 100);

                    if ($bestTicket > $discount) {
                        $bestTicket = $discount;
                        $ticketPercent = $ticket->discount;
                    }
                }
            }
        }

        if ($with_percent) {
            return [
                'bestTicket' => $bestTicket,
                'percent' => $ticketPercent
            ];
        }

        return $bestTicket;
    }

    public function getDiscount($ticket = null, $user = null)
    {
        $activeSpecialOffer = $this->activeSpecialOffer();

        $discountOut = $activeSpecialOffer ? $this->price * $activeSpecialOffer->percent / 100 : 0;

        if (!empty($user) and !empty($user->getUserGroup()) and isset($user->getUserGroup()->discount) and $user->getUserGroup()->discount > 0) {
            $discountOut += $this->price * $user->getUserGroup()->discount / 100;
        }

        if (!empty($ticket) and $ticket->isValid()) {
            $discountOut += $this->price * $ticket->discount / 100;
        }

        return $discountOut;
    }

    public function getDiscountPercent()
    {
        $percent = 0;

        $activeSpecialOffer = $this->activeSpecialOffer();

        if (!empty($activeSpecialOffer)) {
            $percent += $activeSpecialOffer->percent;
        }

        $tickets = Ticket::where('webinar_id', $this->id)->get();

        foreach ($tickets as $ticket) {
            if (!empty($ticket) and $ticket->isValid()) {
                $percent += $ticket->discount;
            }
        }

        return $percent;
    }

    public function getWebinarCapacity()
    {
        $salesCount = !empty($this->sales_count) ? $this->sales_count : $this->sales()->count();

        $capacity = $this->capacity - $salesCount;

        return $capacity > 0 ? $capacity : 0;
    }

    public function getExpiredAccessDays($purchaseDate, $giftId = null)
    {
        if (!empty($giftId)) {
            $gift = Gift::query()->where('id', $giftId)
                ->where('status', 'active')
                ->first();

            if (!empty($gift) and !empty($gift->date)) {
                $purchaseDate = $gift->date;
            }
        }

        return strtotime("+{$this->access_days} days", $purchaseDate);
    }

    public function checkHasExpiredAccessDays($purchaseDate, $giftId = null)
    {

        if (!empty($giftId)) {
            $gift = Gift::query()->where('id', $giftId)
                ->where('status', 'active')
                ->first();

            if (!empty($gift) and !empty($gift->date)) {
                $purchaseDate = $gift->date;
            }
        }

        $time = time();

        return strtotime("+{$this->access_days} days", $purchaseDate) > $time;
    }

    public function getSaleItem($user = null)
    {
        if (empty($user)) {
            $user = auth()->user();
        }

        if (!empty($user)) {
            $productTypes = match ($this->type) {
                'webinar' => ['webinar'],
                'course' => ['course_video'],
                'text_lesson' => ['course_video'],
                default => ['course_video', 'webinar'],
            };

            $upeProduct = \App\Models\PaymentEngine\UpeProduct::whereIn('product_type', $productTypes)
                ->where('external_id', $this->id)
                ->first();

            if ($upeProduct) {
                return \App\Models\PaymentEngine\UpeSale::where('user_id', $user->id)
                    ->where('product_id', $upeProduct->id)
                    ->whereIn('status', ['active', 'partially_refunded'])
                    ->orderByDesc('id')
                    ->first();
            }
        }

        return null;
    }

    public function getSaleItem1($user = null)
    {
        if (empty($user)) {
            $user = auth()->user();
        }

        if (!empty($user)) {
            $productTypes = match ($this->type) {
                'webinar' => ['webinar'],
                'course' => ['course_video'],
                'text_lesson' => ['course_video'],
                default => ['course_video', 'webinar'],
            };

            $upeProduct = \App\Models\PaymentEngine\UpeProduct::whereIn('product_type', $productTypes)
                ->where('external_id', $this->id)
                ->first();

            if ($upeProduct) {
                return \App\Models\PaymentEngine\UpeSale::where('user_id', $user->id)
                    ->where('product_id', $upeProduct->id)
                    ->where('pricing_mode', 'installment')
                    ->whereIn('status', ['active', 'partially_refunded', 'pending_payment'])
                    ->orderByDesc('id')
                    ->first();
            }
        }

        return null;
    }
    /**
     * V-15 FIX: Returns true ONLY if user has a real purchase/access grant.
     * Does NOT include admin bypass, creator bypass, or teacher bypass.
     * Use this for financial logic (refund eligibility, payment checks, etc.)
     */
    public function hasPurchased($user = null): bool
    {
        if (empty($user) and auth()->check()) {
            $user = auth()->user();
        }

        if (empty($user)) {
            return false;
        }

        $sale = $this->getSaleItem($user);
        return !empty($sale);
    }

    public function checkUserHasBought($user = null, $checkExpired = true, $test = false): bool
    {
        if (empty($user) and auth()->check()) {
            $user = auth()->user();
        }

        if (empty($user)) {
            return false;
        }

        // Role-based bypass: creator, teacher, partner teacher, admin
        if ($this->creator_id == $user->id or $this->teacher_id == $user->id) {
            return true;
        }

        $partnerTeachers = !empty($this->webinarPartnerTeacher) ? $this->webinarPartnerTeacher->pluck('teacher_id')->toArray() : [];
        if (in_array($user->id, $partnerTeachers)) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // UPE AccessEngine: check direct course access
        $accessEngine = app(\App\Services\PaymentEngine\AccessEngine::class);
        $productTypes = match ($this->type) {
            'webinar' => ['webinar'],
            'course' => ['course_video'],
            'text_lesson' => ['course_video'],
            default => ['course_video', 'webinar'],
        };

        $upeProduct = \App\Models\PaymentEngine\UpeProduct::whereIn('product_type', $productTypes)
            ->where('external_id', $this->id)
            ->first();

        if ($upeProduct) {
            $result = $accessEngine->hasAccess($user->id, $upeProduct->id);
            if ($result->hasAccess) {
                return true;
            }
        }

        // Check bundle access: if this webinar is in any bundle the user bought
        $bundleWebinar = BundleWebinar::where('webinar_id', $this->id)
            ->with(['bundle'])->get();

        if ($bundleWebinar->isNotEmpty()) {
            foreach ($bundleWebinar as $item) {
                if (!empty($item->bundle) and $item->bundle->checkUserHasBought($user)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getInstallmentOrder()
    {
        $user = auth()->user();

        if (!empty($user)) {
            return InstallmentOrder::query()->where('user_id', $user->id)
                ->where('webinar_id', $this->id)
                ->where('status', 'open')
                ->whereNull('refund_at')
                ->first();
        }

        return null;
    }

    public function getFilesLearningProgressStat($userId = null)
    {
        $passed = 0;

        if (empty($userId)) {
            $userId = auth()->id();
        }

        $files = $this->files()
            ->where('status', 'active')
            ->get();

        foreach ($files as $file) {
            $status = CourseLearning::where('user_id', $userId)
                ->where('file_id', $file->id)
                ->first();

            if (!empty($status)) {
                $passed += 1;
            }
        }

        return [
            'passed' => $passed,
            'count' => count($files)
        ];
    }

    public function getSessionsLearningProgressStat($userId = null)
    {
        $passed = 0;

        if (empty($userId)) {
            $userId = auth()->id();
        }

        $sessions = $this->sessions()
            ->where('status', 'active')
            ->get();

        foreach ($sessions as $session) {
            $status = CourseLearning::where('user_id', $userId)
                ->where('session_id', $session->id)
                ->first();

            if (!empty($status)) {
                $passed += 1;
            }
        }

        return [
            'passed' => $passed,
            'count' => count($sessions)
        ];
    }

    public function getTextLessonsLearningProgressStat($userId = null)
    {
        $passed = 0;

        if (empty($userId)) {
            $userId = auth()->id();
        }

        $textLessons = $this->textLessons()
            ->where('status', 'active')
            ->get();

        foreach ($textLessons as $textLesson) {
            $status = CourseLearning::where('user_id', $userId)
                ->where('text_lesson_id', $textLesson->id)
                ->first();

            if (!empty($status)) {
                $passed += 1;
            }
        }

        return [
            'passed' => $passed,
            'count' => count($textLessons)
        ];
    }

    public function getAssignmentsLearningProgressStat($userId = null)
    {
        $passed = 0;

        if (empty($userId)) {
            $userId = auth()->id();
        }

        $assignments = $this->assignments()
            ->where('status', 'active')
            ->get();

        foreach ($assignments as $assignment) {
            $assignmentHistory = WebinarAssignmentHistory::where('assignment_id', $assignment->id)
                ->where('student_id', $userId)
                ->where('status', WebinarAssignmentHistory::$passed)
                ->first();

            if (!empty($assignmentHistory)) {
                $passed += 1;
            }
        }

        return [
            'passed' => $passed,
            'count' => count($assignments)
        ];
    }

    public function getQuizzesLearningProgressStat($userId = null)
    {
        $passed = 0;

        if (empty($userId)) {
            $userId = auth()->id();
        }

        $quizzes = $this->quizzes()
            ->where('status', 'active')
            ->get();

        foreach ($quizzes as $quiz) {
            $quizHistory = QuizzesResult::where('quiz_id', $quiz->id)
                ->where('user_id', $userId)
                ->where('status', QuizzesResult::$passed)
                ->first();

            if (!empty($quizHistory)) {
                $passed += 1;
            }
        }

        return [
            'passed' => $passed,
            'count' => count($quizzes)
        ];
    }

    public function getProgress($isLearningPage = false)
    {
        $progress = 0;

        if (
            auth()->check() and
            $this->checkUserHasBought() and
            (
                !$this->isWebinar() or
                ($this->isWebinar() and $this->isProgressing()) or
                $isLearningPage
            )
        ) {
            $user_id = auth()->id();

            $filesStat = $this->getFilesLearningProgressStat($user_id);
            $sessionsStat = $this->getSessionsLearningProgressStat($user_id);
            $textLessonsStat = $this->getTextLessonsLearningProgressStat($user_id);
            $assignmentsStat = $this->getAssignmentsLearningProgressStat($user_id);
            $quizzesStat = $this->getQuizzesLearningProgressStat($user_id);

            $passed = $filesStat['passed'] + $sessionsStat['passed'] + $textLessonsStat['passed'] + $assignmentsStat['passed'] + $quizzesStat['passed'];
            $count = $filesStat['count'] + $sessionsStat['count'] + $textLessonsStat['count'] + $assignmentsStat['count'] + $quizzesStat['count'];

            if ($passed > 0 and $count > 0) {
                $progress = ($passed * 100) / $count;

                $this->handleLearningProgress100Reward($progress, $user_id, $this->id);
            }
        } else if (!is_null($this->capacity)) {
            $salesCount = !empty($this->sales_count) ? $this->sales_count : $this->sales()->count();

            if ($salesCount > 0) {
                $progress = ($salesCount * 100) / $this->capacity;
            }
        }

        return round($progress, 2);
    }

    public function checkShowProgress($isLearningPage = false)
    {
        $show = false;

        if (
            auth()->check() and
            $this->checkUserHasBought() and
            (
                !$this->isWebinar() or
                ($this->isWebinar() and $this->isProgressing()) or
                $isLearningPage
            )
        ) {
            $show = true;
        } else if (!is_null($this->capacity)) {
            $show = true;
        }

        return $show;
    }

    public function handleLearningProgress100Reward($progress, $userId, $itemId)
    {
        if ($progress >= 100) {
            $rewardScore = RewardAccounting::calculateScore(Reward::LEARNING_PROGRESS_100);
            RewardAccounting::makeRewardAccounting($userId, $rewardScore, Reward::LEARNING_PROGRESS_100, $itemId, true);
        }
    }

    public function getImageCover()
    {
        return $this->image_cover;
    }

    public function getImage()
    {
        return $this->thumbnail;
    }

    public function getUrl()
    {
        $slug = strtolower($this->slug);

        $baseUrl = config('app.manual_base_url');
        // $baseUrl = env('APP_URL');

        return $baseUrl . '/course/' . $slug;
    }
        public function getUrldownloadpdf()
        {
            $slug = strtolower($this->slug);

            $baseUrl = env('APP_URL');

            return $baseUrl . '/course/' . $slug;
        }

    public function getLearningPageUrl()
    {
        return url('/course/learning/' . $this->slug);
    }

    public function getLearningPageUrl1()
    {
        $baseUrl = config('app.manual_base_url');

        return $baseUrl . '/course/learning/' . $this->slug;
    }

    public function getNoticeboardsPageUrl()
    {
        return $this->getLearningPageUrl() . '/noticeboards';
    }

    public function getForumPageUrl()
    {
        return $this->getLearningPageUrl() . '/forum';
    }

    public function isCourse()
    {
        return ($this->type == 'course');
    }

    public function isTextCourse()
    {
        return ($this->type == 'text_lesson');
    }

    public function isWebinar()
    {
        return ($this->type == 'webinar');
    }

    public function canAccess($user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!empty($user)) {
            return ($this->creator_id == $user->id or $this->teacher_id == $user->id);
        }

        return false;
    }

    public function canSale()
    {
        $result = true;

        if (!is_null($this->capacity)) {
            $salesCount = !empty($this->sales_count) ? $this->sales_count : $this->sales()->count();

            $result = $salesCount < $this->capacity;
        }

        if ($result and $this->type == 'webinar') {
            $result = ($this->start_date > time());
        }

        return $result;
    }

    public function canJoinToWaitlist()
    {
        $hasBought = $this->checkUserHasBought();

        return ($this->enable_waitlist and !$hasBought and !$this->canSale());
    }

    public function cantSaleStatus($hasBought)
    {
        $status = '';

        if ($hasBought) {
            $status = 'js-course-has-bought-status';
        } else {

            if (!is_null($this->capacity)) {
                $salesCount = !empty($this->sales_count) ? $this->sales_count : $this->sales()->count();

                if ($salesCount >= $this->capacity) {
                    $status = 'js-course-not-capacity-status';
                }
            } elseif ($this->type == 'webinar' and $this->start_date <= time()) {
                $status = 'js-course-has-started-status';
            }
        }

        return $status;
    }

    public function addToCalendarLink()
    {

        $date = \DateTime::createFromFormat('j M Y H:i', dateTimeFormat($this->start_date, 'j M Y H:i', false));

        $link = Link::create($this->title, $date, $date);

        return $link->google();
    }

    public function activeSpecialOffer()
    {
        $activeSpecialOffer = SpecialOffer::where('webinar_id', $this->id)
            ->where('status', SpecialOffer::$active)
            ->where('from_date', '<', time())
            ->where('to_date', '>', time())
            ->first();

        return $activeSpecialOffer ?? false;
    }

    public function nextSession()
    {
        $sessions = $this->sessions()
            ->orderBy('date', 'asc')
            ->get();
        $time = time();

        foreach ($sessions as $session) {
            if ($session->date > $time) {
                return $session;
            }
        }

        return null;
    }

    public function lastSession()
    {
        $session = $this->sessions()
            ->orderBy('date', 'desc')
            ->first();

        return $session;
    }

    public function isProgressing()
    {
        $lastSession = $this->lastSession();

        $isProgressing = false;

        if ($this->start_date <= time() or (!empty($lastSession) and $lastSession->date > time())) {
            $isProgressing = true;
        }

        return $isProgressing;
    }

    public function getShareLink($social)
    {
        $link = ShareFacade::page($this->getUrl(), $this->title)
            ->facebook()
            ->twitter()
            ->whatsapp()
            ->telegram()
            ->getRawLinks();

        return !empty($link[$social]) ? $link[$social] : '';
    }

    public function isDownloadable()
    {
        $downloadable = $this->downloadable;

        if ($this->files->isNotEmpty()) {
            $downloadableFiles = $this->files->where('downloadable', true)->count();

            if ($downloadableFiles > 0) {
                $downloadable = true;
            }
        }

        return $downloadable;
    }

    public function isOwner($userId = null)
    {
        if (empty($userId)) {
            $userId = auth()->id();
        }

        return (($this->creator_id == $userId) or ($this->teacher_id == $userId));
    }

    public function isPartnerTeacher($userId = null)
    {
        if (empty($userId)) {
            $userId = auth()->id();
        }

        $partnerTeachers = !empty($this->webinarPartnerTeacher) ? $this->webinarPartnerTeacher->pluck('teacher_id')->toArray() : [];

        return in_array($userId, $partnerTeachers);
    }

    public function getPrice()
    {
        $price = $this->price;

        $specialOffer = $this->activeSpecialOffer();
        if (!empty($specialOffer)) {
            $price = $price - ($price * $specialOffer->percent / 100);
        }

        return $price;
    }

    public function getStudentsIds()
    {
        $studentsIds = Sale::query()->where('webinar_id', $this->id)
            ->whereNull('refund_at')
            ->whereHas('buyer')
            ->pluck('buyer_id')
            ->toArray();

        $installmentOrders = InstallmentOrder::query()
            ->where('webinar_id', $this->id)
            ->where('status', 'open')
            ->whereNull('refund_at')
            ->get();

        foreach ($installmentOrders as $installmentOrder) {
            if (!empty($installmentOrder)) {
                $hasBought = true;

                if ($installmentOrder->checkOrderHasOverdue()) {
                    $overdueIntervalDays = getInstallmentsSettings('overdue_interval_days');

                    if (empty($overdueIntervalDays) or $installmentOrder->overdueDaysPast() > $overdueIntervalDays) {
                        $hasBought = false;
                    }
                }

                if ($hasBought) {
                    $studentsIds[] = $installmentOrder->user_id;
                }
            }
        }

        $gifts = Gift::query()
            ->where('status', 'active')
            ->where('webinar_id', $this->id)
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->get();

        foreach ($gifts as $gift) {
            $user = User::query()->select('id', 'email')->where('email', $gift->email)->first();

            if (!empty($user)) {
                $studentsIds[] = $user->id;
            }
        }

        $bundleWebinar = BundleWebinar::where('webinar_id', $this->id)
            ->with([
                'bundle'
            ])->get();

        if ($bundleWebinar->isNotEmpty()) {
            foreach ($bundleWebinar as $item) {
                if (!empty($item->bundle)) {
                    $bundleStudents = $item->bundle->getStudentsIds();

                    $studentsIds = array_merge($studentsIds, $bundleStudents);
                }
            }
        }

        return array_unique($studentsIds);
    }

    public function sendNotificationToAllStudentsForNewQuizPublished($quiz)
    {
        $studentsIds = $this->getStudentsIds();

        $notifyOptions = [
            '[q.title]' => $quiz->title,
            '[c.title]' => $this->title
        ];

        if (count($studentsIds)) {
            foreach ($studentsIds as $studentId) {
                $students = \App\User::where('id', $studentId)->first();
                 try {
    $vboutService = new VboutService();
        $listId = '143038';

        $contactData = [
            'email' => $students->email,
            'fields' => [
            '941998' => $students->full_name,
             '941997' => $this->title,
              '941996' => $quiz->title,
              '941999' => dateTimeFormat(time(), 'j M Y H:i'),
            ],
        ];
        $result = $vboutService->addContactToList($listId, $contactData);
} catch (\Exception $e) {

}

            }
        }

        $gifts = Gift::query()
            ->where('status', 'active')
            ->where('webinar_id', $this->id)
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->get();

        foreach ($gifts as $gift) {
            $user = User::query()->select('id', 'email')->where('email', $gift->email)->first();

            if (empty($user)) {
                sendNotificationToEmail("new_quiz", $notifyOptions, $gift->email);
            }
        }
    }
    
 public function extraDetails()
{
    return $this->hasOne(WebinarExtraDetails::class, 'webinar_id');
}
}
