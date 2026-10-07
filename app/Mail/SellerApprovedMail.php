<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $seller)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Farm Account Approved! Welcome to Organic Dairy Gilgit-Baltistan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sellers.approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
