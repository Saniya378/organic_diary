@extends('layouts.public')

@section('title', 'Order Details #' . $order->order_no . ' - Organic Dairy GB')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" class="text-decoration-none">My Orders</a></li>
            <li class="breadcrumb-item active" aria-current="page">#{{ $order->order_no }}</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success rounded-4 shadow-sm mb-4 border-0">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger rounded-4 shadow-sm mb-4 border-0">
            <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row g-4">
        <!-- Left: Order Details & Items -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom border-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="text-muted small">Sub-Order Reference</span>
                        <h4 class="mb-0 font-serif text-dark-green font-monospace fw-bold">#{{ $order->order_no }}</h4>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-outline-success rounded-pill btn-sm px-3 fw-semibold">
                            <i class="bi bi-printer me-1"></i> Print Invoice
                        </a>
                        @if($order->order_group_id)
                            <a href="{{ route('orders.group.invoice', $order->order_group_id) }}" target="_blank" class="btn btn-outline-secondary rounded-pill btn-sm px-3 fw-semibold">
                                <i class="bi bi-receipt me-1"></i> Master Receipt
                            </a>
                        @endif
                        @php
                            $statusBadges = [
                                'pending_payment' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                'placed' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                'new' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                'confirmed' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                'packing' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
                                'in_transit' => 'bg-primary text-white',
                                'delivered' => 'bg-success-subtle text-success border border-success-subtle',
                                'cancelled' => 'bg-danger-subtle text-danger border border-danger-subtle',
                            ];
                            $badgeClass = $statusBadges[$order->status] ?? 'bg-light text-dark';
                        @endphp
                        <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill fs-6">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if($order->status === 'cancelled')
                        @if($order->isCancelledBySeller())
                            <div class="alert alert-danger rounded-4 p-4 border-0 shadow-sm mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center p-2" style="width: 44px; height: 44px; min-width: 44px;">
                                        <i class="bi bi-shop-window fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1 text-danger">Order Cancelled by Seller / Farmer</h5>
                                        <p class="mb-1 text-dark">This order was cancelled by <strong>{{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}</strong>.</p>
                                        <div class="small text-muted"><strong>Seller's Reason:</strong> {{ $order->cancel_reason ?? 'Not specified' }}</div>
                                    </div>
                                </div>
                            </div>
                        @elseif($order->isCancelledByAdmin())
                            <div class="alert alert-danger rounded-4 p-4 border-0 shadow-sm mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center p-2" style="width: 44px; height: 44px; min-width: 44px;">
                                        <i class="bi bi-shield-x fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1 text-danger">Order Cancelled by Admin</h5>
                                        <p class="mb-1 text-dark">This order was cancelled by platform administration.</p>
                                        <div class="small text-muted"><strong>Reason:</strong> {{ $order->cancel_reason ?? 'Not specified' }}</div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary rounded-4 p-4 border-0 shadow-sm mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center p-2" style="width: 44px; height: 44px; min-width: 44px;">
                                        <i class="bi bi-x-circle-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1 text-dark">Order Cancelled by Customer</h5>
                                        <div class="small text-muted"><strong>Reason:</strong> {{ $order->cancel_reason ?? 'Customer request' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Farm Seller Information Card -->
                    <div class="p-3 bg-light rounded-3 border mb-4 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Himalayan Producer Farm</div>
                            <h6 class="fw-bold text-dark-green font-serif mb-0">
                                &#127969; {{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}
                            </h6>
                            <div class="small text-secondary"><i class="bi bi-geo-alt me-1"></i> {{ $order->seller->sellerProfile->location ?? 'Gilgit-Baltistan' }}</div>
                        </div>
                        <a href="{{ route('farmers.show', $order->seller->id) }}" class="btn btn-sm btn-outline-darkgreen rounded-pill px-3">
                            View Farm Profile
                        </a>
                    </div>

                    <!-- Items Table -->
                    <h6 class="fw-bold font-serif text-dark mb-3">Purchased Items Snapshot</h6>
                    <div class="table-responsive mb-4">
                        <table class="table align-middle border-light">
                            <thead class="bg-light">
                                <tr class="text-muted small">
                                    <th>Item</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Line Total</th>
                                    @if($order->status === 'delivered')
                                        <th class="text-end">Verified Review</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    @php
                                        $itemReview = $order->reviews->firstWhere('product_id', $item->product_id);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                            @if($item->product && $item->product->batch_no)
                                                <div class="small text-muted">Batch: {{ $item->product->batch_no }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold text-dark-green">{{ $item->quantity }}</td>
                                        <td class="text-end">Rs {{ number_format($item->unit_price) }}</td>
                                        <td class="text-end fw-bold text-dark">Rs {{ number_format($item->line_total) }}</td>
                                        @if($order->status === 'delivered')
                                            <td class="text-end">
                                                @if($itemReview)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill">
                                                        <i class="bi bi-patch-check-fill me-1"></i> Reviewed ({{ $itemReview->rating }}★)
                                                    </span>
                                                @elseif($item->product)
                                                    <a href="{{ route('shop.show', $item->product->slug) }}#reviews-section" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 text-xs fw-semibold">
                                                        <i class="bi bi-star me-1"></i> Write Review
                                                    </a>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Status History Audit Log -->
                    <h6 class="fw-bold font-serif text-dark mb-3">Status Updates & History</h6>
                    <div class="list-group list-group-flush border rounded-3 mb-3">
                        @forelse($order->statusHistories as $history)
                            <div class="list-group-item d-flex justify-content-between align-items-start py-3">
                                <div class="me-3">
                                    <div class="fw-bold text-dark">
                                        {{ ucfirst(str_replace('_', ' ', $history->status)) }}
                                    </div>
                                    <div class="small text-muted">{{ $history->note ?? 'Status updated by system.' }}</div>
                                    <div class="small text-secondary">Updated by: <strong>{{ $history->changedByUser->name ?? 'System' }}</strong></div>
                                </div>
                                <span class="badge bg-light text-muted border small">
                                    {{ $history->created_at->format('M d, Y - h:i A') }}
                                </span>
                            </div>
                        @empty
                            <div class="list-group-item text-muted small py-3">No status history records available.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Actions, Shipping & Summary -->
        <div class="col-lg-4">
            <!-- Action Buttons -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 d-grid gap-2">
                    <a href="{{ route('orders.track', $order->order_no) }}" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-geo-alt-fill fs-5 text-gold"></i> Live Order Tracker
                    </a>

                    @if($canCancel)
                        <button type="button" class="btn btn-outline-danger rounded-pill py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#cancelDetailModal">
                            <i class="bi bi-x-circle me-1"></i> Cancel Order
                        </button>

                        <!-- Cancel Modal -->
                        <div class="modal fade" id="cancelDetailModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title font-serif text-danger">Cancel Order #{{ $order->order_no }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body text-start">
                                            <p class="small text-muted mb-3">
                                                Are you sure you want to cancel this order? The reserved dairy items will be released back to the farm's live inventory.
                                            </p>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Reason for Cancellation</label>
                                                <select name="cancel_reason" class="form-select form-select-sm">
                                                    <option value="Changed my mind">Changed my mind</option>
                                                    <option value="Ordered by mistake">Ordered by mistake</option>
                                                    <option value="Need to change delivery address">Need to change delivery address</option>
                                                    <option value="Delivery time is too late">Delivery time is too late</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top bg-light">
                                            <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Keep Order</button>
                                            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold">Confirm Cancellation</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @php
                $orderPayment = $order->orderGroup->payment;
            @endphp

            <!-- Payment Status Card -->
            @if($orderPayment)
                <div class="card border-0 shadow-sm rounded-4 mb-4 {{ $orderPayment->isUnderReview() ? 'border border-warning' : ($orderPayment->isRejected() ? 'border border-danger' : '') }}">
                    <div class="card-header bg-white py-3 px-4 border-bottom border-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold font-serif text-dark-green">
                            <i class="bi bi-credit-card-2-front text-gold me-1"></i> Payment Status
                        </h6>
                        @if($orderPayment->isPaid())
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-xs">Paid &amp; Verified</span>
                        @elseif($orderPayment->isUnderReview())
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 text-xs fw-bold">Under Review</span>
                        @elseif($orderPayment->isRejected())
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 text-xs fw-bold">Rejected</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 text-xs">Unpaid</span>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        @if($orderPayment->isUnderReview())
                            <div class="alert alert-warning rounded-3 border-0 small mb-3">
                                <i class="bi bi-hourglass-split me-1"></i> <strong>Payment Under Review:</strong> The farm/seller is reviewing Transaction ID <code class="text-dark">{{ $orderPayment->transaction_id ?? $orderPayment->gateway_ref }}</code>. Your order will be confirmed upon verification.
                            </div>
                        @elseif($orderPayment->isRejected())
                            <div class="alert alert-danger rounded-3 border-0 small mb-3">
                                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Payment Verification Declined:</div>
                                <div>{{ $orderPayment->admin_notes ?? 'Invalid transaction details.' }}</div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm w-100 rounded-pill fw-bold shadow-sm mb-2" data-bs-toggle="modal" data-bs-target="#buyerResubmitModal">
                                <i class="bi bi-arrow-repeat me-1"></i> Re-submit Transaction ID
                            </button>
                        @elseif($orderPayment->isPaid())
                            <div class="small text-muted mb-2">
                                <i class="bi bi-check-circle-fill text-success me-1"></i> Verified on {{ $orderPayment->verified_at?->format('M d, Y h:i A') ?? $orderPayment->paid_at?->format('M d, Y') }}
                            </div>
                        @endif

                        <div class="small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Method:</span>
                                <span class="fw-semibold text-dark">{{ ucfirst(str_replace('_', ' ', $orderPayment->payment_channel ?? $order->orderGroup->payment_method)) }}</span>
                            </div>
                            @if($orderPayment->transaction_id || $orderPayment->gateway_ref)
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">TRX ID:</span>
                                    <span class="font-monospace fw-bold text-dark">{{ $orderPayment->transaction_id ?? $orderPayment->gateway_ref }}</span>
                                </div>
                            @endif
                            @if($orderPayment->sender_name)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Sender:</span>
                                    <span>{{ $orderPayment->sender_name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Re-submit Payment Modal -->
                @if($orderPayment->isRejected())
                    <div class="modal fade" id="buyerResubmitModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <form action="{{ route('orders.resubmit-payment', $order->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-header border-bottom py-3">
                                        <h5 class="modal-title font-serif fw-bold text-dark">Re-submit Payment Proof</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Payment Channel Used <span class="text-danger">*</span></label>
                                            <select name="payment_channel" class="form-select form-select-sm" required>
                                                <option value="meezan_bank">Meezan Bank Direct Transfer</option>
                                                <option value="bank_transfer">Other Bank (1Link / IBFT)</option>
                                                <option value="easypaisa">Easypaisa</option>
                                                <option value="jazzcash">JazzCash</option>
                                                <option value="other">Other Digital Channel</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Sender Name <span class="text-danger">*</span></label>
                                            <input type="text" name="sender_name" class="form-control form-control-sm" value="{{ old('sender_name', $orderPayment->sender_name ?? Auth::user()->name) }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Transaction ID (TRX ID) <span class="text-danger">*</span></label>
                                            <input type="text" name="transaction_id" class="form-control form-control-sm font-monospace" placeholder="e.g. 98472910" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Upload New Receipt Screenshot (Optional)</label>
                                            <input type="file" name="proof_image" class="form-control form-control-sm" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top py-2">
                                        <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold">Submit for Review</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Financial Breakdown -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom border-light">
                    <h6 class="mb-0 fw-bold font-serif text-dark-green">Price Summary</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Items Subtotal:</span>
                        <span class="fw-semibold">Rs {{ number_format($order->subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Delivery Fee:</span>
                        <span class="fw-semibold">Rs {{ number_format($order->delivery_fee) }}</span>
                    </div>
                    <hr class="my-2 opacity-25">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">Sub-order Total:</span>
                        <span class="fs-5 fw-bold text-primary font-serif">Rs {{ number_format($order->total) }}</span>
                    </div>
                    <div class="mt-3 small text-muted">
                        Payment: <strong>{{ strtoupper($order->orderGroup->payment_method) }}</strong> (Status: <strong>{{ strtoupper($order->payment_status) }}</strong>)
                    </div>
                </div>
            </div>

            <!-- Shipping Destination & Slot Card -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 px-4 border-bottom border-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold font-serif text-dark-green">
                        <i class="bi bi-geo-alt-fill text-gold me-1"></i> Delivery &amp; Slot
                    </h6>
                    @php
                        $slotLabels = [
                            'morning' => ['label' => '🌅 Morning (6am-9am)', 'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
                            'evening' => ['label' => '🌆 Evening (5pm-8pm)', 'class' => 'bg-info-subtle text-info-emphasis border border-info-subtle'],
                            'anytime' => ['label' => '🚚 Standard Daytime', 'class' => 'bg-light text-dark border'],
                        ];
                        $slot = $order->delivery_slot ?? ($order->orderGroup->delivery_slot ?? 'morning');
                        $slotInfo = $slotLabels[$slot] ?? $slotLabels['morning'];
                    @endphp
                    <span class="badge {{ $slotInfo['class'] }} rounded-pill px-2.5 py-1 text-xs fw-semibold">
                        {{ $slotInfo['label'] }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <strong class="text-dark d-block mb-1">{{ $order->orderGroup->shipping_full_name }}</strong>
                    <div class="small text-muted mb-2"><i class="bi bi-telephone me-1"></i> {{ $order->orderGroup->shipping_phone }}</div>
                    <div class="small text-secondary mb-3">{{ $order->orderGroup->shipping_address_line }}, {{ $order->orderGroup->shipping_area }}, <strong>{{ $order->orderGroup->shipping_city }}</strong>, Gilgit-Baltistan</div>

                    @if($order->rider_name)
                        <div class="p-2.5 bg-light rounded-3 border">
                            <div class="small fw-bold text-dark mb-1"><i class="bi bi-bicycle text-success me-1"></i> Assigned Delivery Rider:</div>
                            <div class="small text-dark fw-semibold">{{ $order->rider_name }}</div>
                            <div class="small text-muted"><a href="tel:{{ $order->rider_phone }}" class="text-decoration-none text-primary"><i class="bi bi-telephone me-1"></i> {{ $order->rider_phone }}</a></div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Live Support Card -->
            <div class="card border-0 shadow-sm rounded-4 mt-3 text-center p-3" style="background: #F4FBF6; border: 1px solid #D2EED9 !important;">
                <div class="small fw-bold text-dark mb-1"><i class="bi bi-headset me-1 text-success"></i> Need Help with this Order?</div>
                <div class="text-xs text-muted mb-2">Get instant live AI assistance or contact our support team.</div>
                <button type="button" onclick="window.openSupportChat('Track Order #{{ $order->order_no }}')" class="btn btn-success rounded-pill btn-sm fw-bold px-3 py-1.5 shadow-xs">
                    <i class="bi bi-chat-dots-fill me-1"></i> Live Chat Support
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
