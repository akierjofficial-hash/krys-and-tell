<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\Appointment;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushService
{
    /**
     * Notify all subscribed staff/admin devices when a new booking is created.
     * IMPORTANT: This method must never throw, or booking will 500.
     */
    public function notifyNewBooking(Appointment $appointment): void
    {
        // Build a friendly message (safe fallbacks)
        $serviceName = null;
        try {
            $serviceName = optional($appointment->service)->name;
        } catch (\Throwable $e) {
            // ignore
        }
        $serviceName = $serviceName ?: ($appointment->service_name ?? 'Booking');

        $patientName = $appointment->patient_name
            ?? $appointment->full_name
            ?? $appointment->name
            ?? $appointment->public_name
            ?? $appointment->public_full_name
            ?? 'A patient';

        $title = 'New Booking Request';
        $body  = "{$patientName} • {$serviceName}";

        // Safer default: open homepage (works for both staff/admin)
        // You can change this later to a shared /approvals redirect route if you want.
        $url   = '/';

        $this->sendToAll($title, $body, $url);
    }

    /**
     * Send a push notification to all saved subscriptions.
     */
    public function sendToAll(string $title, string $body, string $url = '/'): void
    {
        try {
            $publicKey  = env('VAPID_PUBLIC_KEY');
            $privateKey = env('VAPID_PRIVATE_KEY');
            $subject    = env('VAPID_SUBJECT');

            if (!$publicKey || !$privateKey || !$subject) {
                Log::warning('WebPush: Missing VAPID env vars. Push skipped.');
                return;
            }

            $subs = PushSubscription::query()->get();
            if ($subs->isEmpty()) {
                return;
            }

            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => $subject,
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);

            $payload = json_encode([
                'title' => $title,
                'body'  => $body,
                'url'   => $url,
            ], JSON_UNESCAPED_SLASHES);

            foreach ($subs as $s) {
                // Your DB columns are expected to be: endpoint, public_key, auth_token, content_encoding
                $subscription = Subscription::create([
                    'endpoint' => $s->endpoint,
                    'publicKey' => $s->public_key,
                    'authToken' => $s->auth_token,
                    'contentEncoding' => $s->content_encoding ?: 'aesgcm',
                ]);

                $webPush->queueNotification($subscription, $payload);
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    continue;
                }

                // Remove expired subscriptions (410 Gone) so it doesn’t keep failing
                $endpoint = method_exists($report, 'getRequest') && $report->getRequest()
                    ? (string) $report->getRequest()->getUri()
                    : null;

                $reason = $report->getReason();

                Log::warning('WebPush failed: ' . $reason, [
                    'endpoint' => $endpoint,
                ]);

                // If endpoint expired, delete it
                if ($endpoint && str_contains((string) $reason, '410')) {
                    PushSubscription::where('endpoint', $endpoint)->delete();
                }
            }
        } catch (\Throwable $e) {
            // IMPORTANT: never break bookings
            Log::error('WebPush exception (ignored): ' . $e->getMessage());
        }
    }
}
