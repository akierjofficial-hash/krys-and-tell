<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || $user->is_active) return $next($request);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if ($request->expectsJson()) return response()->json(['message' => 'This account is inactive.'], 403);
        return redirect()->route($user->role === 'user' ? 'userlogin' : 'login')
            ->withErrors(['email' => 'This account is inactive. Contact an administrator.']);
    }
}
