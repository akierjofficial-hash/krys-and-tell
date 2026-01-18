<?php

namespace App\Notifications;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentApproved extends Notification
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
        $doctor  = optional($a->doctor)->name ?? ($a->dentist_name ?? 'To be assigned');
        $when    = $dt ? $dt->format('M d, Y h:i A') : '—';

        return (new MailMessage)
            ->subject("Booking Approved: {$service}")
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Good news — your booking has been approved and confirmed.')
            ->line("**Service:** {$service}")
            ->line("**Date & Time:** {$when}")
            ->line("**Doctor:** {$doctor}")
            ->action('View My Schedule', route('profile.show'))
            ->line('If you need to reschedule, please contact the clinic.');
    }
}
