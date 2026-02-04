<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Store or update a Web Push subscription for the logged-in staff/admin user.
     */
    public function store(Request $request)
    {
        $payload = $request->input('subscription') ?? $request->all();

        $endpoint = $payload['endpoint'] ?? null;
        $keys = $payload['keys'] ?? [];

        $p256dh = $keys['p256dh'] ?? null;
        $auth = $keys['auth'] ?? null;

        if (!$endpoint || !$p256dh || !$auth) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid subscription payload.',
            ], 422);
        }

        $user = $request->user();

        PushSubscription::updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'user_id' => $user?->id,
                'role' => $user?->role,
                'public_key' => $p256dh,
                'auth_token' => $auth,
                'content_encoding' => $payload['contentEncoding'] ?? $payload['content_encoding'] ?? 'aesgcm',
            ]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Remove a stored subscription (usually after unsubscribe).
     */
    public function destroy(Request $request)
    {
        $endpoint = $request->input('endpoint');
        if (!$endpoint) {
            $payload = $request->input('subscription') ?? $request->all();
            $endpoint = $payload['endpoint'] ?? null;
        }

        if (!$endpoint) {
            return response()->json([
                'ok' => false,
                'message' => 'Endpoint is required.',
            ], 422);
        }

        PushSubscription::where('endpoint', $endpoint)->delete();

        return response()->json(['ok' => true]);
    }
}
