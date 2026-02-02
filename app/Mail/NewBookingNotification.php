<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewBookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function build()
    {
        return $this->subject('New Booking: ' . ($this->appointment->appointment_date ?? ''))
            ->markdown('emails.bookings.new_booking', [
                'appointment' => $this->appointment
            ]);
    }
}
