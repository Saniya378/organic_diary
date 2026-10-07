@extends('layouts.panel')

@section('title', 'Order ' . $order->order_no . ' - Admin Center')
@section('page_heading', 'Order #' . $order->order_no)

@section('menu_items')
    @include('admin.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill mb-2">
                <i class="bi bi-arrow-left me-1"></i> Back to All Orders
            </a>
            <h4 class="font-serif fw-bold text-dark mb-0">
                Order <span class="text-darkgreen font-monospace">#{{ $order->order_no }}</span>
            </h4>
            <div class="text-muted small">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</div>
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn btn-outline-success rounded-pill px-3 btn-sm fw-semibold">
                <i class="bi bi-printer me-1"></i> Print Invoice
            </a>
            @if($order->order_group_id)
                <a href="{{ route('orders.group.invoice', $order->order_group_id) }}" target="_blank" class="btn btn-outline-secondary rounded-pill px-3 btn-sm fw-semibold">
                    <i class="bi bi-receipt me-1"></i> Master Receipt
                </a>
            @endif
            @if($order->status !== 'cancelled')
                <button type="button" class="btn btn-outline-danger rounded-pill px-3 btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-x-circle me-1"></i> Cancel Order
                </button>
            @endif
        </div>

    @if($order->status === 'cancelled')
        <div class="alert alert-danger rounded-4 p-4 border-0 shadow-sm mb-4">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center p-2" style="width: 44px; height: 44px; min-width: 44px;">
                    <i class="bi bi-x-octagon fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-danger">
                        @if($order->isCancelledBySeller())
                            Order Cancelled by Seller ({{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }})
                        @elseif($order->isCancelledByAdmin())
                            Order Cancelled by Platform Admin
                        @else
                            Order Cancelled by Buyer / Customer
                        @endif
                    </h5>
                    <div class="text-dark small mb-1">
                        <strong>Reason:</strong> {{ $order->cancel_reason ?? 'No specific reason recorded.' }}
                    </div>
                    <div class="text-muted text-xs">
                        Stock has been restored to the farmer's live inventory.
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Items & Timeline -->
        <div class="col-lg-8">
            <!-- Order Items Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="font-serif fw-bold text-dark mb-3">Purchased Items</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small">
                                <th class="ps-3">Item</th>
                                <th>Unit Price</th>
                                <th>Quantity</th>
                                <th class="text-end pe-3">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center overflow-hidden" style="width: 45px; height: 45px; min-width: 45px;">
                                                @if($item->product && $item->product->images->isNotEmpty())
                                                    <img src="{{ Storage::url($item->product->images->first()->path) }}" alt="{{ $item->product_name }}" class="w-100 h-100 object-fit-cover">
                                                @else
                                                    <i class="bi bi-egg text-muted fs-5"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                                @if($item->variant)
                                                    <div class="text-xs text-muted">Variant: <span class="badge bg-light text-dark border">{{ $item->variant->label }}</span></div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rs {{ number_format($item->unit_price) }}</td>
                                    <td>x {{ $item->quantity }}</td>
                                    <td class="text-end pe-3 fw-bold text-darkgreen">Rs {{ number_format($item->line_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td colspan="3" class="text-end text-muted ps-3">Subtotal</td>
                                <td class="text-end pe-3 fw-semibold text-dark">{{ $order->formatted_subtotal }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end text-muted ps-3">Delivery Fee</td>
                                <td class="text-end pe-3 text-dark">{{ $order->formatted_delivery_fee }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end text-muted ps-3">Platform Commission (5%)</td>
                                <td class="text-end pe-3 text-muted">Rs {{ number_format($order->commission) }}</td>
                            </tr>
                            <tr class="table-light">
                                <td colspan="3" class="text-end fw-bold text-dark ps-3 fs-6">Grand Total</td>
                                <td class="text-end pe-3 fw-bold text-darkgreen fs-5">{{ $order->formatted_total }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Order Timeline Audit History -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="font-serif fw-bold text-dark mb-4">Status Transition Timeline</h5>

                @if($order->statusHistories->isEmpty())
                    <p class="text-muted small mb-0">No transition history logged yet.</p>
                @else
                    <div class="timeline ps-3 position-relative" style="border-left: 2px dashed #2F5D3A; margin-left: 10px;">
                        @foreach($order->statusHistories as $history)
                            <div class="mb-4 position-relative ps-4">
                                <div class="position-absolute" style="left: -27px; top: 0; width: 14px; height: 14px; border-radius: 50%; background-color: #2F5D3A; border: 3px solid #FAF7F0;"></div>
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <span class="badge bg-primary text-capitalize rounded-pill px-3 py-1 mb-1">
                                        {{ str_replace('_', ' ', $history->status) }}
                                    </span>
                                    <span class="text-xs text-muted">{{ $history->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div class="text-sm text-dark mt-1">{{ $history->note ?? 'Status changed.' }}</div>
                                @if($history->changedByUser)
                                    <div class="text-xs text-muted">By: {{ $history->changedByUser->name }} ({{ ucfirst($history->changedByUser->role) }})</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Buyer, Seller, Delivery Details -->
        <div class="col-lg-4">
            <!-- Order Status & Logistics -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="font-serif fw-bold text-dark mb-3">Order Status</h6>
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <span class="text-muted small">Status:</span>
                    <span class="badge bg-darkgreen text-white px-3 py-1 rounded-pill text-capitalize">
                        {{ str_replace('_', ' ', $order->status) }}
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <span class="text-muted small">Payment Status:</span>
                    @if($order->payment_status === 'paid')
                        <span class="badge bg-success px-3 py-1 rounded-pill">Paid</span>
                    @elseif($order->payment_status === 'under_review')
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">Under Review</span>
                    @elseif($order->payment_status === 'rejected')
                        <span class="badge bg-danger px-3 py-1 rounded-pill">Rejected</span>
                    @else
                        <span class="badge bg-secondary px-3 py-1 rounded-pill">{{ ucfirst($order->payment_status) }}</span>
                    @endif
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted small">Payment Method:</span>
                    <span class="fw-semibold text-dark text-uppercase">
                        {{ str_replace('_', ' ', $order->orderGroup->payment_method ?? 'COD') }}
                    </span>
                </div>
                @if($order->rider_name)
                    <div class="mt-3 pt-3 border-top">
                        <div class="text-muted small mb-1">Assigned Rider:</div>
                        <div class="fw-semibold text-dark">{{ $order->rider_name }}</div>
                        <div class="text-xs text-muted"><i class="bi bi-telephone me-1"></i> {{ $order->rider_phone }}</div>
                    </div>
                @endif
                @if($order->cancel_reason)
                    <div class="mt-3 pt-3 border-top bg-danger-subtle p-2 rounded-3">
                        <div class="text-danger fw-semibold small mb-1"><i class="bi bi-x-octagon me-1"></i> Cancelled Reason:</div>
                        <div class="text-xs text-dark">{{ $order->cancel_reason }}</div>
                    </div>
                @endif
            </div>

            <!-- Payment Proof & Verification Card -->
            @php
                $orderPayment = $order->orderGroup->payment;
            @endphp
            @if($orderPayment)
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 {{ $orderPayment->isUnderReview() ? 'border border-warning' : '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="font-serif fw-bold text-dark mb-0">
                            <i class="bi bi-shield-check text-success me-1"></i> Payment Verification
                        </h6>
                        @if($orderPayment->isUnderReview())
                            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 text-xs fw-bold">Action Needed</span>
                        @elseif($orderPayment->isPaid())
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-xs">Verified</span>
                        @elseif($orderPayment->isRejected())
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 text-xs">Rejected</span>
                        @endif
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-3 text-sm">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Transaction ID:</span>
                            <strong class="font-monospace text-dark">{{ $orderPayment->transaction_id ?? $orderPayment->gateway_ref ?? 'N/A' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Channel / Bank:</span>
                            <span class="fw-semibold text-dark">{{ ucfirst(str_replace('_', ' ', $orderPayment->payment_channel ?? $orderPayment->method)) }}</span>
                        </div>
                        @if($orderPayment->sender_name)
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Sender Name:</span>
                                <span class="fw-semibold text-dark">{{ $orderPayment->sender_name }}</span>
                            </div>
                        @endif
                        @if($orderPayment->sender_account)
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Sender Account:</span>
                                <span class="font-monospace text-muted">{{ $orderPayment->sender_account }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between pt-1 border-top mt-1">
                            <span class="text-muted">Payable Total:</span>
                            <strong class="text-success fs-6">{{ $orderPayment->formatted_amount }}</strong>
                        </div>
                    </div>

                    @if($orderPayment->proof_image_url)
                        <div class="mb-3 text-center">
                            <a href="{{ $orderPayment->proof_image_url }}" target="_blank" class="d-block position-relative rounded-3 overflow-hidden border">
                                <img src="{{ $orderPayment->proof_image_url }}" alt="Receipt Proof" class="img-fluid" style="max-height: 180px; object-fit: cover; width: 100%;">
                                <span class="badge bg-dark bg-opacity-75 text-white position-absolute bottom-0 start-50 translate-middle-x mb-2 px-3 py-1 rounded-pill text-xs">
                                    <i class="bi bi-zoom-in me-1"></i> View Full Receipt
                                </span>
                            </a>
                        </div>
                    @endif

                    @if($orderPayment->admin_notes)
                        <div class="alert alert-light border small text-muted p-2 rounded-3 mb-3">
                            <strong>Admin Note:</strong> {{ $orderPayment->admin_notes }}
                        </div>
                    @endif

                    @if(!$orderPayment->isPaid())
                        <div class="d-grid gap-2">
                            <form action="{{ route('admin.payments.approve', $orderPayment->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill fw-bold shadow-sm" onclick="return confirm('Are you sure you want to approve this payment?');">
                                    <i class="bi bi-check2-circle me-1"></i> Approve &amp; Mark Paid
                                </button>
                            </form>

                            <button type="button" class="btn btn-outline-danger btn-sm w-100 rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#rejectOrderPaymentModal">
                                <i class="bi bi-x-circle me-1"></i> Reject Payment Proof
                            </button>
                        </div>

                        <!-- Reject Modal for Single Order -->
                        <div class="modal fade" id="rejectOrderPaymentModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <form action="{{ route('admin.payments.reject', $orderPayment->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header border-bottom py-3">
                                            <h5 class="modal-title font-serif fw-bold text-danger">Reject Payment Proof</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <label class="form-label small fw-bold text-dark">Reason for Rejection <span class="text-danger">*</span></label>
                                            <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Explain reason (e.g. TRX not found in bank statement)..." required></textarea>
                                        </div>
                                        <div class="modal-footer border-top py-2">
                                            <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold">Confirm Rejection</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Customer & Delivery Address Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="font-serif fw-bold text-dark mb-3">Buyer & Shipping Address</h6>
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-darkgreen fw-bold" style="width: 42px; height: 42px;">
                        {{ strtoupper(substr($order->buyer->name ?? 'B', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $order->buyer->name ?? 'Guest Buyer' }}</div>
                        <div class="text-xs text-muted">{{ $order->buyer->email ?? 'No email' }}</div>
                        <div class="text-xs text-muted">{{ $order->buyer->phone ?? '' }}</div>
                    </div>
                </div>

                <div class="small">
                    <div class="text-muted mb-1">Shipping Snapshot:</div>
                    <div class="fw-semibold text-dark">{{ $order->orderGroup->shipping_full_name ?? $order->buyer->name }}</div>
                    <div class="text-muted">{{ $order->orderGroup->shipping_address_line ?? 'N/A' }}</div>
                    <div class="text-muted">{{ $order->orderGroup->shipping_area ?? '' }}, {{ $order->orderGroup->shipping_city ?? '' }}</div>
                    <div class="text-muted mt-1"><i class="bi bi-telephone me-1"></i> {{ $order->orderGroup->shipping_phone ?? $order->buyer->phone }}</div>
                </div>
            </div>

            <!-- Seller Farm Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h6 class="font-serif fw-bold text-dark mb-3">Fulfilling Seller Farm</h6>
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px;">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}</div>
                        <div class="text-xs text-muted">{{ $order->seller->sellerProfile->location ?? 'Gilgit-Baltistan' }}</div>
                        <div class="text-xs text-muted"><i class="bi bi-telephone me-1"></i> {{ $order->seller->phone ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    @if($order->status !== 'cancelled')
        <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <form action="{{ route('admin.orders.cancel', $order) }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title font-serif fw-bold text-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i> Cancel Order #{{ $order->order_no }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-3">
                            <p class="text-muted small mb-3">
                                Cancelling this order will automatically restore stock for all items in this order and record your reason in the timeline.
                            </p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-sm">Cancellation Reason <span class="text-danger">*</span></label>
                                <textarea name="cancel_reason" class="form-control" rows="3" placeholder="Provide a reason for cancelling..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger rounded-pill px-4">Confirm Order Cancellation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
