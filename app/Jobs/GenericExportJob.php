<?php

namespace App\Jobs;

use App\Models\ExportTracker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class GenericExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $export;
    protected $fileName;
    protected $trackerId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export, $fileName, $trackerId)
    {
        $this->export = $export;
        $this->fileName = $fileName;
        $this->trackerId = $trackerId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tracker = ExportTracker::find($this->trackerId);
        
        if (!$tracker) {
            return;
        }

        try {
            $tracker->update([
                'percentage' => 10,
                'status' => 'processing'
            ]);

            // Define the path
            $filePath = 'exports/' . $this->fileName;

            // Run the export and store it in public disk
            Excel::store($this->export, $filePath, 'public');

            $tracker->update([
                'percentage' => 100,
                'status' => 'completed',
                'download_url' => '/store/' . $filePath
            ]);

        } catch (\Exception $e) {
            \Log::error('GenericExportJob failed: ' . $e->getMessage());
            
            if ($tracker) {
                $tracker->update([
                    'status' => 'failed'
                ]);
            }
        }
    }
}
