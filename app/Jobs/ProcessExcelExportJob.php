<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Gift;
use App\Models\ExportTracker;
use App\Exports\SubscriptionStudentsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\StudentFiltersTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Queueable as BusQueueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;

class ProcessExcelExportJob implements ShouldQueue
{
    use Queueable, StudentFiltersTrait;

    protected $trackerId;
    protected $subscriptionId;
    protected $filters;
    protected $adminId;

    /**
     * Create a new job instance.
     */
    public function __construct($trackerId, $subscriptionId, $filters, $adminId)
    {
        $this->trackerId = $trackerId;
        $this->subscriptionId = $subscriptionId;
        $this->filters = $filters;
        $this->adminId = $adminId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tracker = ExportTracker::find($this->trackerId);
        if (!$tracker) return;

        try {
            $tracker->update(['status' => 'processing']);

            $subscription = Subscription::where('id', $this->subscriptionId)->with(['subscriptionWebinars'])->first();
            if (!$subscription) {
                $tracker->update(['status' => 'failed', 'error_message' => 'Subscription not found']);
                return;
            }

            $giftsIds = Gift::query()->where('subscription_id', $subscription->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('date')->orWhere('date', '<', time());
                })
                ->whereHas('sale')
                ->pluck('id')->toArray();

            $baseQuery = User::join('sales', 'sales.buyer_id', 'users.id')
                ->select('users.*', 'sales.created_at as purchase_date')
                ->where(function ($query) use ($subscription, $giftsIds) {
                    $query->where('sales.subscription_id', $subscription->id);
                    $query->orWhereIn('sales.gift_id', $giftsIds);
                })
                ->whereNull('sales.refund_at');

            $studentsQuery = $this->studentsListsFilters($subscription, $baseQuery, $this->filters)
                ->orderBy('sales.created_at', 'desc');

            $totalRecords = $studentsQuery->count();
            $tracker->update(['total_records' => $totalRecords]);

            if ($totalRecords === 0) {
                $tracker->update(['status' => 'completed', 'percentage' => 100]);
                return;
            }

            $processedData = [];
            $processedCount = 0;
            $subscriptionWebinars = $subscription->subscriptionWebinars;
            $webinarStatisticController = new \App\Http\Controllers\Panel\WebinarStatisticController();

            // Process in chunks of 50
            $studentsQuery->chunk(50, function ($students) use ($subscriptionWebinars, $webinarStatisticController, &$processedData, &$processedCount, $totalRecords, $tracker) {
                foreach ($students as $student) {
                    $learnings = 0;
                    $webinarCount = 0;

                    foreach ($subscriptionWebinars as $subscriptionWebinar) {
                        if (!empty($subscriptionWebinar->webinar)) {
                            $webinarCount += 1;
                            $learnings += $webinarStatisticController->getCourseProgressForStudent($subscriptionWebinar->webinar, $student->id);
                        }
                    }

                    $student->learning = ($learnings > 0 && $webinarCount > 0) ? round($learnings / $webinarCount, 2) : 0;
                    $processedData[] = clone $student;
                    $processedCount++;
                }

                // Update Progress
                $percentage = min(99, round(($processedCount / $totalRecords) * 100));
                $tracker->update([
                    'processed_records' => $processedCount,
                    'percentage' => $percentage
                ]);
            });

            // Generate and Save Excel
            $export = new SubscriptionStudentsExport($processedData);
            $fileName = 'exports/students_' . $this->subscriptionId . '_' . time() . '.xlsx';
            
            if (!\Storage::disk('public')->exists('exports')) {
                \Storage::disk('public')->makeDirectory('exports');
            }

            Excel::store($export, $fileName, 'public');

            $tracker->update([
                'status' => 'completed',
                'percentage' => 100,
                'download_url' => \Storage::disk('public')->url($fileName)
            ]);

        } catch (\Exception $e) {
            \Log::error('ProcessExcelExportJob Error: ' . $e->getMessage());
            $tracker->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
