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
        $a = $this->appointment->loadMissing(['service', 'doctor']);

        $dt = null;
        try {
            $dt = Carbon::parse(($a->appointment_date ?? '').' '.($a->appointment_time ?? ''));
        } catch (\Throwable $e) {
            // keep null
        }

        $service = optional($a->service)->name ?? 'Dental Appointment';
        $when    = $dt ? $dt->format('M d, Y h:i A') : '—';

        return (new MailMessage)
            ->subject("Booking Declined: {$service}")
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Your booking request was declined.')
            ->line("**Requested schedule:** {$when}")
            ->line("**Service:** {$service}")
            ->action('Book Another Schedule', route('public.services.index'))
            ->line('If you want help choosing another time, please contact the clinic.');
    }
}
