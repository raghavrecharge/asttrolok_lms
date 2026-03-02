<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Exports\AgoraHistoryExport;
use App\Http\Controllers\Controller;
use App\Models\AgoraHistory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\BackgroundExportTrait;

class AgoraHistoryController extends Controller
{
    use BackgroundExportTrait;
    public function index()
    {
        try {
            $this->authorize('admin_agora_history_list');

            $agoraHistories = AgoraHistory::whereNotNull('end_at')
                ->orderBy('start_at')
                ->with([
                    'session' => function ($query) {
                        $query->with('webinar');
                    }
                ])
                ->paginate(10);

            $data = [
                'pageTitle' => trans('update.agora_history'),
                'agoraHistories' => $agoraHistories
            ];

            return view('admin.agora_history.index', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function exportExcel()
    {
        try {
            $agoraHistories = AgoraHistory::whereNotNull('end_at')
                ->orderBy('start_at')
                ->with([
                    'session' => function ($query) {
                        $query->with('webinar');
                    }
                ])
                ->get();

            $export = new AgoraHistoryExport($agoraHistories);

            return $this->dispatchBackgroundExport($export, 'agoraHistory_' . date('Y-m-d_H-i-s') . '.xlsx', 'Agora History Export');
        } catch (\Exception $e) {
            \Log::error('exportExcel error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
