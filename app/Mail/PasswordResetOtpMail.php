<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $otp,
        public int $expireMinutes = 10
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Password Reset OTP: {$this->otp} - Organic Dairy",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.password-reset-otp',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
