<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\User;
use App\Models\Sale;
use App\Models\Gift;
use App\Models\ExportTracker;
use App\Exports\SubscriptionStudentsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\StudentFiltersTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
        ini_set('memory_limit', '-1');
        
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
                ->select('users.id', 'users.full_name', 'users.mobile', 'users.email', 'users.status', 'sales.created_at as purchase_date')
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

            $processedCount = 0;
            $subscriptionWebinars = $subscription->subscriptionWebinars;
            
            // Pre-calculate webinar item counts to avoid N+1 inside chunk
            $webinarData = [];
            foreach ($subscriptionWebinars as $sw) {
                if (!empty($sw->webinar)) {
                    $w = $sw->webinar;
                    $filesCount = $w->files()->where('status', 'active')->count();
                    $sessionsCount = $w->sessions()->where('status', 'active')->count();
                    $textLessonsCount = $w->textLessons()->where('status', 'active')->count();
                    $assignmentsCount = $w->assignments()->where('status', 'active')->count();
                    $quizzesCount = $w->quizzes()->where('status', 'active')->count();
                    
                    $totalItems = $filesCount + $sessionsCount + $textLessonsCount + $assignmentsCount + $quizzesCount;
                    
                    $webinarData[$w->id] = [
                        'webinar' => $w,
                        'total_items' => $totalItems,
                        'file_ids' => $w->files()->where('status', 'active')->pluck('id')->toArray(),
                        'session_ids' => $w->sessions()->where('status', 'active')->pluck('id')->toArray(),
                        'text_lesson_ids' => $w->textLessons()->where('status', 'active')->pluck('id')->toArray(),
                        'assignment_ids' => $w->assignments()->where('status', 'active')->pluck('id')->toArray(),
                        'quiz_ids' => $w->quizzes()->where('status', 'active')->pluck('id')->toArray(),
                    ];
                }
            }

            // Setup temporary CSV buffer
            $tempFileName = 'exports/temp_' . $this->subscriptionId . '_' . time() . '.csv';
            Storage::disk('local')->put($tempFileName, '');
            $filePath = Storage::disk('local')->path($tempFileName);
            $handle = fopen($filePath, 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['ID', 'Name', 'Mobile', 'Email', 'Purchase Date', 'Learning Progress', 'Status']);

            // Process in chunks of 50
            $studentsQuery->chunk(50, function ($students) use ($webinarData, $handle, &$processedCount, $totalRecords, $tracker) {
                $studentIds = $students->pluck('id')->toArray();
                
                // Batch fetch ALL progress for these 50 students at once
                $allLearnings = \App\Models\CourseLearning::whereIn('user_id', $studentIds)->get()->groupBy('user_id');
                $allAssignmentHistories = \App\Models\WebinarAssignmentHistory::whereIn('student_id', $studentIds)
                    ->where('status', \App\Models\WebinarAssignmentHistory::$passed)
                    ->get()->groupBy('student_id');
                $allQuizResults = \App\Models\QuizzesResult::whereIn('user_id', $studentIds)
                    ->where('status', \App\Models\QuizzesResult::$passed)
                    ->get()->groupBy('user_id');

                foreach ($students as $student) {
                    $totalProgressSum = 0;
                    $webinarCount = 0;

                    $studentLearnings = $allLearnings->get($student->id, collect());
                    $studentAssignments = $allAssignmentHistories->get($student->id, collect());
                    $studentQuizzes = $allQuizResults->get($student->id, collect());

                    foreach ($webinarData as $wId => $data) {
                        $webinarCount++;
                        $passed = 0;
                        
                        // Count passed items for this specific webinar for this specific student
                        $passed += $studentLearnings->whereIn('file_id', $data['file_ids'])->count();
                        $passed += $studentLearnings->whereIn('session_id', $data['session_ids'])->count();
                        $passed += $studentLearnings->whereIn('text_lesson_id', $data['text_lesson_ids'])->count();
                        $passed += $studentAssignments->whereIn('assignment_id', $data['assignment_ids'])->count();
                        $passed += $studentQuizzes->whereIn('quiz_id', $data['quiz_ids'])->count();

                        if ($passed > 0 && $data['total_items'] > 0) {
                            $totalProgressSum += ($passed * 100) / $data['total_items'];
                        }
                    }

                    $avgLearning = ($totalProgressSum > 0 && $webinarCount > 0) ? round($totalProgressSum / $webinarCount, 2) : 0;
                    
                    fputcsv($handle, [
                        $student->id,
                        $student->full_name,
                        $student->mobile,
                        $student->email,
                        $student->purchase_date,
                        $avgLearning,
                        $student->status
                    ]);
                    
                    $processedCount++;
                }

                $tracker->update([
                    'processed_records' => $processedCount,
                    'percentage' => min(99, round(($processedCount / $totalRecords) * 100))
                ]);
                
                unset($students, $allLearnings, $allAssignmentHistories, $allQuizResults);
                gc_collect_cycles();
            });

            fclose($handle);

            $fileName = 'exports/students_' . $this->subscriptionId . '_' . time() . '.xlsx';
            $export = new \App\Exports\BufferedCsvExport($filePath);
            
            if (!Storage::disk('public')->exists('exports')) {
                Storage::disk('public')->makeDirectory('exports');
            }

            Excel::store($export, $fileName, 'public');
            Storage::disk('local')->delete($tempFileName);

            $tracker->update([
                'status' => 'completed',
                'percentage' => 100,
                'download_url' => Storage::disk('public')->url($fileName)
            ]);

        } catch (\Throwable $e) {
            \Log::error('ProcessExcelExportJob Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $tracker->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
