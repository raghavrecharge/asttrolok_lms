<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\ForumTopicReport;
use Illuminate\Http\Request;

class ForumTopicReportsController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_forum_topic_post_reports');

            $reports = ForumTopicReport::with([
                'user' => function ($query) {
                    $query->select('id', 'full_name');
                },
                'topic',
                'topicPost'
            ])->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('update.topic_and_post_reports'),
                'reports' => $reports
            ];

            return view('admin.forums.topics.reports', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $this->authorize('admin_forum_topic_post_reports');

            $report = ForumTopicReport::findOrFail($id);

            $report->delete();

            return back();
        } catch (\Exception $e) {
            \Log::error('delete error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
