<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #{{ $order->order_no }} Delivered</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAF7F0; margin: 0; padding: 24px; color: #1F2A22; }
        .email-container { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #ECE6D6; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .header { background: #1F3A27; color: #ffffff; padding: 32px 28px; text-align: center; }
        .header h1 { margin: 0 0 6px 0; font-family: Georgia, serif; font-size: 24px; color: #ffffff; }
        .header p { margin: 0; color: #E0A93B; font-size: 14px; font-weight: 500; letter-spacing: 0.5px; }
        .content { padding: 28px; }
        .delivered-banner { background: #EBF5EE; border-left: 4px solid #2F5D3A; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px; }
        .delivered-title { font-size: 18px; font-weight: bold; color: #2F5D3A; margin: 0 0 4px 0; }
        .delivered-subtitle { font-size: 14px; color: #386641; margin: 0; }
        .order-meta { background: #F6F4EB; border-radius: 12px; padding: 16px 20px; margin: 20px 0; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .meta-row:last-child { margin-bottom: 0; }
        .meta-label { color: #6B756D; }
        .meta-val { font-weight: 600; color: #1F2A22; }
        .items-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 16px; }
        .items-table th { text-align: left; padding: 8px 4px; color: #6B756D; border-bottom: 1px solid #ECE6D6; font-size: 12px; text-transform: uppercase; }
        .items-table td { padding: 10px 4px; border-bottom: 1px solid #F0ECE1; }
        .items-table tr:last-child td { border-bottom: none; }
        .price-col { text-align: right; font-weight: 600; }
        .cta-btn { display: inline-block; background: #2F5D3A; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 30px; font-weight: bold; font-size: 14px; margin: 20px 0 10px; }
        .footer { background: #FAF7F0; text-align: center; padding: 20px; font-size: 12px; color: #6B756D; border-top: 1px solid #ECE6D6; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Organic Dairy Gilgit-Baltistan</h1>
            <p>100% PURE &bull; MOUNTAIN HARVESTED &bull; FARM TO DOORSTEP</p>
        </div>

        <div class="content">
            <div class="delivered-banner">
                <div class="delivered-title">&#10004; Package Successfully Delivered!</div>
                <div class="delivered-subtitle">Your fresh Himalayan dairy products have arrived at your destination.</div>
            </div>

            <p style="font-size: 15px; line-height: 1.5; color: #1F2A22;">
                Dear <strong>{{ $order->buyer->name ?? 'Valued Customer' }}</strong>,<br>
                We are delighted to confirm that your order <strong>#{{ $order->order_no }}</strong> from <strong>{{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}</strong> has been safely delivered!
            </p>

            @if($deliveryNote)
                <p style="font-size: 13px; color: #6B756D; font-style: italic; background: #FAF7F0; padding: 10px 14px; border-radius: 8px;">
                    "{{ $deliveryNote }}"
                </p>
            @endif

            <div class="order-meta">
                <div class="meta-row">
                    <span class="meta-label">Order Number:</span>
                    <span class="meta-val">#{{ $order->order_no }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Seller / Farm:</span>
                    <span class="meta-val">{{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }} ({{ $order->seller->sellerProfile->location ?? 'Gilgit-Baltistan' }})</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Total Amount:</span>
                    <span class="meta-val" style="color: #2F5D3A;">Rs {{ number_format($order->total) }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Payment Status:</span>
                    <span class="meta-val" style="color: #2F5D3A;">Paid</span>
                </div>
            </div>

            <h3 style="font-family: Georgia, serif; color: #1F3A27; margin: 20px 0 10px;">Items Delivered</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style="text-align: center;">Qty</th>
                        <th class="price-col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                            </td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td class="price-col">Rs {{ number_format($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="text-align: center; margin-top: 24px;">
                <p style="font-size: 14px; color: #4A5568; margin-bottom: 8px;">
                    How was your experience? Your reviews help support local mountain farmers!
                </p>
                <a href="{{ route('orders.show', $order->id) }}" class="cta-btn">
                    View Order & Leave a Review
                </a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Organic Dairy Marketplace. Pure mountain dairy from Hunza, Skardu & Ghizer.
        </div>
    </div>
</body>
</html>
