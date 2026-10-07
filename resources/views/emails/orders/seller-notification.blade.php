<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Farm Order #{{ $order->order_no }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAF7F0; margin: 0; padding: 24px; color: #1F2A22; }
        .email-container { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #ECE6D6; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .header { background: #1F3A27; color: #ffffff; padding: 28px; text-align: center; }
        .header h1 { margin: 0 0 6px 0; font-family: Georgia, serif; font-size: 22px; color: #ffffff; }
        .header p { margin: 0; color: #E0A93B; font-size: 13px; font-weight: 500; }
        .content { padding: 28px; }
        .badge-placed { display: inline-block; background-color: #E8F5E9; color: #2F5D3A; font-weight: bold; font-size: 13px; padding: 4px 12px; border-radius: 20px; }
        .order-meta { background: #F6F4EB; border-radius: 12px; padding: 16px 20px; margin: 20px 0; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .meta-row:last-child { margin-bottom: 0; }
        .meta-label { color: #6B756D; }
        .meta-val { font-weight: 600; color: #1F2A22; }
        .section-title { font-family: Georgia, serif; font-size: 18px; color: #1F3A27; margin: 24px 0 12px 0; border-bottom: 1px solid #ECE6D6; padding-bottom: 8px; }
        .items-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .items-table th { text-align: left; padding: 8px 4px; color: #6B756D; border-bottom: 1px solid #ECE6D6; font-size: 12px; text-transform: uppercase; }
        .items-table td { padding: 10px 4px; border-bottom: 1px solid #F0ECE1; }
        .items-table tr:last-child td { border-bottom: none; }
        .price-col { text-align: right; font-weight: 600; }
        .summary-card { background: #FAF7F0; border-radius: 12px; padding: 16px 20px; margin-top: 24px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .summary-row.total { border-top: 1px solid #ECE6D6; padding-top: 10px; font-size: 16px; font-weight: bold; color: #1F3A27; }
        .footer { background: #FAF7F0; text-align: center; padding: 20px; font-size: 12px; color: #6B756D; border-top: 1px solid #ECE6D6; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Organic Dairy Seller Notification</h1>
            <p>NEW ORDER RECEIVED &bull; PREPARE FOR PACKING</p>
        </div>

        <div class="content">
            <h2 style="font-family: Georgia, serif; color: #1F3A27; margin-top: 0;">New Order #{{ $order->order_no }}</h2>
            <p style="font-size: 15px; line-height: 1.5; color: #1F2A22;">
                Hello <strong>{{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}</strong>,<br>
                A customer has placed an order for your farm products. Please review the batch details and start packing in insulated temperature-safe storage.
            </p>

            <div class="order-meta">
                <div class="meta-row">
                    <span class="meta-label">Sub-Order No:</span>
                    <span class="meta-val">#{{ $order->order_no }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Customer Name:</span>
                    <span class="meta-val">{{ $order->orderGroup->shipping_full_name }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Customer Phone:</span>
                    <span class="meta-val">{{ $order->orderGroup->shipping_phone }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Destination City:</span>
                    <span class="meta-val">{{ $order->orderGroup->shipping_city }} ({{ $order->orderGroup->shipping_area }})</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Payment Status:</span>
                    <span class="meta-val">{{ strtoupper($order->payment_status) }} ({{ strtoupper($order->orderGroup->payment_method) }})</span>
                </div>
            </div>

            <div class="section-title">Ordered Items to Fulfill</div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product & Variant</th>
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
                            </td>
                            <td style="text-align: center; font-weight: bold; color: #2F5D3A;">&times; {{ $item->quantity }}</td>
                            <td style="text-align: right;">Rs {{ number_format($item->unit_price) }}</td>
                            <td class="price-col">Rs {{ number_format($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="summary-card">
                <div class="summary-row">
                    <span style="color: #6B756D;">Items Subtotal:</span>
                    <span style="font-weight: 600;">Rs {{ number_format($order->subtotal) }}</span>
                </div>
                <div class="summary-row">
                    <span style="color: #6B756D;">Platform Commission (5%):</span>
                    <span style="font-weight: 600; color: #6B756D;">Rs {{ number_format($order->commission) }}</span>
                </div>
                <div class="summary-row">
                    <span style="color: #6B756D;">Delivery Fee:</span>
                    <span style="font-weight: 600;">Rs {{ number_format($order->delivery_fee) }}</span>
                </div>
                <div class="summary-row total">
                    <span>Seller Net Payout:</span>
                    <span style="color: #2F5D3A;">Rs {{ number_format($order->subtotal - $order->commission) }}</span>
                </div>
            </div>

            <p style="font-size: 13px; color: #6B756D; margin-top: 24px; line-height: 1.5;">
                Please log into your seller portal to update the order status to <em>Packing</em> and hand over to our Gilgit-Baltistan courier rider once sealed.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Organic Dairy Seller Network &bull; Gilgit-Baltistan
        </div>
    </div>
</body>
</html>
