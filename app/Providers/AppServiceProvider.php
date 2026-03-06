<?php

namespace App\Providers;

use App\Listeners\UpdateLastLogin;
use App\Models\Appointment;              // ✅ add this
use App\Observers\AppointmentObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
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
            config(['session.secure' => true]);
        }

        // ✅ Use Bootstrap pagination views globally (fixes huge next/prev SVG issue)
        Paginator::useBootstrapFive();

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email', '');
            return Limit::perMinute(8)->by($request->ip() . '|' . strtolower($email));
        });

        RateLimiter::for('public-forms', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('booking-submit', function (Request $request) {
            $user = $request->user();
            $key = $user?->id ? ('user:' . $user->id) : ('ip:' . $request->ip());

            return Limit::perMinute(4)->by($key);
        });

        RateLimiter::for('push-subscriptions', function (Request $request) {
            $user = $request->user();
            return Limit::perMinute(30)->by($user?->id ?: $request->ip());
        });

        // ✅ Track last login (Laravel 11/12 - no EventServiceProvider by default)
        Event::listen(Login::class, UpdateLastLogin::class);

        // ✅ Push notify on new bookings (observer)
        Appointment::observe(AppointmentObserver::class);
    }
}
