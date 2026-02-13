<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Store the last visited list/index URL (GET only) with its query string.
 *
 * This enables "return to same filtered list" behavior after visiting
 * create/show/edit pages and after save/delete/restore actions.
 */
class StoreReturnUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('get')) {
            $route = $request->route();
            $name = $route?->getName();

            // Store only list routes to avoid overwriting the return URL
            // when navigating to show/edit/create pages.
            //
            // NOTE: not every list page uses *.index (some are nested list pages
            // like staff.patients.visits or staff.services.patients).
            $isListRoute = is_string($name) && (
                Str::endsWith($name, '.index') ||
                Str::endsWith($name, '.patients') ||
                Str::endsWith($name, '.visits')
            );

            if ($isListRoute) {
                // Avoid storing LiveSnapshot/widgets etc even if misnamed.
                if (!$request->expectsJson() && !$request->ajax()) {
                    $request->session()->put('kt.return_url', $request->fullUrl());
                }
            }
        }

        return $next($request);
    }
}
