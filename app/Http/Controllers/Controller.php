<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Open-redirect safe check: allow only relative URLs or same-host absolute URLs.
     */
    protected function ktIsSafeReturnUrl(?string $url): bool
    {
        if (!$url) return false;

        // Relative path (including query/hash)
        if (Str::startsWith($url, '/')) return true;

        // Absolute URL but only if it matches app host
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $reqHost = request()?->getHost();
        $host = parse_url($url, PHP_URL_HOST);

        if (!is_string($host)) return false;
        if (is_string($appHost) && Str::lower($host) === Str::lower($appHost)) return true;
        if (is_string($reqHost) && Str::lower($host) === Str::lower($reqHost)) return true;

        return false;
    }

    /**
     * Resolve the best return URL in priority order:
     *  a) explicit ?return= / return input (safe)
     *  b) session('kt.return_url') (safe)
     *  c) fallback route
     */
    protected function ktReturnUrl(Request $request, string $fallbackRouteName, array $fallbackParams = []): string
    {
        $candidate = $request->input('return') ?? $request->query('return');
        if ($this->ktIsSafeReturnUrl($candidate)) {
            return $candidate;
        }

        $sessionUrl = $request->session()->get('kt.return_url');
        if ($this->ktIsSafeReturnUrl($sessionUrl)) {
            return (string) $sessionUrl;
        }

        return route($fallbackRouteName, $fallbackParams);
    }

    protected function ktRedirectToReturn(Request $request, string $fallbackRouteName, array $fallbackParams = []): RedirectResponse
    {
        return redirect()->to($this->ktReturnUrl($request, $fallbackRouteName, $fallbackParams));
    }
}
