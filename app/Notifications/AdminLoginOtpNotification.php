<?php

// app/Notifications/AdminLoginOtpNotification.php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AdminLoginOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        public int $minutesValid = 10
    ) {}

    public function via($notifiable): array {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage {
        return (new MailMessage)
            ->subject('Your Admin Login Code')
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Use this code to finish logging in:')
            ->line('**'.$this->code.'**')
            ->line('This code expires in '.$this->minutesValid.' minutes.')
            ->line('If you did not try to log in, please change your password.');
    }
}
