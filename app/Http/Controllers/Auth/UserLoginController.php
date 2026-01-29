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
        $role = strtolower((string)($u->role ?? ''));

        // ✅ Allow ONLY role=user in /userlogin
        if ($role !== 'user') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'This account is Staff/Admin. Please use the Staff/Admin portal.',
            ]);
        }

        /**
         * ✅ Redirect priority:
         * 1) explicit ?redirect=/somewhere (like /book/ID)
         * 2) intended URL (Laravel auth protected page)
         * 3) fallback to Services page
         */
        $redirect = $request->input('redirect');
        if ($redirect) {
            return redirect()->to($redirect);
        }

        return redirect()->intended(route('public.services.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.home');
    }
}
