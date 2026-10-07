<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Status Update #{{ $order->order_no }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAF7F0; margin: 0; padding: 24px; color: #1F2A22; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #ECE6D6; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .header { background: #1F3A27; color: #ffffff; padding: 28px; text-align: center; }
        .header h1 { margin: 0 0 6px 0; font-family: Georgia, serif; font-size: 22px; color: #ffffff; }
        .header p { margin: 0; color: #E0A93B; font-size: 13px; font-weight: 500; }
        .content { padding: 28px; }
        .status-badge { display: inline-block; background-color: #E8F5E9; color: #2F5D3A; font-weight: bold; font-size: 14px; padding: 6px 16px; border-radius: 20px; text-transform: uppercase; }
        .order-meta { background: #F6F4EB; border-radius: 12px; padding: 16px 20px; margin: 20px 0; font-size: 14px; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .meta-row:last-child { margin-bottom: 0; }
        .btn-track { display: inline-block; background-color: #2F5D3A; color: #ffffff !important; padding: 12px 24px; border-radius: 30px; font-weight: bold; text-decoration: none; margin-top: 16px; }
        .footer { background: #FAF7F0; text-align: center; padding: 20px; font-size: 12px; color: #6B756D; border-top: 1px solid #ECE6D6; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Organic Dairy Gilgit-Baltistan</h1>
            <p>ORDER PROGRESS NOTIFICATION</p>
        </div>

        <div class="content">
            <h2 style="font-family: Georgia, serif; color: #1F3A27; margin-top: 0;">Order Status Update</h2>
            <p style="font-size: 15px; line-height: 1.5; color: #1F2A22;">
                Hello <strong>{{ $order->orderGroup->shipping_full_name }}</strong>,<br>
                Your farm order <strong>#{{ $order->order_no }}</strong> from <strong>{{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}</strong> has advanced to:
            </p>

            <div style="text-align: center; margin: 24px 0;">
                <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $newStatus)) }}</span>
            </div>

            @if($note)
                <p style="font-size: 14px; color: #4A5568; background: #FFF9E6; padding: 12px 16px; border-radius: 8px; border-left: 4px solid #E0A93B;">
                    <strong>Note from Farm / Courier:</strong> {{ $note }}
                </p>
            @endif

            @if($order->rider_name && in_array($newStatus, ['in_transit', 'delivered']))
                <div style="background: #EBF5EE; border-radius: 12px; padding: 16px; margin: 16px 0;">
                    <strong style="color: #2F5D3A; display: block; margin-bottom: 4px;">Assigned Delivery Rider:</strong>
                    <div>Name: {{ $order->rider_name }}</div>
                    @if($order->rider_phone)
                        <div>Contact: <a href="tel:{{ $order->rider_phone }}" style="color: #2F5D3A; font-weight: bold;">{{ $order->rider_phone }}</a></div>
                    @endif
                </div>
            @endif

            <div class="order-meta">
                <div class="meta-row">
                    <span style="color: #6B756D;">Sub-Order No:</span>
                    <strong>#{{ $order->order_no }}</strong>
                </div>
                <div class="meta-row">
                    <span style="color: #6B756D;">Order Total:</span>
                    <strong>Rs {{ number_format($order->total) }}</strong>
                </div>
                <div class="meta-row">
                    <span style="color: #6B756D;">Delivery City:</span>
                    <strong>{{ $order->orderGroup->shipping_city }}</strong>
                </div>
            </div>

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ route('orders.track', $order->order_no) }}" class="btn-track">
                    Track Live Dispatch Timeline &rarr;
                </a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Organic Dairy Gilgit-Baltistan &bull; Sustainable Cold-Chain Dairy
        </div>
    </div>
</body>
</html>
