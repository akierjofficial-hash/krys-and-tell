<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReplyMail;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StaffContactMessageReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_reply_to_contact_message_and_reply_is_logged(): void
    {
        Mail::fake();

        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $contact = ContactMessage::create([
            'name' => 'Jane Sender',
            'email' => 'jane.sender@example.com',
            'message' => 'Hello clinic, I have a question about schedule.',
        ]);

        $payload = [
            'subject' => 'Re: Your message to Krys & Tell',
            'reply_message' => 'Thanks for contacting us. We will assist you shortly.',
        ];

        $this->actingAs($staff)
            ->post(route('staff.messages.reply', $contact), $payload)
            ->assertRedirect(route('staff.messages.show', $contact));

        Mail::assertSent(ContactMessageReplyMail::class, function (ContactMessageReplyMail $mail) use ($contact, $payload) {
            return $mail->hasTo($contact->email)
                && $mail->subjectLine === $payload['subject']
                && $mail->replyBody === $payload['reply_message'];
        });

        $contact->refresh();

        $this->assertNotNull($contact->read_at);
        $this->assertNotNull($contact->replied_at);
        $this->assertSame($payload['subject'], $contact->reply_subject);
        $this->assertSame($payload['reply_message'], $contact->reply_message);
    }
}
