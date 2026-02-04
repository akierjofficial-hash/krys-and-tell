<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushService
{
    public function notifyNewBooking(Appointment $appointment): void
    {
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

        $this->sendToAll($title, $body, '/');
    }

    public function sendToAll(string $title, string $body, string $url = '/'): void
    {
        // ✅ Silence ONLY the GMP/BCMath notice from minishlink/web-push
        $oldReporting = error_reporting();
        $noticeNeedle = 'install the GMP or BCMath extension';

        $handler = set_error_handler(function ($severity, $message, $file, $line) use ($noticeNeedle) {
            if (is_string($message) && str_contains($message, $noticeNeedle)) {
                Log::warning('WebPush notice (ignored): ' . $message);
                return true; // handled; do not convert to exception
            }
            return false; // let Laravel handle other errors normally
        });

        try {
            $publicKey  = env('VAPID_PUBLIC_KEY');
            $privateKey = env('VAPID_PRIVATE_KEY');
            $subject    = env('VAPID_SUBJECT');

            if (!$publicKey || !$privateKey || !$subject) {
                Log::warning('WebPush: Missing VAPID env vars. Push skipped.');
                return;
            }

            $subs = PushSubscription::query()->get();
            if ($subs->isEmpty()) return;

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
                $subscription = Subscription::create([
                    'endpoint' => $s->endpoint,
                    'publicKey' => $s->public_key,
                    'authToken' => $s->auth_token,
                    'contentEncoding' => $s->content_encoding ?: 'aesgcm',
                ]);

                $webPush->queueNotification($subscription, $payload);
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) continue;

                $endpoint = method_exists($report, 'getRequest') && $report->getRequest()
                    ? (string) $report->getRequest()->getUri()
                    : null;

                $reason = (string) $report->getReason();

                Log::warning('WebPush failed: ' . $reason, [
                    'endpoint' => $endpoint,
                ]);

                // Expired subscription: remove
                if ($endpoint && str_contains($reason, '410')) {
                    PushSubscription::where('endpoint', $endpoint)->delete();
                }
            }
        } catch (\Throwable $e) {
            // IMPORTANT: never break booking flow
            Log::error('WebPush exception (ignored): ' . $e->getMessage());
        } finally {
            if ($handler !== null) restore_error_handler();
            error_reporting($oldReporting);
        }
    }
}
