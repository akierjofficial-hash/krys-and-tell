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
     * Handle login
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

        $user = Auth::user();

        // Optional: inactive accounts
        if (isset($user->is_active) && !$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Your account is inactive.',
            ])->withInput();
        }

        // Regenerate session AFTER successful login checks
        $request->session()->regenerate();

        // Optional: Track last login (safe)
        try {
            if (Schema::hasColumn($user->getTable(), 'last_login_at')) {
                $user->forceFill(['last_login_at' => now()])->save();
            }
        } catch (\Throwable $e) {
            // Ignore if the column/table doesn't exist or DB blocks it
        }

        // ✅ Respect intended URL (booking will go back after login)
        $fallback = match ($user->role ?? null) {
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
