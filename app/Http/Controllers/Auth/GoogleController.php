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
        // ✅ Basic scopes only (no People API, no phone/address/birthday)
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google sign-in failed. Please try again.']);
        }

        $googleId = $googleUser->getId();
        $email    = $googleUser->getEmail();

        if (empty($email)) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google did not return an email address. Please use a different account.']);
        }

        // Find existing user by google_id OR email
        $user = User::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if (!$user) {
            // ✅ New Google signup -> role always "user"
            $user = User::create([
                'name'              => $googleUser->getName() ?: 'User',
                'email'             => $email,
                'google_id'         => $googleId,
                'role'              => 'user',
                'email_verified_at' => now(),

                // required (password NOT NULL)
                'password'          => Hash::make(Str::random(40)),
                'password_set'      => false,
            ]);
        } else {
            // Link google_id if missing
            if (empty($user->google_id)) {
                $user->google_id = $googleId;
            }

            // ✅ never overwrite admin/staff role
            if (empty($user->role)) {
                $user->role = 'user';
            }

            // Ensure email verified if they came via google
            if (empty($user->email_verified_at)) {
                $user->email_verified_at = now();
            }

            // Safety for older rows
            if ($user->password_set === null) {
                $user->password_set = false;
            }

            $user->save();
        }

        Auth::login($user, true);

        // ✅ Use portal as a single “role router”
        return redirect()->intended(route('portal'));
    }
}
