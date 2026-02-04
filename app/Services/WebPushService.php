<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    /**
     * Send a "New booking" push to all staff + admins who enabled push.
     */
    public function sendNewBooking(Appointment $appointment): void
    {
        $publicKey = config('webpush.vapid.public_key');
        $privateKey = config('webpush.vapid.private_key');
        $subject = config('webpush.vapid.subject') ?: config('app.url');

        if (!$publicKey || !$privateKey) {
            // Not configured (safe no-op)
            return;
        }

        // Build a human-friendly message
        $name = $appointment->public_name
            ?? trim(($appointment->public_first_name ?? '').' '.($appointment->public_last_name ?? ''))
            ?: 'New patient';

        $service = $appointment->relationLoaded('service') ? ($appointment->service?->name ?? null) : null;
        $service = $service ?: ($appointment->service_id ? 'New service request' : 'New booking request');

        $date = $appointment->appointment_date ? (string) $appointment->appointment_date : null;
        $time = $appointment->appointment_time ? (string) $appointment->appointment_time : null;

        $when = trim(($date ? $date : '').($time ? ' '.$time : ''));
        if ($when === '') $when = 'Walk-in / time not set';

        $payload = [
            'title' => 'New booking request',
            'body'  => "$name • $service • $when",
            // This route exists in routes/web.php (added in this patch).
            'url'   => url('/approvals'),
            'icon'  => url('/images/pwa/icon-192.png'),
        ];

        $auth = [
            'VAPID' => [
                'subject' => $subject,
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ];

        try {
            $webPush = new WebPush($auth);

            $subs = PushSubscription::query()
                ->whereIn('role', ['admin', 'staff'])
                ->get();

            if ($subs->isEmpty()) return;

            foreach ($subs as $s) {
                $subscription = Subscription::create([
                    'endpoint' => $s->endpoint,
                    'publicKey' => $s->public_key,
                    'authToken' => $s->auth_token,
                    'contentEncoding' => $s->content_encoding ?: 'aesgcm',
                ]);

                $webPush->queueNotification($subscription, json_encode($payload, JSON_UNESCAPED_SLASHES));
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) continue;

                // If the subscription is gone/invalid, remove it.
                $endpoint = method_exists($report, 'getRequest') ? (string) $report->getRequest()->getUri() : null;
                $statusCode = null;

                try {
                    $response = $report->getResponse();
                    $statusCode = $response?->getStatusCode();
                } catch (\Throwable $e) {
                    // ignore
                }

                if ($endpoint && in_array($statusCode, [404, 410], true)) {
                    PushSubscription::where('endpoint', $endpoint)->delete();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Web push send failed: '.$e->getMessage(), [
                'appointment_id' => $appointment->id ?? null,
            ]);
        }
    }
}
