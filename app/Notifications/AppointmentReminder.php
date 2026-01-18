<?php

namespace App\Notifications;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends Notification
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public string $kind = '24h' // '24h' or '1h'
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $a = $this->appointment;

        $start = Carbon::parse($a->appointment_date.' '.$a->appointment_time);
        $service = optional($a->service)->name ?? 'Dental Appointment';
        $doctor  = optional($a->doctor)->name ?? ($a->dentist_name ?? 'To be assigned');

        $when = $start->format('M d, Y h:i A');
        $relative = $start->diffForHumans(); // e.g. "in 23 hours"

        $subjectPrefix = $this->kind === '1h' ? 'Upcoming in ~1 hour' : 'Upcoming within 24 hours';

        return (new MailMessage)
            ->subject("{$subjectPrefix}: {$service}")
            ->greeting('Hi '.$notifiable->name.',')
            ->line("This is a reminder for your appointment **{$relative}**.")
            ->line("**Service:** {$service}")
            ->line("**Date & Time:** {$when}")
            ->line("**Doctor:** {$doctor}")
            ->action('View My Schedule', route('profile.show'))
            ->line('If you need to reschedule, please contact the clinic.');
    }
}
