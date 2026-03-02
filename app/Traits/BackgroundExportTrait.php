<?php

namespace App\Traits;

use App\Jobs\GenericExportJob;
use App\Models\ExportTracker;
use Illuminate\Support\Facades\Auth;

trait BackgroundExportTrait
{
    /**
     * Dispatch a background export job and return a JSON response.
     *
     * @param mixed $export The Maatwebsite\Excel export object
     * @param string $fileName
     * @param string $title
     * @return \Illuminate\Http\JsonResponse
     */
    protected function dispatchBackgroundExport($export, $fileName, $title = 'Export')
    {
        $user = Auth::user();

        $tracker = ExportTracker::create([
            'user_id' => $user->id,
            'title' => $title,
            'type' => 'excel',
            'percentage' => 0,
            'status' => 'pending'
        ]);

        dispatch(new GenericExportJob($export, $fileName, $tracker->id));

        return response()->json([
            'status' => 'success',
            'msg' => trans('public.request_success') . ' - Export started. Check the tray in navbar for progress.',
            'tracker_id' => $tracker->id
        ]);
    }
}
