<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedBuyerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $newStatus, public ?string $note = null)
    {
    }

    public function envelope(): Envelope
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $this->newStatus));
        return new Envelope(
            subject: "Order #{$this->order->order_no} Status Update: {$statusLabel} - Organic Dairy",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.status-updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
