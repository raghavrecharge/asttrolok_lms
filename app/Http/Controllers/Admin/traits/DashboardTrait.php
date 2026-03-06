<?php

namespace App\Http\Controllers\Admin\traits;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Models\Accounting;
use App\Models\Comment;
use App\Models\Meeting;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Support;
use App\Models\Webinar;
use App\Models\WebinarPartPayment;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeProduct;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Web;

trait DashboardTrait
{
    /**
     * UPE cutover: before this date use legacy tables, after use UPE tables.
     * This is the created_at of the earliest UpeSale record.
     */
    private static $UPE_CUTOVER = '2026-02-14 12:17:11';

    private function getUpeCutover()
    {
        return Carbon::parse(self::$UPE_CUTOVER);
    }

    /**
     * Legacy income from Sale + WebinarPartPayment.
     * Uses timestamps for Sale.created_at (stored as int) and Carbon for WebinarPartPayment.
     */
    private function getLegacyIncomes($from = null, $to = null)
    {
        $saleQuery = Sale::whereNull('product_order_id')->whereNull('status');
        if ($from) {
            $saleQuery->where('created_at', '>=', $from->timestamp);
        }
        if ($to) {
            $saleQuery->where('created_at', '<', $to->timestamp);
        }
        $saleIncome = $saleQuery->sum('total_amount');

        $installQuery = WebinarPartPayment::query();
        if ($from) {
            $installQuery->where('created_at', '>=', $from);
        }
        if ($to) {
            $installQuery->where('created_at', '<', $to);
        }
        $installIncome = $installQuery->sum('amount');

        return max($saleIncome + $installIncome, 0);
    }

    /**
     * UPE income from ledger credit entries.
     */
    private function getUpeIncomes($from = null, $to = null)
    {
        $query = UpeLedgerEntry::where('direction', UpeLedgerEntry::DIR_CREDIT)
            ->whereIn('entry_type', [
                UpeLedgerEntry::TYPE_PAYMENT,
                UpeLedgerEntry::TYPE_INSTALLMENT_PAYMENT,
                UpeLedgerEntry::TYPE_SUBSCRIPTION_CHARGE,
            ]);
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<', $to);
        }
        return max($query->sum('amount'), 0);
    }

    /**
     * Legacy sales count from Sale table.
     */
    private function getLegacySalesCount($from = null, $to = null)
    {
        $query = Sale::whereNull('refund_at')->whereNull('status');
        if ($from) {
            $query->where('created_at', '>=', $from->timestamp);
        }
        if ($to) {
            $query->where('created_at', '<', $to->timestamp);
        }
        return $query->count();
    }

    /**
     * UPE sales count from upe_sales table.
     */
    private function getUpeSalesCount($from = null, $to = null)
    {
        $query = UpeSale::whereNotIn('status', ['refunded', 'cancelled']);
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<', $to);
        }
        return $query->count();
    }

    /**
     * Hybrid sales count: legacy before cutover + UPE after cutover.
     */
    private function getHybridSalesCount($from = null, $to = null)
    {
        $cutover = $this->getUpeCutover();

        if ($from && $to) {
            if ($from >= $cutover) {
                return $this->getUpeSalesCount($from, $to);
            } elseif ($to <= $cutover) {
                return $this->getLegacySalesCount($from, $to);
            } else {
                return $this->getLegacySalesCount($from, $cutover) + $this->getUpeSalesCount($cutover, $to);
            }
        }

        return $this->getLegacySalesCount(null, $cutover) + $this->getUpeSalesCount($cutover, null);
    }

    public function dailySalesTypeStatistics()
    {
        try {
            $this->authorize('admin_general_dashboard_daily_sales_statistics');

            $todayStart = Carbon::today();
            $todayEnd = Carbon::tomorrow();

            $baseQuery = UpeSale::whereNotIn('status', ['refunded', 'cancelled'])
                ->whereBetween('created_at', [$todayStart, $todayEnd]);

            $webinarsSales = (clone $baseQuery)
                ->whereHas('product', function ($q) {
                    $q->where('product_type', 'webinar');
                })->count();

            $courseSales = (clone $baseQuery)
                ->whereHas('product', function ($q) {
                    $q->where('product_type', 'course_video');
                })->count();

            $appointmentSales = (clone $baseQuery)
                ->whereHas('product', function ($q) {
                    $q->where('product_type', 'meeting');
                })->count();

            $allSales = (clone $baseQuery)
                ->whereHas('product', function ($q) {
                    $q->whereIn('product_type', ['webinar', 'course_video', 'meeting', 'bundle', 'subscription']);
                })->count();

            return [
                'webinarsSales' => $webinarsSales,
                'courseSales' => $courseSales,
                'appointmentSales' => $appointmentSales,
                'allSales' => $allSales,
            ];
        } catch (\Exception $e) {
            \Log::error('dailySalesTypeStatistics error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getIncomeStatistics()
    {
        try {
            $this->authorize('admin_general_dashboard_income_statistics');

            $now = Carbon::now();

            $totalSales = $this->getIncomes();

            $todaySales = $this->getIncomes(Carbon::today(), Carbon::tomorrow());

            $monthSales = $this->getIncomes($now->copy()->startOfMonth(), $now->copy()->endOfMonth());

            $yearSales = $this->getIncomes($now->copy()->startOfYear(), $now->copy()->endOfYear());

            return [
                'totalSales' => $totalSales,
                'todaySales' => $todaySales,
                'monthSales' => $monthSales,
                'yearSales' => $yearSales,
            ];
        } catch (\Exception $e) {
            \Log::error('getIncomeStatistics error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Hybrid income: legacy before cutover + UPE after cutover.
     */
    private function getIncomes($from = null, $to = null)
    {
        $cutover = $this->getUpeCutover();

        if ($from && $to) {
            if ($from >= $cutover) {
                return $this->getUpeIncomes($from, $to);
            } elseif ($to <= $cutover) {
                return $this->getLegacyIncomes($from, $to);
            } else {
                return $this->getLegacyIncomes($from, $cutover) + $this->getUpeIncomes($cutover, $to);
            }
        }

        // All-time: legacy before cutover + UPE after cutover
        return $this->getLegacyIncomes(null, $cutover) + $this->getUpeIncomes($cutover, null);
    }

    private function getAllDateStartAndEnd()
    {
        $time = time();
        $beginOfDay = strtotime("today", $time);
        $endOfDay = strtotime("tomorrow", $beginOfDay) - 1;

        $monday = strtotime('next Monday -1 week');
        $beginOfWeek = date('w', $monday) == date('w') ? strtotime(date("Y-m-d", $monday) . " +7 days") : $monday;
        $endOfWeek = strtotime(date("Y-m-d", $beginOfWeek) . " +7 days") - 1;

        $beginOfMonth = strtotime(date('Y-m-01', $time));
        $endOfMonth = strtotime(date('Y-m-t', $time));

        $beginOfYear = strtotime(date('Y-01-01', $time));
        $endOfYear = strtotime(date('Y-m-d', strtotime('12/31')));

        return [
            'today' => [
                'start' => $beginOfDay,
                'end' => $endOfDay,
            ],
            'week' => [
                'start' => $beginOfWeek,
                'end' => $endOfWeek,
            ],
            'month' => [
                'start' => $beginOfMonth,
                'end' => $endOfMonth,
            ],
            'year' => [
                'start' => $beginOfYear,
                'end' => $endOfYear,
            ],
        ];
    }

    public function getTotalSalesStatistics()
    {
        try {
            $now = Carbon::now();

            $totalSales = $this->getHybridSalesCount();
            $todaySales = $this->getHybridSalesCount(Carbon::today(), Carbon::tomorrow());
            $monthSales = $this->getHybridSalesCount($now->copy()->startOfMonth(), $now->copy()->endOfMonth());
            $yearSales = $this->getHybridSalesCount($now->copy()->startOfYear(), $now->copy()->endOfYear());

            return [
                'totalSales' => $totalSales,
                'todaySales' => $todaySales,
                'monthSales' => $monthSales,
                'yearSales' => $yearSales,
            ];
        } catch (\Exception $e) {
            \Log::error('getTotalSalesStatistics error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getNewSalesCount()
    {
        try {
            $this->authorize('admin_general_dashboard_new_sales');

            return Sale::whereNull('refund_at')
                ->whereDoesntHave('saleLog')
                ->count();
        } catch (\Exception $e) {
            \Log::error('getNewSalesCount error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getNewCommentsCount()
    {
        try {
            $this->authorize('admin_general_dashboard_new_comments');

            return Comment::where('status', 'pending')
                ->count();
        } catch (\Exception $e) {
            \Log::error('getNewCommentsCount error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getNewTicketsCount()
    {
        try {
            $this->authorize('admin_general_dashboard_new_tickets');

            return Support::whereNotNull('department_id')
                ->where('status', 'replied')
                ->count();
        } catch (\Exception $e) {
            \Log::error('getNewTicketsCount error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getPendingReviewCount()
    {
        try {
            $this->authorize('admin_general_dashboard_new_reviews');

            return Webinar::where('status', 'pending')
                ->count();
        } catch (\Exception $e) {
            \Log::error('getPendingReviewCount error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getMonthAndYearSalesChart($type = 'month_of_year')
    {
        try {
            $labels = [];
            $data = [];

            if ($type == 'day_of_month') {

                for ($day = 1; $day <= 31; $day++) {
                    $startDay = Carbon::create(date('Y'), date('m'), $day)->startOfDay();
                    $endDay = $startDay->copy()->endOfDay();

                    $labels[] = str_pad($day, 2, 0, STR_PAD_LEFT);

                    $amount = $this->getIncomes($startDay, $endDay);

                    $data[] = round($amount, 2);
                }

            } elseif ($type == 'month_of_year') {
                for ($month = 1; $month <= 12; $month++) {
                    $date = Carbon::create(date('Y'), $month);

                    $start_date = $date->copy()->startOfMonth();
                    $end_date = $date->copy()->endOfMonth();

                    $labels[] = trans('panel.month_' . $month);

                    $amount = $this->getIncomes($start_date, $end_date);

                    $data[] = round($amount, 2);
                }
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (\Exception $e) {
            \Log::error('getMonthAndYearSalesChart error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getMonthAndYearSalesChartStatistics()
    {
        try {
            $now = Carbon::now();

            $beginOfDay = Carbon::today();
            $endOfDay = Carbon::tomorrow();

            $beginOfWeek = $now->copy()->startOfWeek();
            $endOfWeek = $now->copy()->endOfWeek();

            $beginOfMonth = $now->copy()->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();

            $beginOfYear = $now->copy()->startOfYear();
            $endOfYear = $now->copy()->endOfYear();

            $lastDayStart = $beginOfDay->copy()->subDay();
            $lastDayEnd = $beginOfDay->copy();

            $lastWeekStart = $beginOfWeek->copy()->subWeek();
            $lastWeekEnd = $endOfWeek->copy()->subWeek();

            $lastMonthStart = $beginOfMonth->copy()->subMonth();
            $lastMonthEnd = $endOfMonth->copy()->subMonth();

            $lastYearStart = $beginOfYear->copy()->subYear();
            $lastYearEnd = $endOfYear->copy()->subYear();

            $todaySales = $this->getLedgerCreditsSum($beginOfDay, $endOfDay);
            $lastDaySales = $this->getLedgerCreditsSum($lastDayStart, $lastDayEnd);

            $weekSales = $this->getLedgerCreditsSum($beginOfWeek, $endOfWeek);
            $lastWeekSales = $this->getLedgerCreditsSum($lastWeekStart, $lastWeekEnd);

            $monthSales = $this->getLedgerCreditsSum($beginOfMonth, $endOfMonth);
            $lastMonthSales = $this->getLedgerCreditsSum($lastMonthStart, $lastMonthEnd);

            $yearSales = $this->getLedgerCreditsSum($beginOfYear, $endOfYear);
            $lastYearSales = $this->getLedgerCreditsSum($lastYearStart, $lastYearEnd);

            return [
                'todaySales' => [
                    'amount' => $todaySales,
                    'grow_percent' => $this->getGrowPercent($lastDaySales, $todaySales),
                ],
                'weekSales' => [
                    'amount' => $weekSales,
                    'grow_percent' => $this->getGrowPercent($lastWeekSales, $weekSales),
                ],
                'monthSales' => [
                    'amount' => $monthSales,
                    'grow_percent' => $this->getGrowPercent($lastMonthSales, $monthSales),
                ],
                'yearSales' => [
                    'amount' => $yearSales,
                    'grow_percent' => $this->getGrowPercent($lastYearSales, $yearSales),
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('getMonthAndYearSalesChartStatistics error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function getLedgerCreditsSum($from, $to)
    {
        return $this->getIncomes($from, $to);
    }

    private function getGrowPercent($last, $new)
    {
        $percent = 'No previous value';
        $status = 'up';

        if ($last != 0) {
            $tmp = ($new - $last);
            $abs = abs($last);

            $res = ($tmp / $abs * 100);

            $percent = round($res, 3) . '%';
            $status = $res > 0 ? 'up' : 'down';
        }

        return [
            'percent' => $percent,
            'status' => $status
        ];
    }

    public function getInstallmentHealth()
    {
        try {
            $overdueSchedules = \App\Models\PaymentEngine\UpeInstallmentSchedule::where('status', 'overdue')->count();

            $overdueAmount = \App\Models\PaymentEngine\UpeInstallmentSchedule::where('status', 'overdue')
                ->sum('amount_due');

            $upcomingDue = \App\Models\PaymentEngine\UpeInstallmentSchedule::whereIn('status', ['due', 'upcoming'])
                ->where('due_date', '<=', Carbon::now()->addDays(7))
                ->count();

            $activePlans = \App\Models\PaymentEngine\UpeInstallmentPlan::where('status', 'active')->count();

            $completedPlans = \App\Models\PaymentEngine\UpeInstallmentPlan::where('status', 'completed')->count();

            return [
                'overdueCount' => $overdueSchedules,
                'overdueAmount' => $overdueAmount,
                'upcomingDueCount' => $upcomingDue,
                'activePlans' => $activePlans,
                'completedPlans' => $completedPlans,
            ];
        } catch (\Exception $e) {
            \Log::error('getInstallmentHealth error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'overdueCount' => 0,
                'overdueAmount' => 0,
                'upcomingDueCount' => 0,
                'activePlans' => 0,
                'completedPlans' => 0,
            ];
        }
    }

    public function getRecentSales($limit = 5)
    {
        try {
            return UpeSale::with(['user', 'product'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        } catch (\Exception $e) {
            \Log::error('getRecentSales error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return collect();
        }
    }

    public function getRecentComments()
    {
        try {
            $this->authorize('admin_general_dashboard_recent_comments');

            return Comment::orderBy('created_at', 'desc')->limit(6)->get();
        } catch (\Exception $e) {
            \Log::error('getRecentComments error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getRecentTickets()
    {
        try {
            $this->authorize('admin_general_dashboard_recent_tickets');

            $tickets = Support::whereNotNull('department_id')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $pendingReply = Support::whereNotNull('department_id')
                ->whereIn('status', ['open', 'replied'])
                ->count();

            return [
                'tickets' => $tickets,
                'pendingReply' => $pendingReply,
            ];
        } catch (\Exception $e) {
            \Log::error('getRecentTickets error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getRecentWebinars()
    {
        try {
            $this->authorize('admin_general_dashboard_recent_webinars');

            $webinars = Webinar::where('type', Webinar::$webinar)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $pendingReviews = Webinar::where('type', Webinar::$webinar)
                ->where('status', 'pending')
                ->count();

            return [
                'webinars' => $webinars,
                'pendingReviews' => $pendingReviews,
            ];
        } catch (\Exception $e) {
            \Log::error('getRecentWebinars error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getRecentCourses()
    {
        try {
            $this->authorize('admin_general_dashboard_recent_courses');

            $courses = Webinar::where('type', Webinar::$course)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $pendingReviews = Webinar::where('type', Webinar::$course)
                ->where('status', 'pending')
                ->count();

            return [
                'courses' => $courses,
                'pendingReviews' => $pendingReviews,
            ];
        } catch (\Exception $e) {
            \Log::error('getRecentCourses error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function usersStatisticsChart()
    {
        try {
            $labels = [];
            $data = [];

            for ($day = 1; $day <= 31; $day++) {
                $startDay = strtotime(date('Y-m-' . $day));
                $endDay = strtotime('-1 second', strtotime('+1 day', $startDay));

                $labels[] = str_pad($day, 2, 0, STR_PAD_LEFT);

                $data[] = User::whereBetween('created_at', [$startDay, $endDay])
                    ->count();
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (\Exception $e) {
            \Log::error('usersStatisticsChart error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getClassesStatistics()
    {
        try {
            $labels = [Webinar::$webinar, Webinar::$course, Webinar::$textLesson];
            $data = [];

            $query = Webinar::where('status', Webinar::$active);
            $allClasses = $query->count();

            foreach ($labels as $label) {
                $count = deepClone($query)->where('type', $label)->count();
                $percent = !empty($allClasses) ? ($count * 100) / $allClasses : 0;
                $data[] = round($percent, 2);
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (\Exception $e) {
            \Log::error('getClassesStatistics error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getNetProfitChart($type = 'month_of_year')
    {
        try {
            $labels = [];
            $data = [];

            $query = Accounting::where('system', 1)
                ->where('tax', 0);

            if ($type == 'day_of_month') {

                for ($day = 1; $day <= 31; $day++) {
                    $startDay = strtotime(date('Y-m-' . $day));
                    $endDay = strtotime('-1 second', strtotime('+1 day', $startDay));

                    $labels[] = str_pad($day, 2, 0, STR_PAD_LEFT);

                    $amount = $this->computingAccounting(deepClone($query), $startDay, $endDay);
                    $data[] = round($amount, 2);
                }
            } elseif ($type == 'month_of_year') {
                for ($month = 1; $month <= 12; $month++) {
                    $date = Carbon::create(date('Y'), $month);

                    $start_date = $date->timestamp;
                    $end_date = $date->copy()->endOfMonth()->timestamp;

                    $labels[] = trans('panel.month_' . $month);

                    $amount = $this->computingAccounting(deepClone($query), $start_date, $end_date);
                    $data[] = round($amount, 2);
                }
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (\Exception $e) {
            \Log::error('getNetProfitChart error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function computingAccounting($query, $start, $end)
    {
        $additions = deepClone($query)->whereBetween('created_at', [$start, $end])
            ->where('type', Accounting::$addiction)
            ->sum('amount');

        $deductions = deepClone($query)->whereBetween('created_at', [$start, $end])
            ->where('type', Accounting::$deduction)
            ->sum('amount');

        $charge = $additions - $deductions;
        return $charge > 0 ? $charge : 0;
    }

    public function getNetProfitStatistics()
    {
        try {
            $dateStartAndEnd = $this->getAllDateStartAndEnd();

            $beginOfDay = $dateStartAndEnd['today']['start'];
            $endOfDay = $dateStartAndEnd['today']['end'];

            $beginOfWeek = $dateStartAndEnd['week']['start'];
            $endOfWeek = $dateStartAndEnd['week']['end'];

            $beginOfMonth = $dateStartAndEnd['month']['start'];
            $endOfMonth = $dateStartAndEnd['month']['end'];

            $beginOfYear = $dateStartAndEnd['year']['start'];
            $endOfYear = $dateStartAndEnd['year']['end'];

            $lastDayStart = $beginOfDay - 24 * 60 * 60;
            $lastDayEnd = $endOfDay - 24 * 60 * 60;

            $lastWeekStart = $beginOfWeek - 7 * 24 * 60 * 60;
            $lastWeekEnd = $endOfWeek - 7 * 24 * 60 * 60;

            $lastMonthStart = $beginOfMonth - 30 * 24 * 60 * 60;
            $lastMonthEnd = $endOfMonth - 30 * 24 * 60 * 60;

            $lastYearStart = $beginOfYear - 365 * 24 * 60 * 60;
            $lastYearEnd = $endOfYear - 365 * 24 * 60 * 60;

            $query = Accounting::where('system', 1)
                ->where('tax', 0);

            $todayIncome = $this->computingAccounting(deepClone($query), $beginOfDay, $endOfDay);

            $lastDayIncome = $this->computingAccounting(deepClone($query), $lastDayStart, $lastDayEnd);

            $weekIncome = $this->computingAccounting(deepClone($query), $beginOfWeek, $endOfWeek);

            $lastWeekIncome = $this->computingAccounting(deepClone($query), $lastWeekStart, $lastWeekEnd);

            $monthIncome = $this->computingAccounting(deepClone($query), $beginOfMonth, $endOfMonth);

            $lastMonthIncome = $this->computingAccounting(deepClone($query), $lastMonthStart, $lastMonthEnd);

            $yearIncome = $this->computingAccounting(deepClone($query), $beginOfYear, $endOfYear);

            $lastYearIncome = $this->computingAccounting(deepClone($query), $lastYearStart, $lastYearEnd);

            return [
                'todayIncome' => [
                    'amount' => $todayIncome,
                    'grow_percent' => $this->getGrowPercent($lastDayIncome, $todayIncome),
                ],
                'weekIncome' => [
                    'amount' => $weekIncome,
                    'grow_percent' => $this->getGrowPercent($lastWeekIncome, $weekIncome),
                ],
                'monthIncome' => [
                    'amount' => $monthIncome,
                    'grow_percent' => $this->getGrowPercent($lastMonthIncome, $monthIncome),
                ],
                'yearIncome' => [
                    'amount' => $yearIncome,
                    'grow_percent' => $this->getGrowPercent($lastYearIncome, $yearIncome),
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('getNetProfitStatistics error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getTopSellingClasses()
    {
        try {
            $webinars = Webinar::where('webinars.status', Webinar::$active)
            ->join('sales', 'webinars.id', '=', 'sales.webinar_id')
            ->select(
                'webinars.*',
                'sales.webinar_id',
                DB::raw('count(sales.webinar_id) as sales_count'),
                DB::raw('sum(sales.total_amount) as sales_amount')
            )
            ->whereNull('sales.refund_at')
            ->whereNull('sales.status')
            ->where('sales.amount', '>', 0)
            ->whereNotIn('webinars.id', [2071, 2064])
            ->groupBy('sales.webinar_id')
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get();
            $webinarPartTotal=[];
            $webinarInstallmentTotal=[];
            foreach($webinars as $webinar){
                $webinarPart = WebinarPartPayment::query()
                    ->where('webinar_id', $webinar->id)
                    ->sum('amount');
                $webinarPartcount = WebinarPartPayment::query()
                    ->where('webinar_id', $webinar->id)
                    ->count();

                $webinarInstallmentsum = Sale::whereNull('sales.refund_at')
                    ->whereNull('sales.status')
                    ->whereNotNull('sales.installment_payment_id')
                    ->join('installment_order_payments', 'sales.installment_payment_id', '=', 'installment_order_payments.id')
                    ->join('installment_orders', 'installment_order_payments.installment_order_id', '=', 'installment_orders.id')
                    ->where('installment_orders.webinar_id', $webinar->id)
                    ->sum('sales.total_amount');

                $webinarInstallmentcount = Sale::whereNull('sales.refund_at')
                    ->whereNull('sales.status')
                    ->whereNotNull('sales.installment_payment_id')
                    ->join('installment_order_payments', 'sales.installment_payment_id', '=', 'installment_order_payments.id')
                    ->join('installment_orders', 'installment_order_payments.installment_order_id', '=', 'installment_orders.id')
                    ->where('installment_orders.webinar_id', $webinar->id)
                    ->count();

                $webinarPartTotal[$webinar->id]=[
                    'amount' => $webinarPart,
                    'count' => $webinarPartcount,
                ];
                $webinarInstallmentTotal[$webinar->id]=[
                    'amount' => $webinarInstallmentsum,
                    'count' => $webinarInstallmentcount,
                ];
            }

            return $data =[
                    'webinars' => $webinars,
                    'webinarPartTotal' => $webinarPartTotal,
                    'webinarInstallmentTotal' => $webinarInstallmentTotal,
                ];
        } catch (\Exception $e) {
            \Log::error('getTopSellingClasses error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getTopSellingAppointments()
    {
        try {
            return Meeting::where('meetings.disabled', false)
                ->join('sales', 'meetings.id', '=', 'sales.meeting_id')
                ->select('meetings.*', 'sales.meeting_id',
                    DB::raw('count(sales.meeting_id) as sales_count'),
                    DB::raw('sum(sales.total_amount) as sales_amount')
                )->whereNull('sales.refund_at')
                ->where('sales.amount', '>', '0')
                ->whereNotIn('sales.seller_id', [1398])
                ->groupBy('sales.meeting_id')
                ->orderBy('sales_count', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            \Log::error('getTopSellingAppointments error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getTopSellingTeachersAndOrganizations($role = 'teachers')
    {
        try {
            $users = User::where('users.status', 'active')
                ->join('sales', 'users.id', '=', 'sales.seller_id')
                ->select('users.*', 'sales.seller_id',
                    DB::raw('count(sales.seller_id) as sales_count'),
                    DB::raw('sum(sales.total_amount) as sales_amount')
                )->whereNull('sales.refund_at')
                ->where('sales.amount', '>', '0')
                ->whereNull('sales.status')
                ->where('users.role_name', (($role == 'teachers') ? Role::$teacher : Role::$organization))
                ->groupBy('sales.seller_id')
                ->orderBy('sales_count', 'desc')
                ->limit(7)
                ->get();

            $usersdata=[];

            foreach ($users as $user) {
                $duration = Webinar::where('status', Webinar::$active)
                    ->where(function ($query) use ($user) {
                        $query->where('creator_id', $user->id)
                            ->orWhere('teacher_id', $user->id);
                    })->sum('duration');

                $user->classes_durations = $duration;

                $webinars = Webinar::where('status', Webinar::$active)
                    ->where(function ($query) use ($user) {
                        $query->where('teacher_id', $user->id);
                    })->get();

                if($webinars){

                    $Totalamount=0;
                    $Totalcount=0;

                    foreach($webinars as $webinar){

                        $webinarPart = WebinarPartPayment::query()
                            ->where('webinar_id', $webinar->id)
                            ->sum('amount');

                        $webinarPartcount = WebinarPartPayment::query()
                            ->where('webinar_id', $webinar->id)
                            ->count();

                        $webinarInstallmentsum = Sale::whereNull('sales.refund_at')
                            ->whereNull('sales.status')
                            ->whereNotNull('sales.installment_payment_id')
                            ->join('installment_order_payments', 'sales.installment_payment_id', '=', 'installment_order_payments.id')
                            ->join('installment_orders', 'installment_order_payments.installment_order_id', '=', 'installment_orders.id')
                            ->where('installment_orders.webinar_id', $webinar->id)
                            ->sum('sales.total_amount');

                        $webinarInstallmentcount = Sale::whereNull('sales.refund_at')
                            ->whereNull('sales.status')
                            ->whereNotNull('sales.installment_payment_id')
                            ->join('installment_order_payments', 'sales.installment_payment_id', '=', 'installment_order_payments.id')
                            ->join('installment_orders', 'installment_order_payments.installment_order_id', '=', 'installment_orders.id')
                            ->where('installment_orders.webinar_id', $webinar->id)
                            ->count();

                        $Totalamount+=($webinarPart+$webinarInstallmentsum);
                        $Totalcount+=($webinarPartcount+$webinarInstallmentcount);
                    }

                    $usersdata[$user->id]=[
                        'amount' => $Totalamount,
                        'count' => $Totalcount,
                    ];

                }else{
                    $usersdata[$user->id]=[
                'amount' => 0,
                'count' => 0,
            ];
                }
            }
            $data=[
                    'users' => $users,
                    'usersdata' => $usersdata,
                ];
            return $data;
        } catch (\Exception $e) {
            \Log::error('getTopSellingTeachersAndOrganizations error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getMostActiveStudents()
    {
        try {
            return User::where('users.status', 'active')
                ->join('sales', 'users.id', '=', 'sales.buyer_id')
                ->select('users.*', 'sales.buyer_id',
                    DB::raw('count(sales.webinar_id) as purchased_classes'),
                    DB::raw('count(sales.meeting_id) as reserved_appointments'),
                    DB::raw('sum(sales.total_amount) as total_cost')
                )->whereNull('sales.refund_at')
                ->where('sales.amount', '>', '0')
                ->where('users.role_name', Role::$user)
                ->whereNotIn('users.id', [1504,4067,4443,1575,1508,2668,4914,5250,2835,5128,4913,5082])
                ->groupBy('sales.buyer_id')
                ->orderBy('purchased_classes', 'desc')
                ->orderBy('reserved_appointments', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            \Log::error('getMostActiveStudents error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
