<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
            $user = User::create([
                'name' => $googleUser->name ?? $googleUser->nickname ?? 'User',
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'role' => 'user',
                'email_verified_at' => now(), // optional but recommended for OAuth
            ]);
        } else {
            // Link google_id if missing
            if (empty($user->google_id)) {
                $user->google_id = $googleUser->id;
            }

            // ✅ Don’t overwrite staff/admin — only set if empty/null
            if (empty($user->role)) {
                $user->role = 'user';
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
