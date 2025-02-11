<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ImageService;
use app\Services\CcHubSettingsService;
use app\Services\CcHubTokenService;
use app\Services\ImageProcessingService;

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
        $this->app->bind(ImageService::class);
        $this->app->bind(CcHubSettingsService::class);
        $this->app->bind(CcHubTokenService::class);
        $this->app->bind(ImageProcessingService::class);
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
