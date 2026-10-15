@extends('layouts.panel')

@section('title', 'Manage Order #' . $order->order_no . ' - Farmer Portal')
@section('page_heading', 'Order Fulfillment: #' . $order->order_no)

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('seller.orders.index') }}" class="text-decoration-none">Orders</a></li>
            <li class="breadcrumb-item active" aria-current="page">#{{ $order->order_no }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left: Items, History, & Status Progression -->
        <div class="col-lg-8">
            
            <!-- Order Header Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom border-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="text-muted small">Sub-Order Reference</span>
                        <h4 class="mb-0 font-serif text-dark-green font-monospace fw-bold">#{{ $order->order_no }}</h4>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <a href="{{ route('seller.orders.invoice', $order->id) }}" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold">
                            <i class="bi bi-printer me-1"></i> Print Packing Slip / Invoice
                        </a>
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
                            Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if($order->status === 'cancelled')
                        <div class="alert alert-danger rounded-4 p-4 border-0 shadow-sm mb-4">
                            <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-x-octagon-fill fs-2 text-danger"></i>
                                <div>
                                    <h5 class="fw-bold mb-1 text-danger">This Order is Cancelled</h5>
                                    <p class="mb-1 text-dark">
                                        @if($order->isCancelledBySeller())
                                            Cancelled by you (Farm/Seller).
                                        @elseif($order->isCancelledByAdmin())
                                            Cancelled by Platform Admin.
                                        @else
                                            Cancelled by Buyer/Customer.
                                        @endif
                                    </p>
                                    <div class="small text-muted"><strong>Reason:</strong> {{ $order->cancel_reason ?? 'No specific reason provided' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Status Progression Workflow Banner -->
                    <div class="p-3 bg-light rounded-4 border mb-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <strong class="d-block text-dark font-serif">Order Progression Step:</strong>
                                @if($nextStatus)
                                    <small class="text-muted">Next available milestone: <strong class="text-primary">{{ ucfirst(str_replace('_', ' ', $nextStatus)) }}</strong></small>
                                @elseif($order->status === 'delivered')
                                    <span class="badge bg-success text-white">Completed & Delivered</span>
                                @elseif($order->status === 'cancelled')
                                    <span class="badge bg-danger text-white">Order Cancelled</span>
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @if(!in_array($order->status, ['in_transit', 'delivered', 'cancelled']))
                                    <button type="button" class="btn btn-outline-danger rounded-pill px-3 py-2 fw-semibold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#sellerCancelModal">
                                        <i class="bi bi-x-circle me-1"></i> Cancel Order
                                    </button>
                                @endif

                                @if($nextStatus)
                                    @if($nextStatus === 'in_transit')
                                        <!-- Button triggering Rider Assignment Modal -->
                                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#dispatchModal">
                                            <i class="bi bi-truck me-1"></i> Dispatch with Rider &rarr;
                                        </button>
                                    @else
                                        <form action="{{ route('seller.orders.advance-status', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="target_status" value="{{ $nextStatus }}">
                                            @if($nextStatus === 'confirmed')
                                                <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Confirm Order & Notify Buyer &rarr;
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                                    @if($nextStatus === 'packing')
                                                        <i class="bi bi-box-seam me-1"></i> Mark as Packed in Cold-Box &rarr;
                                                    @elseif($nextStatus === 'delivered')
                                                        <i class="bi bi-house-check me-1"></i> Mark as Delivered to Doorstep &rarr;
                                                    @else
                                                        {{ ucfirst(str_replace('_', ' ', $nextStatus)) }} &rarr;
                                                    @endif
                                                </button>
                                            @endif
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <h6 class="fw-bold font-serif text-dark mb-3">Ordered Dairy Items</h6>
                    <div class="table-responsive mb-4">
                        <table class="table align-middle border-light">
                            <thead class="bg-light">
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
                                            <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                            @if($item->product && $item->product->batch_no)
                                                <small class="text-muted">Batch: {{ $item->product->batch_no }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold text-dark-green">{{ $item->quantity }}</td>
                                        <td class="text-end">Rs {{ number_format($item->unit_price) }}</td>
                                        <td class="text-end fw-bold text-dark">Rs {{ number_format($item->line_total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Status History Audit -->
                    <h6 class="fw-bold font-serif text-dark mb-3">Status Audit Log</h6>
                    <div class="list-group list-group-flush border rounded-3">
                        @foreach($order->statusHistories as $history)
                            <div class="list-group-item d-flex justify-content-between align-items-start py-3">
                                <div>
                                    <strong class="text-dark">{{ ucfirst(str_replace('_', ' ', $history->status)) }}</strong>
                                    <div class="small text-muted">{{ $history->note }}</div>
                                    <div class="small text-secondary">Changed by: {{ $history->changedByUser->name ?? 'System' }}</div>
                                </div>
                                <span class="badge bg-light text-muted border small">
                                    {{ $history->created_at->format('M d, Y - h:i A') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Customer, Courier, & Payout Details -->
        <div class="col-lg-4">
            
            <!-- Buyer Payment Verification Card (Direct Seller-Buyer Settlement) -->
            @php
                $payment = $order->orderGroup->payment;
            @endphp
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden" style="border: 1px solid {{ $order->payment_status === 'under_review' ? '#F6C343' : ($order->payment_status === 'paid' ? '#D2EED9' : '#E8E2D5') }} !important;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center {{ $order->payment_status === 'under_review' ? 'bg-warning-subtle text-warning-emphasis' : ($order->payment_status === 'paid' ? 'bg-success-subtle text-success' : 'bg-light') }}">
                    <h6 class="mb-0 fw-bold font-serif">
                        <i class="bi bi-credit-card-2-front me-1"></i> Buyer Payment Verification
                    </h6>
                    <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success text-white' : ($order->payment_status === 'under_review' ? 'bg-warning text-dark' : 'bg-secondary text-white') }} rounded-pill small">
                        {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Method:</span>
                        <strong class="text-dark text-capitalize">{{ str_replace('_', ' ', $payment->payment_channel ?? $order->orderGroup->payment_method) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Order Amount:</span>
                        <strong class="text-dark font-serif fs-6">Rs {{ number_format($order->total) }}</strong>
                    </div>

                    @if($payment && ($payment->transaction_id || $payment->proof_image || $payment->sender_name))
                        <hr class="my-2 opacity-25">
                        @if($payment->transaction_id)
                            <div class="mb-2 small">
                                <span class="text-muted d-block">Transaction / Reference ID:</span>
                                <code class="fw-bold fs-6 text-dark font-monospace bg-light p-1 rounded d-inline-block">{{ $payment->transaction_id }}</code>
                            </div>
                        @endif
                        @if($payment->sender_name)
                            <div class="mb-2 small">
                                <span class="text-muted">Sender:</span>
                                <strong class="text-dark">{{ $payment->sender_name }}</strong>
                                @if($payment->sender_account)
                                    <span class="text-muted">({{ $payment->sender_account }})</span>
                                @endif
                            </div>
                        @endif

                        @if($payment->proof_image)
                            <div class="mb-3">
                                <span class="text-muted small d-block mb-1">Receipt Screenshot / Proof:</span>
                                <a href="{{ Storage::url($payment->proof_image) }}" target="_blank" class="d-block text-center border rounded-3 p-1 bg-light text-decoration-none">
                                    <img src="{{ Storage::url($payment->proof_image) }}" alt="Payment Proof" class="img-fluid rounded" style="max-height: 140px; object-fit: contain;">
                                    <div class="small fw-semibold text-primary mt-1"><i class="bi bi-zoom-in me-1"></i> View Full Receipt</div>
                                </a>
                            </div>
                        @endif
                    @endif

                    <!-- Action buttons for seller -->
                    @if($order->payment_status === 'under_review' || ($order->payment_status === 'pending' && $order->orderGroup->payment_method !== 'cod'))
                        <div class="d-grid gap-2 mt-3">
                            <form action="{{ route('seller.orders.approve-payment', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success rounded-pill w-100 fw-bold shadow-xs py-2" onclick="return confirm('Accept and verify payment of Rs {{ number_format($order->total) }} for this order?')">
                                    <i class="bi bi-check-circle-fill me-1"></i> Verify &amp; Accept Payment
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-danger rounded-pill w-100 btn-sm py-1.5 fw-semibold" data-bs-toggle="modal" data-bs-target="#sellerRejectPaymentModal">
                                <i class="bi bi-x-circle me-1"></i> Reject Payment Proof
                            </button>
                        </div>
                    @elseif($order->payment_status === 'paid')
                        <div class="alert alert-success border-0 rounded-3 py-2 px-3 mb-0 small text-success-emphasis bg-success-subtle mt-2">
                            <i class="bi bi-check-circle-fill me-1"></i> Payment verified &amp; settled with buyer.
                        </div>
                    @elseif($order->payment_status === 'rejected')
                        <div class="alert alert-danger border-0 rounded-3 py-2 px-3 mb-0 small text-danger bg-danger-subtle mt-2">
                            <i class="bi bi-x-circle-fill me-1"></i> Payment rejected. Waiting for customer re-submission.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Financial Payout Breakdown -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom border-light">
                    <h6 class="mb-0 fw-bold font-serif text-dark-green">Financial & Payout Breakdown</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Item Subtotal:</span>
                        <span class="fw-semibold">Rs {{ number_format($order->subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Delivery Fee:</span>
                        <span class="fw-semibold">Rs {{ number_format($order->delivery_fee) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-2 text-danger">
                        <span>Platform Commission:</span>
                        <span>- Rs {{ number_format($order->commission) }}</span>
                    </div>
                    <hr class="my-2 opacity-25">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold text-dark">Seller Net Payout:</span>
                        <span class="fs-5 fw-bold text-primary font-serif">Rs {{ number_format($order->subtotal - $order->commission) }}</span>
                    </div>
                    <div class="p-2 bg-light rounded-3 small">
                        <span class="text-muted">Payment Method:</span>
                        <strong class="text-dark">{{ strtoupper($order->orderGroup->payment_method) }}</strong> &bull;
                        <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success text-white' : 'bg-warning text-dark' }} ms-1">
                            {{ strtoupper($order->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer & Delivery Address Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom border-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold font-serif text-dark-green">
                        <i class="bi bi-geo-alt-fill text-gold me-1"></i> Customer Shipping
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
                    <div class="small text-muted mb-2">
                        <i class="bi bi-telephone me-1"></i>
                        <a href="tel:{{ $order->orderGroup->shipping_phone }}" class="text-decoration-none fw-semibold">
                            {{ $order->orderGroup->shipping_phone }}
                        </a>
                    </div>
                    <div class="small text-secondary mb-2">
                        {{ $order->orderGroup->shipping_address_line }}, {{ $order->orderGroup->shipping_area }}, <strong>{{ $order->orderGroup->shipping_city }}</strong>, Gilgit-Baltistan
                    </div>
                    <div class="p-2 bg-light rounded-3 small text-muted">
                        <i class="bi bi-info-circle me-1 text-primary"></i> <strong>Schedule:</strong> Please ensure dispatch reaches customer during the <strong>{{ ucfirst($slot) }} slot</strong>.
                    </div>
                </div>
            </div>

            <!-- Assigned Courier Rider Card if applicable -->
            @if($order->rider_name || $order->rider_phone)
                <div class="card border-0 shadow-sm rounded-4 bg-light border-success">
                    <div class="card-body p-4">
                        <span class="badge bg-success-subtle text-success border border-success-subtle mb-2">Assigned Delivery Rider</span>
                        <h6 class="fw-bold text-dark mb-1">{{ $order->rider_name }}</h6>
                        <div class="small text-muted mb-2">Himalayan Cold-Chain Courier Fleet</div>
                        <a href="tel:{{ $order->rider_phone }}" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold">
                            <i class="bi bi-telephone-outbound-fill me-1"></i> Call Rider ({{ $order->rider_phone }})
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

<!-- Dispatch / Rider Assignment Modal (When moving packing -> in_transit) -->
@if($nextStatus === 'in_transit')
    <div class="modal fade" id="dispatchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title font-serif text-dark-green">Dispatch Order & Assign Rider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('seller.orders.advance-status', $order->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="target_status" value="in_transit">
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Please provide the name and mobile number of the valley courier rider handling this insulated cold-chain shipment.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Rider Name <span class="text-danger">*</span></label>
                            <input type="text" name="rider_name" class="form-control" placeholder="e.g. Shamsuddin Hunzai" value="{{ old('rider_name', $order->rider_name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Rider Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="rider_phone" class="form-control" placeholder="0341XXXXXXX" value="{{ old('rider_phone', $order->rider_phone) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Dispatch Notes (Optional)</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="e.g. Cold box sealed at 8:30 AM with fresh ice gel packs"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">Dispatch Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Seller Cancel Order Modal -->
@if(!in_array($order->status, ['in_transit', 'delivered', 'cancelled']))
    <div class="modal fade" id="sellerCancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title font-serif text-danger">Cancel Order #{{ $order->order_no }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('seller.orders.cancel', $order->id) }}" method="POST">
                    @csrf
                    <div class="modal-body text-start">
                        <p class="small text-muted mb-3">
                            Are you sure you want to cancel this order as a seller? The reserved quantity will be automatically restored to your farm stock, and the customer will be notified with your stated reason.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Reason for Cancellation <span class="text-danger">*</span></label>
                            <select name="cancel_reason" class="form-select form-select-sm mb-2" onchange="if(this.value==='Other'){document.getElementById('customReasonWrapper').style.display='block';}else{document.getElementById('customReasonWrapper').style.display='none';}">
                                <option value="Batch out of stock / Insufficient fresh morning yield">Batch out of stock / Insufficient fresh morning yield</option>
                                <option value="Quality check failed prior to packaging">Quality check failed prior to packaging</option>
                                <option value="Adverse mountain weather / road blockage">Adverse mountain weather / road blockage</option>
                                <option value="Cold-storage packaging material shortage">Cold-storage packaging material shortage</option>
                                <option value="Customer requested cancellation via phone">Customer requested cancellation via phone</option>
                                <option value="Other">Other (Write custom note)</option>
                            </select>
                            <div id="customReasonWrapper" style="display: none;">
                                <textarea name="custom_cancel_reason" class="form-control form-control-sm" rows="2" placeholder="Describe the specific reason..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Keep Order</button>
                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold" onclick="const customWrap = document.getElementById('customReasonWrapper'); if(customWrap && customWrap.style.display !== 'none'){ const customText = customWrap.querySelector('textarea').value; if(customText) { this.form.querySelector('select[name=cancel_reason]').value = customText; } }">
                            Confirm Cancellation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Seller Reject Payment Modal -->
<div class="modal fade" id="sellerRejectPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow text-start">
            <div class="modal-header border-bottom">
                <h5 class="modal-title font-serif text-danger">Reject Payment Receipt (#{{ $order->order_no }})</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('seller.orders.reject-payment', $order->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Please indicate why the customer's payment transaction was not accepted. The customer will be immediately notified to upload a valid receipt.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                        <select name="rejection_reason" class="form-select form-select-sm mb-2" onchange="if(this.value==='Other'){document.getElementById('customRejReason').style.display='block';}else{document.getElementById('customRejReason').style.display='none';}">
                            <option value="Transaction ID not found in farm account statement">Transaction ID not found in farm account statement</option>
                            <option value="Screenshot is blurry / illegible receipt">Screenshot is blurry / illegible receipt</option>
                            <option value="Transferred amount does not match order total">Transferred amount does not match order total</option>
                            <option value="Old / duplicate transaction reference">Old / duplicate transaction reference</option>
                            <option value="Other">Other (Write specific reason)</option>
                        </select>
                        <div id="customRejReason" style="display: none;">
                            <textarea name="custom_rejection_reason" class="form-control form-control-sm" rows="2" placeholder="Write specific reason..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold" onclick="const customWrap = document.getElementById('customRejReason'); if(customWrap && customWrap.style.display !== 'none'){ const customText = customWrap.querySelector('textarea').value; if(customText) { this.form.querySelector('select[name=rejection_reason]').value = customText; } }">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
