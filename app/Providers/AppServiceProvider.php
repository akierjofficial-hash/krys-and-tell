<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Listeners\UpdateLastLogin;

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
        // Force HTTPS in production (Render/Railway/etc)
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // ✅ Track last login (Laravel 11/12 - no EventServiceProvider by default)
        Event::listen(Login::class, UpdateLastLogin::class);
    }
}
