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

        // ── UPE Scheduled Jobs ──
        $schedule->job(new \App\Jobs\PaymentEngine\SubscriptionBillingJob)->daily()
            ->appendOutputTo(storage_path('logs/upe_subscription_billing.log'));

        $schedule->job(new \App\Jobs\PaymentEngine\InstallmentOverdueJob)->dailyAt('06:00')
            ->appendOutputTo(storage_path('logs/upe_installment_overdue.log'));

        $schedule->job(new \App\Jobs\PaymentEngine\ExpireTrialsJob)->daily()
            ->appendOutputTo(storage_path('logs/upe_expire_trials.log'));

        $schedule->job(new \App\Jobs\PaymentEngine\ProcessReferralBonusesJob)->daily()
            ->appendOutputTo(storage_path('logs/upe_referral_bonuses.log'));

        // ── UPE Sync: bridge legacy part-payments & access-control into UPE ──
        $schedule->command('upe:sync-part-payments')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/upe_sync_part_payments.log'));

        $schedule->command('upe:sync-access-control')
            ->dailyAt('00:30')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/upe_sync_access_control.log'));
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
