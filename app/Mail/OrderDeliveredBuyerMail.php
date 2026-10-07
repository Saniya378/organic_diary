<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredBuyerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public ?string $deliveryNote = null)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Order #{$this->order->order_no} has been Delivered! - Organic Dairy",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.delivered',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
