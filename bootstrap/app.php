<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Route middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'kt.return' => \App\Http\Middleware\StoreReturnUrl::class,
        ]);

        // ✅ Trust Render / proxy headers (fixes http/https form submit issues)
        $middleware->append(\App\Http\Middleware\TrustProxies::class);

        // Basic security hardening headers for all web responses.
        $middleware->appendToGroup('web', \App\Http\Middleware\SecurityHeaders::class);

        // ✅ Activity logging (for admin/staff pages)
        $middleware->appendToGroup('web', \App\Http\Middleware\LogUserActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
