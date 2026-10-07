@extends('layouts.public')

@section('title', 'Order Placed Successfully - Organic Dairy GB')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <!-- Success Hero Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 text-center">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle p-3 shadow-sm" style="width: 80px; height: 80px;">
                            <i class="bi bi-check2-circle display-4"></i>
                        </span>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold mb-2">
                        Order Confirmed
                    </span>
                    <h2 class="font-serif fw-bold text-dark-green mb-2">Thank You for Your Order!</h2>
                    <p class="text-muted lead fs-6 mb-3">
                        Your Himalayan organic dairy order <strong>#{{ $orderGroup->group_no }}</strong> has been placed. We've notified our partner farmers in Gilgit-Baltistan to harvest and prepare your fresh dairy items.
                    </p>
                    <div class="d-inline-flex flex-wrap gap-2 justify-content-center">
                        <a href="{{ route('orders.group.invoice', $orderGroup->id) }}" target="_blank" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-printer me-1"></i> Print / Save Master Invoice
                        </a>
                        <a href="{{ route('orders.index') }}" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-box-seam me-1"></i> View My Orders
                        </a>
                        <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                            <i class="bi bi-basket me-1"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>

            <!-- Order Details Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold font-serif text-dark-green">
                        <i class="bi bi-receipt-cutoff text-gold me-2"></i> Order Breakdown
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('orders.group.invoice', $orderGroup->id) }}" target="_blank" class="btn btn-outline-darkgreen btn-sm rounded-pill px-3">
                            <i class="bi bi-printer me-1"></i> Master Receipt
                        </a>
                        <span class="badge bg-light text-dark border font-monospace">
                            #{{ $orderGroup->group_no }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    
                    @php
                        $payment = $orderGroup->payment;
                    @endphp

                    <!-- Payment Status Notification Banners -->
                    @if($payment && $payment->isUnderReview())
                        <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-4 p-4 d-flex gap-3 align-items-center">
                            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                <i class="bi bi-hourglass-split fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1">Online Payment Under Admin Verification</h6>
                                <p class="small text-muted mb-0">
                                    We have received your payment proof via <strong>{{ ucfirst(str_replace('_', ' ', $payment->payment_channel ?? 'Bank Transfer')) }}</strong> with Transaction ID: <strong class="text-dark font-monospace">{{ $payment->transaction_id ?? $payment->gateway_ref }}</strong>. The administration team will verify and approve your order shortly.
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('checkout.payment', $orderGroup->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1.5 fw-semibold text-nowrap">
                                    <i class="bi bi-pencil me-1"></i> Update Proof
                                </a>
                            </div>
                        </div>
                    @elseif($payment && $payment->isRejected())
                        <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4 p-4 d-flex gap-3 align-items-center">
                            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Payment Verification Declined</h6>
                                <p class="small mb-0 text-danger-emphasis">
                                    <strong>Admin Reason:</strong> {{ $payment->admin_notes ?? 'Invalid transaction ID or amount mismatch.' }}
                                </p>
                            </div>
                            <div class="d-flex flex-column gap-1 flex-shrink-0">
                                <a href="{{ route('checkout.payment', $orderGroup->id) }}" class="btn btn-sm btn-danger rounded-pill px-3 py-1 fw-bold text-nowrap">
                                    <i class="bi bi-arrow-repeat me-1"></i> Re-submit TRX ID
                                </a>
                                <form action="{{ route('checkout.payment.switch-cod', $orderGroup->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-xs text-nowrap w-100">
                                        Switch to COD
                                    </button>
                                </form>
                            </div>
                        </div>
                    @elseif($payment && $payment->isPaid())
                        <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4 p-3.5 d-flex gap-3 align-items-center">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                <i class="bi bi-shield-fill-check fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-success mb-0">Payment Verified &amp; Confirmed!</h6>
                                <p class="small text-muted mb-0">
                                    Paid Rs {{ number_format($payment->amount) }} &bull; Transaction ID: <span class="font-monospace text-dark fw-semibold">{{ $payment->transaction_id ?? $payment->gateway_ref }}</span> &bull; Verified at {{ $payment->verified_at?->format('M d, Y h:i A') ?? $payment->paid_at?->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    @elseif($orderGroup->payment_method !== 'cod')
                        <div class="alert alert-info rounded-4 border-0 shadow-sm mb-4 p-3.5 d-flex gap-3 align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-clock-history fs-3 text-info"></i>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Awaiting Payment Transfer</h6>
                                    <p class="small text-muted mb-0">Please transfer Rs {{ number_format($orderGroup->grand_total) }} and submit your Transaction ID.</p>
                                </div>
                            </div>
                            <a href="{{ route('checkout.payment', $orderGroup->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold">
                                Pay Now &rarr;
                            </a>
                        </div>
                    @endif

                    <!-- Meta Grid -->
                    <div class="row g-3 bg-light rounded-3 p-3 mb-4">
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted small d-block">Order Date</span>
                            <strong class="text-dark">{{ $orderGroup->created_at->format('M d, Y') }}</strong>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted small d-block">Payment Method</span>
                            <strong class="text-dark">{{ strtoupper(str_replace('_', ' ', $orderGroup->payment_method)) }}</strong>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted small d-block">Payment Status</span>
                            @if($payment && $payment->isPaid())
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 text-xs">PAID &amp; VERIFIED</span>
                            @elseif($payment && $payment->isUnderReview())
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 text-xs">UNDER REVIEW</span>
                            @elseif($payment && $payment->isRejected())
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 text-xs">REJECTED</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 text-xs">UNPAID (COD)</span>
                            @endif
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted small d-block">Total Shipments</span>
                            <strong class="text-dark">{{ count($orderGroup->orders) }} Farm {{ Str::plural('Order', count($orderGroup->orders)) }}</strong>
                        </div>
                    </div>

                    <!-- Individual Seller Sub-Orders -->
                    <h6 class="fw-bold font-serif text-dark mb-3">Farm Sub-Orders</h6>
                    @foreach($orderGroup->orders as $order)
                        <div class="card border rounded-3 mb-3 overflow-hidden shadow-none">
                            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-dark-green">
                                        &#127969; {{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}
                                    </span>
                                    <span class="text-muted small ms-2">({{ $order->seller->sellerProfile->location ?? 'Gilgit-Baltistan' }})</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-xs btn-outline-darkgreen rounded-pill px-2.5 py-1 text-xs fw-semibold" title="Print Farm Invoice">
                                        <i class="bi bi-printer me-1"></i> Invoice
                                    </a>
                                    <span class="badge bg-light text-muted border font-monospace">{{ $order->order_no }}</span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr class="text-muted small">
                                                <th>Product</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-end">Unit Price</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($order->items as $item)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                                        @if($item->product && $item->product->batch_no)
                                                            <div class="small text-muted">Batch: {{ $item->product->batch_no }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                                    <td class="text-end">Rs {{ number_format($item->unit_price) }}</td>
                                                    <td class="text-end fw-semibold text-dark">Rs {{ number_format($item->line_total) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-light py-2 px-3 d-flex justify-content-between small text-muted">
                                <span>Subtotal: Rs {{ number_format($order->subtotal) }} + Delivery: Rs {{ number_format($order->delivery_fee) }}</span>
                                <span class="fw-bold text-dark">Sub-order Total: Rs {{ number_format($order->total) }}</span>
                            </div>
                        </div>
                    @endforeach

                    <!-- Shipping Address & Total Summary -->
                    <div class="row g-4 mt-2">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-white">
                                <h6 class="fw-bold text-dark mb-2">
                                    <i class="bi bi-geo-alt-fill text-gold me-1"></i> Delivery Address
                                </h6>
                                <p class="mb-1 fw-semibold text-dark">{{ $orderGroup->shipping_full_name }}</p>
                                <p class="mb-1 text-muted small"><i class="bi bi-telephone me-1"></i> {{ $orderGroup->shipping_phone }}</p>
                                <p class="mb-0 text-secondary small">{{ $orderGroup->shipping_address_line }}, {{ $orderGroup->shipping_area }}, <strong>{{ $orderGroup->shipping_city }}</strong>, Gilgit-Baltistan</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light border rounded-3 p-3 h-100">
                                <h6 class="fw-bold text-dark mb-2">Order Summary</h6>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Items Subtotal:</span>
                                    <span class="fw-semibold">Rs {{ number_format($orderGroup->subtotal) }}</span>
                                </div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Delivery Charges ({{ count($orderGroup->orders) }} shipments):</span>
                                    <span class="fw-semibold">Rs {{ number_format($orderGroup->delivery_total) }}</span>
                                </div>
                                @if($orderGroup->discount > 0)
                                    <div class="d-flex justify-content-between small mb-1 text-success">
                                        <span>Discount:</span>
                                        <span class="fw-semibold">- Rs {{ number_format($orderGroup->discount) }}</span>
                                    </div>
                                @endif
                                <hr class="my-2 opacity-25">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark">Grand Total:</span>
                                    <span class="fs-5 fw-bold text-primary font-serif">Rs {{ number_format($orderGroup->grand_total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
