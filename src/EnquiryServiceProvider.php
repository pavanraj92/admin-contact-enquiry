<?php

namespace admin\enquiries;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class EnquiryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/enquiry.php',
            'enquiries'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'enquiries');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/enquiry.php' => config_path('enquiry.php'),
            ], 'enquiries-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/enquiries'),
            ], 'enquiries-views');
        }
    }
}
