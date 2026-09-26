<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = auth()->user();

        if ($user && !$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'This account is inactive.'], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'This account is inactive. Contact an administrator.',
            ]);
        }

        if (!$user || !in_array($user->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
