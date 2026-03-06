<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Symfony\Component\HttpFoundation\Request;

class TrustProxies extends Middleware
{
    /**
     * Trust all proxies (Render / nginx / etc.)
     *
     * @var array|string|null
     */
    protected $proxies = null;

    public function __construct()
    {
        $trusted = trim((string) env('TRUSTED_PROXIES', ''));

        if ($trusted === '*') {
            $this->proxies = '*';
            return;
        }

        if ($trusted !== '') {
            $this->proxies = array_values(array_filter(array_map('trim', explode(',', $trusted))));
        }
    }

    /**
     * Use Symfony constants (works across Laravel versions)
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_PREFIX;
}
