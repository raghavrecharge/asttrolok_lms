<?php

namespace App\Providers;

use App\Services\OpsCockpitLogger;
use Illuminate\Support\ServiceProvider;

class OpsCockpitServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(OpsCockpitLogger::class, function ($app) {
            return new OpsCockpitLogger();
        });
    }

    public function boot()
    {
        //
    }
}
