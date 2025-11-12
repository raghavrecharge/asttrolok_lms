<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        // if (app()->environment('production')) {
        //     DB::listen(function ($query) {
        //         if ($query->time > 100) {
        //             Log::warning('Slow query detected', [
        //                 'sql' => $query->sql,
        //                 'time' => $query->time
        //             ]);
        //         }
        //     });
        // }
        
    }
}
