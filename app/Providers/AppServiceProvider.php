<?php

namespace App\Providers;

use App\Modules\Bookings\BookingsService;
use App\Modules\Endorsement\Solo\SoloEndorsementService;
use App\Modules\Position\TrainingPositionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BookingsService::class);
        $this->app->singleton(TrainingPositionService::class);
        $this->app->singleton(SoloEndorsementService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
