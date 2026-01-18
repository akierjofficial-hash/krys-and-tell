<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        // Find existing user by google_id OR email
        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if (!$user) {
            // ✅ New Google signup -> role is always "user"
            // ✅ Postgres requires NOT NULL password -> generate a random one
            // ✅ password_set stays false so UI shows "Set Password" (no current_password)
            $user = User::create([
                'name' => $googleUser->name ?? $googleUser->nickname ?? 'User',
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'role' => 'user',
                'email_verified_at' => now(),

                'password' => Hash::make(Str::random(40)),
                'password_set' => false,
            ]);
        } else {
            // Link google_id if missing
            if (empty($user->google_id)) {
                $user->google_id = $googleUser->id;
            }

            // ✅ Do NOT overwrite staff/admin
            if (empty($user->role)) {
                $user->role = 'user';
            }

            // ✅ If old users exist and password_set column is missing/null, keep it false by default
            // (Only becomes true when they set a password in Profile)
            if ($user->password_set === null) {
                $user->password_set = false;
            }

            $user->save();
        }

        Auth::login($user, true);

        // Respect intended URL (e.g. booking) and fallback by role
        $fallback = match ($user->role) {
            'admin' => route('admin.dashboard'),
            'staff' => route('staff.dashboard'),
            default => route('public.home'),
        };

        return redirect()->intended($fallback);
    }
}
