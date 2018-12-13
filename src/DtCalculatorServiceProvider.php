<?php

namespace Prymag\DtCalculator;

use Illuminate\Support\ServiceProvider;

use Prymag\DtCalculator\DtCalculatorService;

class DtCalculatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind(DtCalculatorService::class, function ($app) {
            return new DtCalculatorService(\Carbon\Carbon::class);
        });
    }
}
