<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $name,
        public int $expireMinutes = 10
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Organic Dairy Verification Code: {$this->otp}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.registration-otp',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
