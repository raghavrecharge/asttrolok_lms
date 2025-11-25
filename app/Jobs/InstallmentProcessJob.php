<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Web\InstallmentsController;
use App\User;
class InstallmentProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestData;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($requestData)
    {
        $this->requestData = $requestData; 
    }
    
     //public $tries = 5; // Allow the job to be attempted 5 times
    
     //public $timeout = 300; // Time in seconds (e.g., 300s = 5 minutes)

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        $data = $this->requestData;
        try {
        
          $InstallmentController =  new InstallmentsController();
          $InstallmentController->installmentBackgroundProcess($data);
        
         Log::info('ProcessTransactionJob completed successfully.');
        } catch (Exception $e) {
    
            Log::error('Error in ProcessTransactionJob: ' . $e->getMessage());
            throw $e->getMessage();
        }
       
      
         echo "cron job is running proccessing";
    }
}
