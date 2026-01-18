<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $now = now();

        $base = Appointment::query()
            ->with(['service', 'doctor'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere(function ($qq) use ($user) {
                      // fallback for older records created before user_id existed
                      $qq->whereNull('user_id')->where('public_email', $user->email);
                  });
            })
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['cancelled','canceled','declined','rejected']);
            });

        $upcoming = (clone $base)
            ->where(function ($q) use ($now) {
                $q->whereDate('appointment_date', '>', $now->toDateString())
                  ->orWhere(function ($qq) use ($now) {
                      $qq->whereDate('appointment_date', $now->toDateString())
                         ->where('appointment_time', '>=', $now->format('H:i:s'));
                  });
            })
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->take(5)
            ->get();

        $history = (clone $base)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(10);

        return view('user.profile', compact('user', 'upcoming', 'history'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:190', Rule::unique('users','email')->ignore($user->id)],
            'notify_24h' => ['nullable','boolean'],
            'notify_1h' => ['nullable','boolean'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->notify_24h = (bool)($data['notify_24h'] ?? false);
        $user->notify_1h = (bool)($data['notify_1h'] ?? false);
        $user->save();

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        // ✅ Only require current password if they previously SET a real password
        $hasLocalPassword = (bool)($user->password_set ?? false);

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($hasLocalPassword) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules);

        $user->password = Hash::make($validated['password']);
        $user->password_set = true; // ✅ after they set it once, require current password next time
        $user->save();

        return back()->with('success', $hasLocalPassword ? 'Password updated.' : 'Password set successfully.');
    }
}
