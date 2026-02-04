<?php

namespace App\Providers;

use App\Listeners\UpdateLastLogin;
use App\Models\Appointment;              // ✅ add this
use App\Observers\AppointmentObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
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
        // Force HTTPS in production (Render/Railway/etc)
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // ✅ Use Bootstrap pagination views globally (fixes huge next/prev SVG issue)
        Paginator::useBootstrapFive();

        // ✅ Track last login (Laravel 11/12 - no EventServiceProvider by default)
        Event::listen(Login::class, UpdateLastLogin::class);

        // ✅ Push notify on new bookings (observer)
        Appointment::observe(AppointmentObserver::class);
    }
}
