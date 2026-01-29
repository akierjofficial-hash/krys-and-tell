<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserLoginController extends Controller
{
    public function show(Request $request)
    {
        return view('auth.userlogin');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        $request->session()->regenerate();

        $u = Auth::user();
        $role = strtolower((string) ($u->role ?? ''));

        // ✅ Allow ONLY role=user in /userlogin
        if ($role !== 'user') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'This account is Staff/Admin. Please use the Staff/Admin portal.',
            ]);
        }

        // ✅ IMPORTANT: prevent old intended URLs (like /profile) from overriding
        $request->session()->forget('url.intended');

        /**
         * ✅ Redirect rules:
         * 1) If explicit redirect is provided (and is a safe internal path), go there
         * 2) Otherwise ALWAYS go to Services page
         */
        $redirect = $request->input('redirect');

        // Only allow internal redirects (security + avoids weird redirects)
        if (is_string($redirect) && $redirect !== '' && str_starts_with($redirect, '/')) {
            return redirect()->to($redirect);
        }

        // ✅ Force services page (NOT profile)
        return redirect()->route('public.services.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.home');
    }
}
