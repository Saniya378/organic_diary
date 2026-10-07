<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOrderSellerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Farm Order #' . $this->order->order_no . ' Received - Organic Dairy',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.seller-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
