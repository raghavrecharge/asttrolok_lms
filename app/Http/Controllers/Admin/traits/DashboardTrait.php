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
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Web;

trait DashboardTrait
{
    public function dailySalesTypeStatistics()
    {
        try {
            $this->authorize('admin_general_dashboard_daily_sales_statistics');

            $beginOfDay = strtotime("today", time());
            $endOfDay = strtotime("tomorrow", $beginOfDay) - 1;

            $webinarsSales = Sale::whereNull('refund_at')->whereNull('status')
                ->where('type', Sale::$webinar)
                ->whereBetween('created_at', [$beginOfDay, $endOfDay])
                ->whereHas('webinar', function ($query) {
                    $query->where('type', Webinar::$webinar);
                })->count();

            $courseSales = Sale::whereNull('refund_at')->whereNull('status')
                ->where('type', Sale::$webinar)
                ->whereBetween('created_at', [$beginOfDay, $endOfDay])
                ->whereHas('webinar', function ($query) {
                    $query->where('type', Webinar::$course);
                })->count();

            $courseSales1 = WebinarPartPayment::query()
                ->selectRaw("*, UNIX_TIMESTAMP(created_at) as created_at")
                ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [$beginOfDay, $endOfDay])
                ->count();
            $courseSales+=$courseSales1;

            $appointmentSales = Sale::whereNull('refund_at')
                ->where('type', Sale::$meeting)
                ->whereBetween('created_at', [$beginOfDay, $endOfDay])
                ->count();

            $allSales = Sale::whereNull('refund_at')->whereNull('status')
                ->whereIn('type', [Sale::$webinar, Sale::$meeting])
                ->whereBetween('created_at', [$beginOfDay, $endOfDay])
                ->count();

            $allSales+=$courseSales1;

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

            $dateStartAndEnd = $this->getAllDateStartAndEnd();

            $beginOfDay = $dateStartAndEnd['today']['start'];
            $endOfDay = $dateStartAndEnd['today']['end'];

            $beginOfMonth = $dateStartAndEnd['month']['start'];
            $endOfMonth = $dateStartAndEnd['month']['end'];

            $beginOfYear = $dateStartAndEnd['year']['start'];
            $endOfYear = $dateStartAndEnd['year']['end'];

            $totalSales = $this->getIncomes();

            $todaySales = $this->getIncomes($beginOfDay, $endOfDay);

            $monthSales = $this->getIncomes($beginOfMonth, $endOfMonth);

            $yearSales = $this->getIncomes($beginOfYear, $endOfYear);

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

    private function getIncomes($from = null, $to = null)
    {
        $querypart2 = WebinarPartPayment::query()
            ->selectRaw("*, UNIX_TIMESTAMP(created_at) as created_at");

        if($from){
        $salespart1 = $querypart2->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [$from, $to]);
        }else{
            $salespart1 = $querypart2;
        }

        $salespart = $salespart1
            ->selectRaw("amount as total_amount")
            ->get();

        $query = Sale::whereNull('product_order_id')->whereNull('status');

        $salesQuery = fromAndToDateFilter($from, $to, $query, 'created_at', false);
        $sales1 = $salesQuery
            ->get();

        $mergedData = $sales1->merge($salespart);

        $mergedCollection = collect($mergedData);

        $sortedCollection = $mergedCollection->sortByDesc('created_at');
        $income = $sortedCollection->sum('total_amount');

        return $income > 0 ? $income : 0;
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
            $dateStartAndEnd = $this->getAllDateStartAndEnd();

            $beginOfDay = $dateStartAndEnd['today']['start'];
            $endOfDay = $dateStartAndEnd['today']['end'];

            $beginOfMonth = $dateStartAndEnd['month']['start'];
            $endOfMonth = $dateStartAndEnd['month']['end'];

            $beginOfYear = $dateStartAndEnd['year']['start'];
            $endOfYear = $dateStartAndEnd['year']['end'];

            $totalSales = Sale::whereNull('refund_at')->whereNull('status')->count();

            $todaySales = Sale::whereNull('refund_at')
                ->whereBetween('created_at', [$beginOfDay, $endOfDay])
                ->count();

            $monthSales = Sale::whereNull('refund_at')
                ->whereBetween('created_at', [$beginOfMonth, $endOfMonth])
                ->count();

            $yearSales = Sale::whereNull('refund_at')
                ->whereBetween('created_at', [$beginOfYear, $endOfYear])
                ->count();

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
                    $startDay = strtotime(date('Y-m-' . $day));
                    $endDay = strtotime('-1 second', strtotime('+1 day', $startDay));

                    $labels[] = str_pad($day, 2, 0, STR_PAD_LEFT);

                    $amount = Sale::whereNull('refund_at')->whereNull('status')
                        ->whereBetween('created_at', [$startDay, $endDay])
                        ->sum('total_amount');

                    $amount1 = WebinarPartPayment::query()
                        ->selectRaw("*, UNIX_TIMESTAMP(created_at) as created_at")
                        ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [$startDay, $endDay])
                        ->sum('amount');
                    $amount+=$amount1;
                    $data[] = round($amount, 2);
                }

            } elseif ($type == 'month_of_year') {
                for ($month = 1; $month <= 12; $month++) {
                    $date = Carbon::create(date('Y'), $month);

                    $start_date = $date->timestamp;
                    $end_date = $date->copy()->endOfMonth()->timestamp;

                    $labels[] = trans('panel.month_' . $month);

                    $amount = Sale::whereNull('refund_at')->whereNull('status')
                        ->whereBetween('created_at', [$start_date, $end_date])
                        ->sum('total_amount');

                    $amount1 = WebinarPartPayment::query()
                        ->selectRaw("*, UNIX_TIMESTAMP(created_at) as created_at")
                        ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [$start_date, $end_date])
                        ->sum('amount');
                    $amount+=$amount1;

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

            $todaySales = Sale::whereNull('refund_at')->whereNull('status')
                ->whereBetween('created_at', [$beginOfDay, $endOfDay])
                ->sum('total_amount');

            $todaySales1 = WebinarPartPayment::query()
                ->selectRaw("*, UNIX_TIMESTAMP(created_at) as created_at")
                ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [$beginOfDay, $endOfDay])
                ->sum('amount');
            $todaySales+=$todaySales1;

            $lastDaySales = Sale::whereNull('refund_at')->whereNull('status')
                ->whereBetween('created_at', [$lastDayStart, $lastDayEnd])
                ->sum('total_amount');

            $lastDaySales1 = WebinarPartPayment::query()
                ->selectRaw("*, UNIX_TIMESTAMP(created_at) as created_at")
                ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [$lastDayStart, $lastDayEnd])
                ->sum('amount');
            $lastDaySales+=$lastDaySales1;

            $weekSales = Sale::whereNull('refund_at')->whereNull('status')
                ->whereBetween('created_at', [$beginOfWeek, $endOfWeek])
                ->sum('total_amount');

            $weekSales1 = WebinarPartPayment::query()
                ->selectRaw("*, UNIX_TIMESTAMP(created_at) as created_at")
                ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [$beginOfWeek, $endOfWeek])
                ->sum('amount');
            $weekSales+=$weekSales1;

            $lastWeekSales = Sale::whereNull('refund_at')->whereNull('status')
                ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
                ->sum('total_amount');

            $lastWeekSales1 = WebinarPartPayment::query()
                ->selectRaw("*, UNIX_TIMESTAMP(created_at) as created_at")
                ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [$lastWeekStart, $lastWeekEnd])
                ->sum('amount');
            $lastWeekSales+=$lastWeekSales1;

            $monthSales = Sale::whereNull('refund_at')->whereNull('status')
                ->whereBetween('created_at', [$beginOfMonth, $endOfMonth])
                ->sum('total_amount');

            $monthSales1 = WebinarPartPayment::query()
                ->selectRaw("*, UNIX_TIMESTAMP(created_at) as created_at")
                ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [$beginOfMonth, $endOfMonth])
                ->sum('amount');
            $monthSales+=$monthSales1;

            $lastMonthSales = Sale::whereNull('refund_at')->whereNull('status')
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->sum('total_amount');

            $lastMonthSales1 = WebinarPartPayment::query()
                ->selectRaw("*, UNIX_TIMESTAMP(created_at) as created_at")
                ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [$lastMonthStart, $lastMonthEnd])
                ->sum('amount');
            $lastMonthSales+=$lastMonthSales1;

            $yearSales = Sale::whereNull('refund_at')->whereNull('status')
                ->whereBetween('created_at', [$beginOfYear, $endOfYear])
                ->sum('total_amount');

            $yearSales1 = WebinarPartPayment::query()
                ->selectRaw("*, UNIX_TIMESTAMP(created_at) as created_at")
                ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [$beginOfYear, $endOfYear])
                ->sum('amount');
            $yearSales+=$yearSales1;

            $lastYearSales = Sale::whereNull('refund_at')->whereNull('status')
                ->whereBetween('created_at', [$lastYearStart, $lastYearEnd])
                ->sum('total_amount');

            $lastYearSales1 = WebinarPartPayment::query()
                ->selectRaw("*, UNIX_TIMESTAMP(created_at) as created_at")
                ->whereBetween(DB::raw('UNIX_TIMESTAMP(created_at)'), [$lastYearStart, $lastYearEnd])
                ->sum('amount');
            $lastYearSales+=$lastYearSales1;

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
