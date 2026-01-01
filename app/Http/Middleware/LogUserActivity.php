<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class LogUserActivity
{
    /**
     * OFF by default to avoid noise.
     * Set true if you also want GET page views (with allowlist below).
     */
    private bool $logGetPageViews = false;

    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        $route = $request->route();
        $routeName = $route?->getName(); // use route_name column

        // if no route name, skip (keeps logs clean)
        if (!$routeName) {
            return $next($request);
        }

        if ($this->shouldSkip($request, $routeName)) {
            return $next($request);
        }

        try {
            $response = $next($request);

            $durationMs = (int) round((microtime(true) - $start) * 1000);

            $this->writeLog(
                request: $request,
                userId: (int) $user->getAuthIdentifier(),
                routeName: $routeName,
                statusCode: method_exists($response, 'getStatusCode') ? (int) $response->getStatusCode() : null,
                durationMs: $durationMs,
                exception: null
            );

            return $response;
        } catch (Throwable $e) {
            $durationMs = (int) round((microtime(true) - $start) * 1000);

            $this->writeLog(
                request: $request,
                userId: (int) $user->getAuthIdentifier(),
                routeName: $routeName,
                statusCode: 500,
                durationMs: $durationMs,
                exception: $e
            );

            throw $e;
        }
    }

    private function shouldSkip(Request $request, string $routeName): bool
    {
        // Default: log ONLY non-GET (CRUD/actions)
        if ($request->isMethod('GET') && !$this->logGetPageViews) {
            return true;
        }

        // exclude tooling
        if (
            str_starts_with($routeName, 'ignition.') ||
            str_starts_with($routeName, 'telescope.') ||
            str_starts_with($routeName, 'horizon.') ||
            str_starts_with($routeName, 'debugbar.')
        ) {
            return true;
        }

        // exclude auth noise
        if ($request->routeIs('login', 'login.submit', 'logout', 'password.*', 'verification.*')) {
            return true;
        }

        // exclude assets
        if ($request->is('storage/*', 'build/*', 'assets/*', 'favicon.ico')) {
            return true;
        }

        // If GET logging is ON, allowlist only admin/staff pages
        if ($request->isMethod('GET') && $this->logGetPageViews) {
            if (!$request->routeIs('admin.*', 'staff.*')) {
                return true;
            }
        }

        return false;
    }

    private function writeLog(
        Request $request,
        int $userId,
        string $routeName,
        ?int $statusCode,
        int $durationMs,
        ?Throwable $exception
    ): void {
        try {
            $event = $this->resolveEvent($request, $routeName);
            $description = $this->makeDescription($request, $routeName, $event);

            $routeParams = $this->normalizeRouteParams($request->route()?->parameters() ?? []);

            $properties = [
                'role' => $this->resolveRole(Auth::user()),
                'duration_ms' => $durationMs,
                'route_params' => $routeParams,
                'query_keys' => array_keys($request->query() ?? []),
                'input_keys' => array_keys($request->except(['_token','_method','password','password_confirmation','current_password'])),
            ];

            if ($exception) {
                $properties['exception'] = [
                    'class' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];
            }

            ActivityLog::create([
                'user_id' => $userId,
                'event' => $event,
                'description' => $description,
                'route_name' => $routeName,
                'url' => $request->fullUrl(),
                'method' => strtoupper($request->method()),
                'status_code' => $statusCode,
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'properties' => $properties,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function resolveEvent(Request $request, string $routeName): string
    {
        $lower = strtolower($routeName);
        if (str_contains($lower, 'approve')) return 'approve';
        if (str_contains($lower, 'decline') || str_contains($lower, 'reject')) return 'decline';

        if ($request->isMethod('POST')) return 'create';
        if ($request->isMethod('PUT') || $request->isMethod('PATCH')) return 'update';
        if ($request->isMethod('DELETE')) return 'delete';
        if ($request->isMethod('GET')) return 'view';

        return 'action';
    }

    private function makeDescription(Request $request, string $routeName, string $event): string
    {
        $params = $this->normalizeRouteParams($request->route()?->parameters() ?? []);

        $resourceId = null;
        foreach (['patient','appointment','visit','payment','booking','user','plan'] as $key) {
            if (isset($params[$key])) {
                $resourceId = $key . ':' . $params[$key];
                break;
            }
        }

        $base = strtoupper($request->method()) . ' ' . $routeName;

        return $resourceId
            ? "{$event} ({$resourceId}) via {$base}"
            : "{$event} via {$base}";
    }

    private function normalizeRouteParams(array $params): array
    {
        $out = [];
        foreach ($params as $key => $value) {
            if ($value instanceof Model) $out[$key] = $value->getKey();
            else $out[$key] = $value;
        }
        return $out;
    }

    private function resolveRole($user): ?string
    {
        if (!$user) return null;

        if (isset($user->role) && is_string($user->role)) {
            return $user->role;
        }

        if (method_exists($user, 'getRoleNames')) {
            $names = $user->getRoleNames();
            return $names[0] ?? null;
        }

        return null;
    }
}
