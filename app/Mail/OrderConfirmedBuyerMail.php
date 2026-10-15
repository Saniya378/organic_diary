<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmedBuyerMail extends OrderStatusUpdatedBuyerMail
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public ?string $note = null)
    {
        parent::__construct($order, 'confirmed', $note);
    }

    public function envelope(): Envelope
    {
        $farmName = $this->order->seller?->sellerProfile?->farm_name ?? $this->order->seller?->name ?? 'Farm';
        
        return new Envelope(
            subject: "Order #{$this->order->order_no} Confirmed by {$farmName} - Organic Dairy",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.confirmed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
