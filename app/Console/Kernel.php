<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('clear:all')
        ->hourly()
        ->appendOutputTo(storage_path('logs/schedule.log'));

        $schedule->job(new \App\Jobs\ExpireTemporaryAccessJob)->hourly()
            ->appendOutputTo(storage_path('logs/expire_temporary_access.log'));

        $schedule->job(new \App\Jobs\ExpireServiceAccessJob)->daily()
            ->appendOutputTo(storage_path('logs/expire_service_access.log'));

        $schedule->job(new \App\Jobs\InstallmentOverdueCheckJob)->dailyAt('06:00')
            ->appendOutputTo(storage_path('logs/installment_overdue_check.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
