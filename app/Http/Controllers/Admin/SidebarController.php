<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Comment;
use App\Models\OfflinePayment;
use App\Models\Payout;
use App\Models\Webinar;
use App\Models\WebinarReview;
use Illuminate\Http\Request;

class SidebarController extends Controller
{
    public function getCoursesBeep()
    {
        try {
            $waitingCoursesCount = Webinar::where('type', Webinar::$course)
                ->where('status', Webinar::$pending)
                ->count();

            return ($waitingCoursesCount > 0);
        } catch (\Exception $e) {
            \Log::error('getCoursesBeep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getBundlesBeep()
    {
        try {
            $waitingBundlesCount = Bundle::where('status', Webinar::$pending)
                ->count();

            return ($waitingBundlesCount > 0);
        } catch (\Exception $e) {
            \Log::error('getBundlesBeep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getWebinarsBeep()
    {
        try {
            $waitingWebinarCount = Webinar::where('type', Webinar::$webinar)
                ->where('status', Webinar::$pending)
                ->count();

            return ($waitingWebinarCount > 0);
        } catch (\Exception $e) {
            \Log::error('getWebinarsBeep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getTextLessonsBeep()
    {
        try {
            $waitingTextLessonCount = Webinar::where('type', Webinar::$textLesson)
                ->where('status', Webinar::$pending)
                ->count();

            return ($waitingTextLessonCount > 0);
        } catch (\Exception $e) {
            \Log::error('getTextLessonsBeep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getUpcomingCoursesBeep()
    {
        try {
            $waitingUpcomingCount = Webinar::where('type', Webinar::$upcoming)
                ->where('status', Webinar::$pending)
                ->count();

            return ($waitingUpcomingCount > 0);
        } catch (\Exception $e) {
            \Log::error('getUpcomingCoursesBeep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getReviewsBeep()
    {
        try {
            $count = WebinarReview::where('status', 'pending')
                ->count();

            return ($count > 0);
        } catch (\Exception $e) {
            \Log::error('getReviewsBeep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getClassesCommentsBeep()
    {
        try {
            $count = Comment::whereNotNull('webinar_id')
                ->where('status', 'pending')
                ->count();

            return ($count > 0);
        } catch (\Exception $e) {
            \Log::error('getClassesCommentsBeep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getBundleCommentsBeep()
    {
        try {
            $count = Comment::whereNotNull('bundle_id')
                ->where('status', 'pending')
                ->count();

            return ($count > 0);
        } catch (\Exception $e) {
            \Log::error('getBundleCommentsBeep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getBlogCommentsBeep()
    {
        try {
            $count = Comment::whereNotNull('blog_id')
                ->where('status', 'pending')
                ->count();

            return ($count > 0);
        } catch (\Exception $e) {
            \Log::error('getBlogCommentsBeep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getProductCommentsBeep()
    {
        try {
            $count = Comment::whereNotNull('product_id')
                ->where('status', 'pending')
                ->count();

            return ($count > 0);
        } catch (\Exception $e) {
            \Log::error('getProductCommentsBeep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getPayoutRequestBeep()
    {
        try {
            $count = Payout::where('status', Payout::$waiting)->count();

            return ($count > 0);
        } catch (\Exception $e) {
            \Log::error('getPayoutRequestBeep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getOfflinePaymentsBeep()
    {
        try {
            $count = OfflinePayment::where('status', OfflinePayment::$waiting)->count();

            return ($count > 0);
        } catch (\Exception $e) {
            \Log::error('getOfflinePaymentsBeep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
