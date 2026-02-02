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
        $a = $this->appointment->loadMissing(['service', 'doctor', 'patient', 'user']);

        $dt = null;
        try {
            $dt = Carbon::parse(($a->appointment_date ?? '') . ' ' . ($a->appointment_time ?? ''));
        } catch (\Throwable $e) {
            // keep null
        }

        $service = optional($a->service)->name ?? 'Dental Appointment';
        $doctor  = optional($a->doctor)->name ?? ($a->dentist_name ?? 'To be assigned');
        $when    = $dt ? $dt->format('M d, Y h:i A') : '—';

        // ✅ Safe greeting name (works for AnonymousNotifiable too)
        $patientName =
            data_get($notifiable, 'name')
            ?: ($a->public_name
                ?? trim(($a->public_first_name ?? '') . ' ' . ($a->public_middle_name ? $a->public_middle_name . ' ' : '') . ($a->public_last_name ?? '')))
            ?: ($a->patient->name ?? null)
            ?: 'there';

        $note = trim((string)($a->staff_note ?? ''));

        $mail = (new MailMessage)
            ->subject("Booking Approved: {$service}")
            ->greeting('Hi ' . $patientName . ',')
            ->line('Good news — your booking has been approved and confirmed.')
            ->line("**Service:** {$service}")
            ->line("**Date & Time:** {$when}")
            ->line("**Doctor:** {$doctor}");

        // ✅ Include staff note/reason in email
        if ($note !== '') {
            $mail->line("**Note from the clinic:**")
                 ->line($note);
        }

        $mail->action('View My Schedule', route('profile.show'))
            ->line('If you need to reschedule, please contact the clinic.');

        return $mail;
    }
}
