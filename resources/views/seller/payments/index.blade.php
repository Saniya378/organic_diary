@extends('layouts.panel')

@section('title', 'Buyer Payments Verification - Farmer Portal')
@section('page_heading', 'Buyer Payments & Verification')

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Overview Stats -->
    <div class="row g-4 mb-4">
        <!-- Awaiting Verification -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid #E0A93B !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Awaiting Verification</span>
                        <h3 class="font-serif fw-bold text-warning-emphasis mt-1 mb-0">Rs {{ number_format($totalPendingReviewSum) }}</h3>
                        <div class="small text-muted mt-2">
                            <span class="badge bg-warning text-dark rounded-pill">{{ $counts['under_review'] }}</span> orders with payment receipts submitted
                        </div>
                    </div>
                    <div class="rounded-4 p-3 bg-warning-subtle text-warning-emphasis">
                        <i class="bi bi-clock-history fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Paid / Verified -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid #2D5A3C !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Verified &amp; Paid</span>
                        <h3 class="font-serif fw-bold text-success mt-1 mb-0">Rs {{ number_format($totalPaidSum) }}</h3>
                        <div class="small text-muted mt-2">
                            <span class="badge bg-success-subtle text-success rounded-pill">{{ $counts['paid'] }}</span> orders confirmed &amp; settled
                        </div>
                    </div>
                    <div class="rounded-4 p-3 bg-success-subtle text-success">
                        <i class="bi bi-patch-check-fill fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payment / Unpaid -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid #6C757D !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Pending or COD</span>
                        <h3 class="font-serif fw-bold text-dark mt-1 mb-0">{{ $counts['pending'] }} Orders</h3>
                        <div class="small text-muted mt-2">
                            Awaiting transfer or pay on delivery
                        </div>
                    </div>
                    <div class="rounded-4 p-3 bg-light text-secondary">
                        <i class="bi bi-cash-stack fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <ul class="nav nav-pills gap-1">
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1.5 text-sm {{ !$currentStatus ? 'active bg-primary' : 'text-dark' }}" href="{{ route('seller.payments.index') }}">
                        All ({{ $counts['all'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1.5 text-sm {{ $currentStatus === 'under_review' ? 'active bg-warning text-dark' : 'text-dark' }}" href="{{ route('seller.payments.index', ['status' => 'under_review']) }}">
                        <i class="bi bi-exclamation-circle-fill me-1"></i> Awaiting Verification ({{ $counts['under_review'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1.5 text-sm {{ $currentStatus === 'paid' ? 'active bg-success' : 'text-dark' }}" href="{{ route('seller.payments.index', ['status' => 'paid']) }}">
                        Paid ({{ $counts['paid'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1.5 text-sm {{ $currentStatus === 'pending' ? 'active bg-secondary' : 'text-dark' }}" href="{{ route('seller.payments.index', ['status' => 'pending']) }}">
                        Pending ({{ $counts['pending'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1.5 text-sm {{ $currentStatus === 'rejected' ? 'active bg-danger' : 'text-dark' }}" href="{{ route('seller.payments.index', ['status' => 'rejected']) }}">
                        Rejected ({{ $counts['rejected'] }})
                    </a>
                </li>
            </ul>

            <!-- Search Form -->
            <form action="{{ route('seller.payments.index') }}" method="GET" class="d-flex align-items-center gap-2">
                @if($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm border-0 bg-light" placeholder="Search Order # or TRX ID...">
                    @if($search)
                        <a href="{{ route('seller.payments.index', ['status' => $currentStatus]) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Payments List Table -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small">
                        <th class="ps-4">Order &amp; Date</th>
                        <th>Buyer Information</th>
                        <th>Method / Channel</th>
                        <th>Transaction Proof</th>
                        <th>Order Amount</th>
                        <th>Payment Status</th>
                        <th class="text-end pe-4">Verification Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $payment = $order->orderGroup->payment;
                            $paymentStatusBadges = [
                                'under_review' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                'paid' => 'bg-success-subtle text-success border border-success-subtle',
                                'pending' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
                                'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                            ];
                            $badgeClass = $paymentStatusBadges[$order->payment_status] ?? 'bg-light text-dark';
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('seller.orders.show', $order->id) }}" class="fw-bold font-monospace text-dark text-decoration-none">
                                    #{{ $order->order_no }}
                                </a>
                                <div class="small text-muted">{{ $order->created_at->format('M d, Y &bull; h:i A') }}</div>
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $order->orderGroup->shipping_full_name ?? $order->buyer->name }}</strong>
                                <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $order->orderGroup->shipping_phone ?? $order->buyer->phone }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark text-capitalize">
                                    {{ str_replace('_', ' ', $payment->payment_channel ?? $order->orderGroup->payment_method) }}
                                </div>
                                <small class="text-muted">{{ strtoupper($order->orderGroup->payment_method) }}</small>
                            </td>
                            <td>
                                @if($payment && ($payment->transaction_id || $payment->proof_image))
                                    <div>
                                        @if($payment->transaction_id)
                                            <span class="badge bg-light text-dark border font-monospace text-xs mb-1">
                                                TRX: {{ $payment->transaction_id }}
                                            </span>
                                        @endif
                                        @if($payment->sender_name)
                                            <div class="text-xs text-muted">From: {{ $payment->sender_name }} ({{ $payment->sender_account ?? 'N/A' }})</div>
                                        @endif
                                        @if($payment->proof_image)
                                            <div class="mt-1">
                                                <a href="{{ Storage::url($payment->proof_image) }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill py-0 px-2 fw-semibold" style="font-size: 0.72rem;">
                                                    <i class="bi bi-image me-1"></i> View Receipt
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small">No receipt uploaded</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark font-serif fs-6">Rs {{ number_format($order->total) }}</strong>
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }} px-2.5 py-1 rounded-pill small">
                                    {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1.5 align-items-center">
                                    @if($order->payment_status === 'under_review' || $order->payment_status === 'pending')
                                        <!-- Approve Payment Form -->
                                        <form action="{{ route('seller.orders.approve-payment', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-xs" onclick="return confirm('Verify and accept payment of Rs {{ number_format($order->total) }} for Order #{{ $order->order_no }}?')">
                                                <i class="bi bi-check2-circle me-1"></i> Verify &amp; Accept
                                            </button>
                                        </form>

                                        <!-- Reject Payment Button triggering Modal -->
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 fw-semibold" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $order->id }}" title="Reject invalid payment slip">
                                            <i class="bi bi-x-circle"></i>
                                        </button>

                                        <!-- Reject Modal for this order -->
                                        <div class="modal fade" id="rejectModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content rounded-4 border-0 shadow text-start">
                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title font-serif text-danger">Reject Payment Proof (#{{ $order->order_no }})</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('seller.orders.reject-payment', $order->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p class="small text-muted mb-3">
                                                                Please explain why the payment receipt was rejected. The buyer will be notified immediately to re-upload a valid transaction slip.
                                                            </p>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                                                                <select name="rejection_reason" class="form-select form-select-sm mb-2" onchange="if(this.value==='Other'){document.getElementById('customRej{{ $order->id }}').style.display='block';}else{document.getElementById('customRej{{ $order->id }}').style.display='none';}">
                                                                    <option value="Transaction ID not found in farm bank/wallet statement">Transaction ID not found in farm bank/wallet statement</option>
                                                                    <option value="Screenshot is blurry / illegible receipt">Screenshot is blurry / illegible receipt</option>
                                                                    <option value="Transferred amount does not match order total">Transferred amount does not match order total</option>
                                                                    <option value="Old / duplicate transaction reference">Old / duplicate transaction reference</option>
                                                                    <option value="Other">Other reason</option>
                                                                </select>
                                                                <div id="customRej{{ $order->id }}" style="display: none;">
                                                                    <textarea name="custom_reason" class="form-control form-control-sm" rows="2" placeholder="Write specific reason..."></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top bg-light">
                                                            <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold">Reject Receipt</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <a href="{{ route('seller.orders.show', $order->id) }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold">
                                        Details &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-credit-card-2-front fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                <h6>No payment records found</h6>
                                <p class="small mb-0">When buyers place orders or submit payment slips, they will show up here for your verification.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="card-footer bg-white py-3 border-top border-light">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
