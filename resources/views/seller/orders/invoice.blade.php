<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing Slip & Invoice #{{ $order->order_no }} - {{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        :root {
            --od-green: #1F3A27;
            --od-gold: #E0A93B;
            --od-bg: #FAF7F0;
            --od-border: #E5DFC9;
        }

        body {
            background-color: #f4f6f8;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #212529;
            margin: 0;
            padding: 20px 0;
        }

        .slip-card {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            border: 1px solid var(--od-border);
            padding: 40px;
        }

        .slip-header {
            border-bottom: 2px solid var(--od-border);
            padding-bottom: 20px;
            margin-bottom: 24px;
        }

        .table-slip {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .table-slip th {
            background-color: var(--od-bg);
            color: var(--od-green);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border-top: 1px solid var(--od-border);
            border-bottom: 1px solid var(--od-border);
        }

        .table-slip td {
            padding: 12px 14px;
            border-bottom: 1px solid #f0ece1;
            font-size: 0.92rem;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .slip-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <!-- Action Bar (Hidden on Print) -->
    <div class="container mb-4 no-print" style="max-width: 820px;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 bg-white p-3 rounded-4 shadow-sm border">
            <a href="{{ route('seller.orders.show', $order->id) }}" class="btn btn-outline-dark rounded-pill px-3 py-1.5 fw-semibold small">
                <i class="bi bi-arrow-left me-1"></i> Back to Order
            </a>
            <div class="d-flex align-items-center gap-2">
                <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 py-1.5 fw-bold shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Print Packing Slip & Dispatch Receipt
                </button>
            </div>
        </div>
    </div>

    <!-- Printable Slip Container -->
    <div class="slip-card">
        <!-- Header -->
        <div class="slip-header d-flex justify-content-between align-items-start">
            <div>
                <h3 class="heading-serif fw-bold text-dark-green mb-1">{{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}</h3>
                <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $order->seller->sellerProfile->location ?? 'Gilgit-Baltistan' }} &bull; Ph: {{ $order->seller->phone }}</div>
                <small class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 mt-2">
                    <i class="bi bi-patch-check-fill me-1"></i> Verified Himalayan Farm Seller
                </small>
            </div>

            <div class="text-end">
                <h4 class="heading-serif fw-bold text-dark mb-0">PACKING SLIP</h4>
                <div class="text-muted small">Order Reference:</div>
                <div class="fs-4 font-monospace fw-bold text-dark-green">#{{ $order->order_no }}</div>
                <div class="badge bg-light text-dark border px-3 py-1 rounded-pill mt-1 text-uppercase fw-bold" style="font-size: 0.7rem;">
                    Status: {{ str_replace('_', ' ', $order->status) }}
                </div>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-4 border h-100">
                    <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <i class="bi bi-truck text-primary me-1"></i> Deliver To (Buyer Details)
                    </div>
                    <div class="fw-bold text-dark fs-6">{{ $order->orderGroup->shipping_full_name ?? $order->buyer->name }}</div>
                    <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $order->orderGroup->shipping_phone ?? $order->buyer->phone }}</div>
                    <div class="small text-dark mt-1">
                        <strong>Address:</strong> {{ $order->orderGroup->shipping_address_line }}, {{ $order->orderGroup->shipping_area }}<br>
                        <strong>City:</strong> {{ $order->orderGroup->shipping_city }}
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 bg-light rounded-4 border h-100">
                    <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <i class="bi bi-box-seam text-success me-1"></i> Dispatch & Logistics
                    </div>
                    <div class="small d-flex justify-content-between mb-1">
                        <span class="text-muted">Order Date:</span>
                        <span class="fw-semibold">{{ $order->created_at->format('M d, Y - h:i A') }}</span>
                    </div>
                    <div class="small d-flex justify-content-between mb-1">
                        <span class="text-muted">Payment Type:</span>
                        <span class="fw-bold text-uppercase">{{ $order->orderGroup->payment_method ?? 'COD' }} ({{ $order->payment_status }})</span>
                    </div>
                    <div class="small d-flex justify-content-between mb-1">
                        <span class="text-muted">Assigned Rider:</span>
                        <span class="fw-bold text-dark">{{ $order->rider_name ? "{$order->rider_name} ({$order->rider_phone})" : 'Not assigned yet' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Packing Items Table -->
        <h6 class="heading-serif fw-bold text-dark mb-2">Package Items Checklist</h6>
        <table class="table-slip">
            <thead>
                <tr>
                    <th style="width: 8%; text-align: center;">Packed</th>
                    <th style="width: 52%;">Item Description</th>
                    <th style="width: 15%; text-align: center;">Qty</th>
                    <th style="width: 12%; text-align: right;">Unit Price</th>
                    <th style="width: 13%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td class="text-center">
                            <span style="display: inline-block; width: 18px; height: 18px; border: 2px solid #6c757d; border-radius: 4px;"></span>
                        </td>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                            @if($item->variant)
                                <div class="small text-muted">Variant: {{ $item->variant->label }}</div>
                            @endif
                        </td>
                        <td class="text-center fw-bold fs-6">{{ $item->quantity }}</td>
                        <td class="text-end text-muted">Rs {{ number_format($item->unit_price) }}</td>
                        <td class="text-end fw-bold">Rs {{ number_format($item->line_total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Earnings Breakdown -->
        <div class="row g-3 justify-content-end mb-4">
            <div class="col-md-5">
                <div class="bg-light p-3 rounded-4 border">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Items Total:</span>
                        <span class="fw-semibold">Rs {{ number_format($order->subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Delivery Fee:</span>
                        <span class="fw-semibold">Rs {{ number_format($order->delivery_fee) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1 text-danger">
                        <span>Platform Commission ({{ (int) round(($order->commission / max(1, $order->subtotal)) * 100) }}%):</span>
                        <span>- Rs {{ number_format($order->commission) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-success">
                        <span>Seller Net Payout:</span>
                        <span>Rs {{ number_format($order->subtotal - $order->commission) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dispatch Signatures -->
        <div class="row g-4 pt-4 border-top border-2 mt-4">
            <div class="col-6">
                <div class="border-top border-dark pt-1 small text-muted text-center" style="margin-top: 40px;">
                    Farm Quality Checker / Packer Signature
                </div>
            </div>
            <div class="col-6">
                <div class="border-top border-dark pt-1 small text-muted text-center" style="margin-top: 40px;">
                    Delivery Courier / Rider Signature
                </div>
            </div>
        </div>
    </div>

</body>
</html>
