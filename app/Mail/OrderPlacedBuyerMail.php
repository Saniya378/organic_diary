<?php

namespace App\Mail;

use App\Models\OrderGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedBuyerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public OrderGroup $orderGroup)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmation #' . $this->orderGroup->group_no . ' - Organic Dairy Gilgit-Baltistan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.placed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
