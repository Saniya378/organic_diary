<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $seller, public string $reason)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Farm Application Status Update - Organic Dairy Gilgit-Baltistan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sellers.rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
