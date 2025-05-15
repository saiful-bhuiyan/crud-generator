<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cache and share general settings with all Blade views
        view()->composer('*', function ($view) {
            // $settings = cache()->rememberForever('general_settings', function () {
            //     return \DB::table('general_settings')->pluck('value', 'key')->toArray();
            // });
            $settings = \DB::table('general_settings')->pluck('value', 'type')->toArray();
            $view->with('general_settings', $settings);
        });
    }
}
