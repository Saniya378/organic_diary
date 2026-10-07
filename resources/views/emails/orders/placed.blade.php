<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation #{{ $orderGroup->group_no }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAF7F0; margin: 0; padding: 24px; color: #1F2A22; }
        .email-container { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #ECE6D6; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .header { background: #1F3A27; color: #ffffff; padding: 32px 28px; text-align: center; }
        .header h1 { margin: 0 0 6px 0; font-family: Georgia, serif; font-size: 24px; color: #ffffff; }
        .header p { margin: 0; color: #E0A93B; font-size: 14px; font-weight: 500; letter-spacing: 0.5px; }
        .content { padding: 28px; }
        .badge-success { display: inline-block; background-color: #E8F5E9; color: #2F5D3A; font-weight: bold; font-size: 13px; padding: 4px 12px; border-radius: 20px; }
        .badge-pending { display: inline-block; background-color: #FFF8E1; color: #E0A93B; font-weight: bold; font-size: 13px; padding: 4px 12px; border-radius: 20px; }
        .order-meta { background: #F6F4EB; border-radius: 12px; padding: 16px 20px; margin: 20px 0; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .meta-row:last-child { margin-bottom: 0; }
        .meta-label { color: #6B756D; }
        .meta-val { font-weight: 600; color: #1F2A22; }
        .section-title { font-family: Georgia, serif; font-size: 18px; color: #1F3A27; margin: 24px 0 12px 0; border-bottom: 1px solid #ECE6D6; padding-bottom: 8px; }
        .seller-block { margin-bottom: 20px; background: #ffffff; border: 1px solid #ECE6D6; border-radius: 12px; padding: 16px; }
        .seller-name { font-weight: bold; color: #2F5D3A; font-size: 15px; margin-bottom: 10px; }
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
            <h1>Organic Dairy Gilgit-Baltistan</h1>
            <p>100% PURE &bull; MOUNTAIN HARVESTED &bull; FARM TO DOORSTEP</p>
        </div>

        <div class="content">
            <h2 style="font-family: Georgia, serif; color: #1F3A27; margin-top: 0;">Order Confirmation</h2>
            <p style="font-size: 15px; line-height: 1.5; color: #1F2A22;">
                Dear <strong>{{ $orderGroup->shipping_full_name }}</strong>,<br>
                Thank you for choosing Organic Dairy! We have received your order <strong>#{{ $orderGroup->group_no }}</strong> and our partner Himalayan farmers have been notified to prepare your fresh organic produce.
            </p>

            <div class="order-meta">
                <div class="meta-row">
                    <span class="meta-label">Order Group:</span>
                    <span class="meta-val">#{{ $orderGroup->group_no }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Date Placed:</span>
                    <span class="meta-val">{{ $orderGroup->created_at->format('M d, Y - h:i A') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Payment Method:</span>
                    <span class="meta-val">{{ strtoupper($orderGroup->payment_method) }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Payment Status:</span>
                    <span class="meta-val">
                        @if($orderGroup->payment && $orderGroup->payment->status === 'paid')
                            <span class="badge-success">PAID</span>
                        @else
                            <span class="badge-pending">CASH ON DELIVERY / UNPAID</span>
                        @endif
                    </span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Delivery Address:</span>
                    <span class="meta-val">{{ $orderGroup->shipping_address_line }}, {{ $orderGroup->shipping_area }}, {{ $orderGroup->shipping_city }} ({{ $orderGroup->shipping_phone }})</span>
                </div>
            </div>

            <div class="section-title">Items in Your Order</div>

            @foreach($orderGroup->orders as $order)
                <div class="seller-block">
                    <div class="seller-name">
                        &#127969; Farm: {{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }} 
                        <span style="font-size: 12px; color: #6B756D; font-weight: normal;">(Order #{{ $order->order_no }})</span>
                    </div>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th style="text-align: center;">Qty</th>
                                <th style="text-align: right;">Unit Price</th>
                                <th class="price-col">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product_name }}</strong>
                                    </td>
                                    <td style="text-align: center;">{{ $item->quantity }}</td>
                                    <td style="text-align: right;">Rs {{ number_format($item->unit_price) }}</td>
                                    <td class="price-col">Rs {{ number_format($item->line_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

            <div class="summary-card">
                <div class="summary-row">
                    <span style="color: #6B756D;">Items Subtotal:</span>
                    <span style="font-weight: 600;">Rs {{ number_format($orderGroup->subtotal) }}</span>
                </div>
                <div class="summary-row">
                    <span style="color: #6B756D;">Delivery Fee ({{ count($orderGroup->orders) }} farm shipments):</span>
                    <span style="font-weight: 600;">Rs {{ number_format($orderGroup->delivery_total) }}</span>
                </div>
                @if($orderGroup->discount > 0)
                    <div class="summary-row">
                        <span style="color: #2F5D3A;">Discount:</span>
                        <span style="font-weight: 600; color: #2F5D3A;">- Rs {{ number_format($orderGroup->discount) }}</span>
                    </div>
                @endif
                <div class="summary-row total">
                    <span>Grand Total:</span>
                    <span style="color: #2F5D3A;">Rs {{ number_format($orderGroup->grand_total) }}</span>
                </div>
            </div>

            <p style="font-size: 13px; color: #6B756D; margin-top: 24px; line-height: 1.5;">
                Need help with your order? Reply to this email or reach our Gilgit-Baltistan customer care line at <strong>+92 355 1234567</strong>.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Organic Dairy Gilgit-Baltistan &bull; Sustainable Himalayan Agriculture &bull; All Rights Reserved.
        </div>
    </div>
</body>
</html>
