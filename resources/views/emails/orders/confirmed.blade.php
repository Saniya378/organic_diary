<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #{{ $order->order_no }} Confirmed</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAF7F0; margin: 0; padding: 24px; color: #1F2A22; }
        .email-container { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #ECE6D6; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .header { background: #1F3A27; color: #ffffff; padding: 32px 28px; text-align: center; }
        .header h1 { margin: 0 0 6px 0; font-family: Georgia, serif; font-size: 24px; color: #ffffff; }
        .header p { margin: 0; color: #E0A93B; font-size: 13px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
        .content { padding: 28px; }
        .confirmed-banner { background: #EBF5EE; border-left: 4px solid #2F5D3A; border-radius: 10px; padding: 18px 20px; margin-bottom: 24px; }
        .confirmed-title { font-size: 18px; font-weight: bold; color: #2F5D3A; margin: 0 0 4px 0; display: flex; align-items: center; gap: 6px; }
        .confirmed-subtitle { font-size: 14px; color: #386641; margin: 0; }
        .order-meta { background: #F6F4EB; border-radius: 12px; padding: 16px 20px; margin: 20px 0; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .meta-row:last-child { margin-bottom: 0; }
        .meta-label { color: #6B756D; }
        .meta-val { font-weight: 600; color: #1F2A22; }
        .badge-status { display: inline-block; background-color: #E8F5E9; color: #2F5D3A; font-weight: bold; font-size: 12px; padding: 4px 12px; border-radius: 20px; }
        .items-table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 16px; }
        .items-table th { text-align: left; padding: 10px 6px; color: #6B756D; border-bottom: 2px solid #ECE6D6; font-size: 12px; text-transform: uppercase; }
        .items-table td { padding: 12px 6px; border-bottom: 1px solid #F0ECE1; }
        .items-table tr:last-child td { border-bottom: none; }
        .price-col { text-align: right; font-weight: 600; }
        .summary-card { background: #FAF7F0; border-radius: 12px; padding: 16px 20px; margin-top: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .summary-row.total { border-top: 1px solid #ECE6D6; padding-top: 10px; font-size: 16px; font-weight: bold; color: #1F3A27; margin-bottom: 0; }
        .cta-btn { display: inline-block; background: #2F5D3A; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 30px; font-weight: bold; font-size: 14px; margin: 10px 4px; }
        .btn-secondary { background: #FAF7F0; color: #2F5D3A !important; border: 1px solid #2F5D3A; }
        .footer { background: #FAF7F0; text-align: center; padding: 22px; font-size: 12px; color: #6B756D; border-top: 1px solid #ECE6D6; }
    </style>
</head>
<body>
    @php
        $farmName = $order->seller?->sellerProfile?->farm_name ?? $order->seller?->name ?? 'Farm Seller';
        $farmLocation = $order->seller?->sellerProfile?->location ?? 'Gilgit-Baltistan';
        $buyerName = $order->orderGroup?->shipping_full_name ?? $order->buyer?->name ?? 'Customer';
        $shippingCity = $order->orderGroup?->shipping_city ?? 'Gilgit-Baltistan';
        $shippingAddress = $order->orderGroup?->shipping_address_line ?? 'Your Address';
        $shippingArea = $order->orderGroup?->shipping_area ?? '';
        $shippingPhone = $order->orderGroup?->shipping_phone ?? $order->buyer?->phone ?? '';
    @endphp

    <div class="email-container">
        <!-- Brand Header -->
        <div class="header">
            <h1>Organic Dairy Gilgit-Baltistan</h1>
            <p>Order Confirmed &bull; Preparing Fresh Produce</p>
        </div>

        <div class="content">
            <!-- Confirmation Banner -->
            <div class="confirmed-banner">
                <div class="confirmed-title">&#10004; Order Confirmed by Farm!</div>
                <div class="confirmed-subtitle">
                    <strong>{{ $farmName }}</strong> has verified availability and confirmed your order.
                </div>
            </div>

            <p style="font-size: 15px; line-height: 1.6; color: #1F2A22; margin-top: 0;">
                Dear <strong>{{ $buyerName }}</strong>,<br>
                Great news! Your farm order <strong>#{{ $order->order_no }}</strong> has been officially confirmed by <strong>{{ $farmName }}</strong> in <strong>{{ $farmLocation }}</strong>. 
                The shepherd has verified the fresh harvest and your items are now queued for cold-chain packaging and dispatch.
            </p>

            @if(!empty($note))
                <div style="background: #FFF9E6; border-left: 4px solid #E0A93B; border-radius: 8px; padding: 12px 16px; margin: 16px 0; font-size: 14px; color: #4A5568;">
                    <strong style="color: #976510;">Message from Farmer:</strong>
                    <div style="margin-top: 4px;">{{ $note }}</div>
                </div>
            @endif

            <!-- Order Meta Box -->
            <div class="order-meta">
                <div class="meta-row">
                    <span class="meta-label">Sub-Order Number:</span>
                    <span class="meta-val">#{{ $order->order_no }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Farm / Producer:</span>
                    <span class="meta-val">{{ $farmName }} ({{ $farmLocation }})</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Current Status:</span>
                    <span class="meta-val">
                        <span class="badge-status">&#9679; Confirmed by Farm</span>
                    </span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Payment Status:</span>
                    <span class="meta-val" style="color: {{ $order->payment_status === 'paid' ? '#2F5D3A' : '#E0A93B' }};">
                        {{ strtoupper($order->payment_status) }}
                    </span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Delivery Destination:</span>
                    <span class="meta-val">{{ $shippingAddress }}{{ $shippingArea ? ', ' . $shippingArea : '' }}, {{ $shippingCity }}</span>
                </div>
            </div>

            <!-- Items Table -->
            <h3 style="font-family: Georgia, serif; color: #1F3A27; margin: 24px 0 10px; font-size: 17px; border-bottom: 1px solid #ECE6D6; padding-bottom: 8px;">
                Confirmed Items
            </h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Unit Price</th>
                        <th class="price-col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                                @if($item->variant)
                                    <div style="font-size: 12px; color: #6B756D;">{{ $item->variant->name ?? $item->variant->weight }}</div>
                                @endif
                                @if($item->product && $item->product->batch_no)
                                    <div style="font-size: 11px; color: #2F5D3A;">Batch: {{ $item->product->batch_no }}</div>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: 600;">{{ $item->quantity }}</td>
                            <td style="text-align: right; color: #6B756D;">Rs {{ number_format($item->unit_price) }}</td>
                            <td class="price-col">Rs {{ number_format($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Financial Summary -->
            <div class="summary-card">
                <div class="summary-row">
                    <span style="color: #6B756D;">Items Subtotal:</span>
                    <span style="font-weight: 600;">Rs {{ number_format($order->subtotal) }}</span>
                </div>
                <div class="summary-row">
                    <span style="color: #6B756D;">Farm Delivery Fee:</span>
                    <span style="font-weight: 600;">Rs {{ number_format($order->delivery_fee) }}</span>
                </div>
                <div class="summary-row total">
                    <span>Order Total:</span>
                    <span style="color: #2F5D3A;">Rs {{ number_format($order->total) }}</span>
                </div>
            </div>

            <!-- What Happens Next Callout -->
            <div style="background: #FAF7F0; border-radius: 12px; padding: 16px 20px; margin: 24px 0;">
                <strong style="color: #1F3A27; font-size: 14px; display: block; margin-bottom: 6px;">
                    &#128230; What Happens Next?
                </strong>
                <ol style="margin: 0; padding-left: 20px; font-size: 13px; color: #4A5568; line-height: 1.6;">
                    <li>The farmer packs your dairy in cold-chain insulated containers to preserve freshness.</li>
                    <li>A local rider will be assigned and you will receive another update when dispatched.</li>
                    <li>You can track the live dispatch progress on our platform anytime.</li>
                </ol>
            </div>

            <!-- Action Buttons -->
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ route('orders.track', $order->order_no) }}" class="cta-btn">
                    Track Live Timeline &rarr;
                </a>
                <a href="{{ route('orders.show', $order->id) }}" class="cta-btn btn-secondary">
                    View Order Details
                </a>
            </div>

            <p style="font-size: 13px; color: #6B756D; margin-top: 24px; text-align: center; line-height: 1.5;">
                Recipient Phone: <strong>{{ $shippingPhone }}</strong><br>
                For any questions regarding this harvest, please contact our support at <strong>+92 355 1234567</strong>.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} Organic Dairy Marketplace Gilgit-Baltistan &bull; Pure Mountain Organic Produce
        </div>
    </div>
</body>
</html>
