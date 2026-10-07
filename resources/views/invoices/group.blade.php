<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $orderGroup->group_no }} - Organic Dairy Gilgit-Baltistan</title>
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

        .invoice-card {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            border: 1px solid var(--od-border);
            padding: 40px;
        }

        .invoice-header {
            border-bottom: 2px solid var(--od-border);
            padding-bottom: 24px;
            margin-bottom: 28px;
        }

        .invoice-brand-badge {
            background-color: var(--od-green);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            border-radius: 12px;
        }

        .meta-box {
            background: var(--od-bg);
            border: 1px solid var(--od-border);
            border-radius: 12px;
            padding: 16px 20px;
        }

        .table-invoice {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0 24px;
        }

        .table-invoice th {
            background-color: var(--od-bg);
            color: var(--od-green);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            border-top: 1px solid var(--od-border);
            border-bottom: 1px solid var(--od-border);
        }

        .table-invoice td {
            padding: 14px 16px;
            border-bottom: 1px solid #f0ece1;
            font-size: 0.92rem;
        }

        .seal-stamp {
            border: 2px dashed #2F5D3A;
            border-radius: 12px;
            padding: 12px 18px;
            color: #2F5D3A;
            display: inline-block;
            background: #EBF5EE;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .invoice-card {
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
    <div class="container mb-4 no-print" style="max-width: 860px;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 bg-white p-3 rounded-4 shadow-sm border">
            <a href="{{ url()->previous() ?: route('orders.index') }}" class="btn btn-outline-dark rounded-pill px-3 py-1.5 fw-semibold small">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <div class="d-flex align-items-center gap-2">
                <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 py-1.5 fw-bold shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Print Multi-Vendor Receipt
                </button>
            </div>
        </div>
    </div>

    <!-- Printable Group Invoice Container -->
    <div class="invoice-card">
        <!-- Header -->
        <div class="invoice-header d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="invoice-brand-badge mb-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-dark" style="width: 32px; height: 32px; background-color: var(--od-gold);">
                        <i class="bi bi-flower1 fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold heading-serif text-white fs-5 lh-1">Organic Dairy</div>
                        <small class="text-warning text-uppercase" style="font-size: 0.62rem; letter-spacing: 1.5px;">Gilgit-Baltistan</small>
                    </div>
                </div>
                <p class="text-muted small mb-0 mt-2">
                    High-Altitude Certified Organic Cooperative<br>
                    Hunza &bull; Skardu &bull; Ghizer Valley &bull; Pakistan<br>
                    <i class="bi bi-globe me-1"></i> www.organicdairy.pk &bull; <i class="bi bi-envelope me-1"></i> support@organicdairy.pk
                </p>
            </div>

            <div class="text-end">
                <h2 class="heading-serif fw-bold text-dark-green mb-1" style="letter-spacing: -0.5px;">ORDER GROUP RECEIPT</h2>
                <div class="text-muted small">Master Group Reference:</div>
                <div class="fs-4 font-monospace fw-bold text-dark">#{{ $orderGroup->group_no }}</div>
                <div class="badge {{ $orderGroup->payment && $orderGroup->payment->status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-1 rounded-pill mt-1 text-uppercase fw-bold" style="font-size: 0.72rem;">
                    Payment: {{ $orderGroup->payment->status ?? 'Pending' }}
                </div>
            </div>
        </div>

        <!-- Meta Details (Billed To, Order Info) -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="meta-box h-100">
                    <div class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <i class="bi bi-person-fill text-success me-1"></i> Shipping & Delivery Details
                    </div>
                    <div class="fw-bold text-dark">{{ $orderGroup->shipping_full_name }}</div>
                    <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $orderGroup->shipping_phone }}</div>
                    <div class="small text-dark mt-1">
                        <strong>Address:</strong> {{ $orderGroup->shipping_address_line }}, {{ $orderGroup->shipping_area }}<br>
                        <strong>City/Valley:</strong> {{ $orderGroup->shipping_city }}
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="meta-box h-100">
                    <div class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <i class="bi bi-info-circle-fill text-primary me-1"></i> Order & Payment Details
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Placed Date:</span>
                        <span class="fw-semibold">{{ $orderGroup->created_at->format('M d, Y - h:i A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Payment Channel:</span>
                        <span class="fw-bold text-dark text-uppercase">{{ $orderGroup->payment_method }}</span>
                    </div>
                    @if($orderGroup->payment && $orderGroup->payment->transaction_id)
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">TRX Reference:</span>
                            <span class="font-monospace fw-bold text-dark">{{ $orderGroup->payment->transaction_id }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Distinct Farm Sellers:</span>
                        <span class="fw-semibold">{{ $orderGroup->orders->count() }} Producers</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Breakdown by Seller -->
        @foreach($orderGroup->orders as $order)
            <div class="p-3 mb-4 rounded-4 border bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill me-2">
                            #{{ $order->order_no }}
                        </span>
                        <strong class="text-dark">{{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}</strong>
                        <small class="text-muted ms-2">({{ $order->seller->sellerProfile->location ?? 'Gilgit-Baltistan' }})</small>
                    </div>
                    <span class="badge bg-light text-dark border rounded-pill text-capitalize">
                        {{ str_replace('_', ' ', $order->status) }}
                    </span>
                </div>

                <table class="table-invoice mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60%;">Product</th>
                            <th style="width: 15%; text-align: center;">Qty</th>
                            <th style="width: 12%; text-align: right;">Price</th>
                            <th style="width: 13%; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                    @if($item->variant)
                                        <small class="text-muted">Variant: {{ $item->variant->label }}</small>
                                    @endif
                                </td>
                                <td class="text-center fw-semibold">{{ $item->quantity }}</td>
                                <td class="text-end text-muted">Rs {{ number_format($item->unit_price) }}</td>
                                <td class="text-end fw-bold text-dark">Rs {{ number_format($item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        <!-- Master Totals -->
        <div class="row g-3 justify-content-end mb-4">
            <div class="col-md-5">
                <div class="bg-light p-3 rounded-4 border">
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Total Products Subtotal:</span>
                        <span class="fw-semibold">Rs {{ number_format($orderGroup->subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Combined Delivery Total:</span>
                        <span class="fw-semibold">Rs {{ number_format($orderGroup->delivery_total) }}</span>
                    </div>
                    @if($orderGroup->discount > 0)
                        <div class="d-flex justify-content-between small mb-2 text-success">
                            <span>Promotional Discount:</span>
                            <span>- Rs {{ number_format($orderGroup->discount) }}</span>
                        </div>
                    @endif
                    <hr class="my-2 border-secondary border-opacity-25">
                    <div class="d-flex justify-content-between fs-5 fw-bold text-dark-green">
                        <span>Grand Total:</span>
                        <span style="color: var(--od-green);">Rs {{ number_format($orderGroup->grand_total) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Seal -->
        <div class="pt-4 border-top border-2 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="seal-stamp mb-2">
                    <i class="bi bi-shield-check me-1"></i> 100% Certified Mountain Harvested
                </div>
                <p class="text-muted small mb-0" style="max-width: 480px; font-size: 0.75rem;">
                    Thank you for supporting traditional mountain shepherd families across Gilgit-Baltistan!
                </p>
            </div>

            <div class="text-end small text-muted">
                <div class="fw-semibold text-dark">Official Platform Master Receipt</div>
                <div>Generated on {{ now()->format('M d, Y - h:i A') }}</div>
            </div>
        </div>
    </div>

</body>
</html>
