<?php

namespace App\Notifications;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentDeclined extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $a = $this->appointment->loadMissing(['service', 'doctor', 'patient', 'user']);

        $dt = null;
        try {
            $dt = Carbon::parse(($a->appointment_date ?? '').' '.($a->appointment_time ?? ''));
        } catch (\Throwable $e) {
            // keep null
        }

        $service = optional($a->service)->name ?? 'Dental Appointment';
        $when    = $dt ? $dt->format('M d, Y h:i A') : '—';

        $patientName =
            data_get($notifiable, 'name')
            ?: ($a->public_name
                ?? trim(($a->public_first_name ?? '') . ' ' . ($a->public_middle_name ? $a->public_middle_name . ' ' : '') . ($a->public_last_name ?? '')))
            ?: ($a->patient->name ?? null)
            ?: 'there';

        $message = (new MailMessage)
            ->subject("Booking Declined: {$service}")
            ->greeting('Hi '.$patientName.',')
            ->line('Your booking request was declined.')
            ->line("**Requested schedule:** {$when}")
            ->line("**Service:** {$service}");

        $reason = trim((string) $a->staff_note);
        if ($reason !== '') $message->line("**Reason from the clinic:** {$reason}");

        return $message->action('Book Another Schedule', route('public.services.index'))
            ->line('If you want help choosing another time, please contact the clinic.');
    }
}
