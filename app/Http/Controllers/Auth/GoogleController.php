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

        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if (!$user) {
            // ✅ Create user (password required in production DB)
            $user = User::create([
                'name'              => $googleUser->name ?? $googleUser->nickname ?? 'User',
                'email'             => $googleUser->email,
                'google_id'         => $googleUser->id,
                'role'              => 'user',
                'password'          => Hash::make(Str::random(32)), // ✅ FIX
                'email_verified_at' => now(),
            ]);
        } else {
            if (empty($user->google_id)) {
                $user->google_id = $googleUser->id;
            }

            if (empty($user->role)) {
                $user->role = 'user';
            }

            // ✅ If legacy user has null password, patch it
            if (empty($user->password)) {
                $user->password = Hash::make(Str::random(32));
            }

            $user->save();
        }

        Auth::login($user, true);

        $fallback = match ($user->role) {
            'admin' => route('admin.dashboard'),
            'staff' => route('staff.dashboard'),
            default => route('public.home'),
        };

        return redirect()->intended($fallback);
    }
}
