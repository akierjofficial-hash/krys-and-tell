<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login (STAFF/ADMIN ONLY)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->withInput();
        }

        // ✅ Prevent session fixation
        $request->session()->regenerate();

        $user = Auth::user();
        $role = strtolower((string)($user->role ?? ''));

        // ✅ BLOCK normal users from staff/admin portal
        if (!in_array($role, ['admin', 'staff'], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('userlogin')->withErrors([
                'email' => 'Patient/users must sign in using the User Login page.',
            ]);
        }

        // Optional: inactive accounts (for staff/admin)
        if (isset($user->is_active) && !$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Your account is inactive.',
            ])->withInput();
        }

        // Optional: Track last login (safe)
        try {
            if (Schema::hasColumn($user->getTable(), 'last_login_at')) {
                $user->forceFill(['last_login_at' => now()])->save();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // ✅ Respect intended URL
        $fallback = match ($role) {
            'admin' => route('admin.dashboard'),
            'staff' => route('staff.dashboard'),
            default => route('public.home'),
        };

        return redirect()->intended($fallback);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.home');
    }
}
