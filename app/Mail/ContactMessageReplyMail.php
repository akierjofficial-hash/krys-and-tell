<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $contactMessage,
        public string $subjectLine,
        public string $replyBody
    ) {}

    public function build()
    {
        return $this
            ->from('krysandt@gmail.com', 'Krys & Tell Dental Clinic')
            ->replyTo('krysandt@gmail.com', 'Krys & Tell Dental Clinic')
            ->subject($this->subjectLine)
            ->markdown('emails.contact.reply', [
                'contactMessage' => $this->contactMessage,
                'replyBody' => $this->replyBody,
            ]);
    }
}
