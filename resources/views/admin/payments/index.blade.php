@extends('layouts.panel')

@section('title', 'Platform Payments - Admin Center')
@section('page_heading', 'Payments & Verification Center')

@section('menu_items')
    @include('admin.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- KPI mini-strip -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Action Required (Under Review) -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center gap-3 position-relative overflow-hidden {{ $totalUnderReviewCount > 0 ? 'border border-warning' : '' }}">
                <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-shield-exclamation fs-3"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-xs text-muted text-uppercase fw-bold">Under Review</span>
                        @if($totalUnderReviewCount > 0)
                            <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 text-xs fw-bold">Action Needed</span>
                        @endif
                    </div>
                    <h4 class="font-serif fw-bold text-dark mb-0">
                        {{ $totalUnderReviewCount }} <small class="fs-6 text-muted font-sans fw-normal">({{ number_format($totalUnderReviewSum) }} PKR)</small>
                    </h4>
                </div>
            </div>
        </div>

        <!-- Card 2: Settled / Paid Payments -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-wallet2 fs-3"></i>
                </div>
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">Verified &amp; Paid</div>
                    <h4 class="font-serif fw-bold text-dark mb-0">Rs {{ number_format($totalPaidSum) }}</h4>
                </div>
            </div>
        </div>

        <!-- Card 3: Pending -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">Pending Transfers / COD</div>
                    <h4 class="font-serif fw-bold text-dark mb-0">Rs {{ number_format($totalPendingSum) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <!-- Status Pills -->
                <ul class="nav nav-pills gap-1 flex-wrap">
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-1 text-sm {{ !$currentStatus ? 'active bg-darkgreen' : 'text-dark bg-light' }}" href="{{ route('admin.payments.index', array_filter(['method' => $currentMethod, 'q' => $search])) }}">
                            All Statuses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'under_review' ? 'active bg-warning text-dark fw-bold' : 'text-dark bg-light' }}" href="{{ route('admin.payments.index', array_filter(['method' => $currentMethod, 'status' => 'under_review', 'q' => $search])) }}">
                            <i class="bi bi-hourglass-split me-1"></i> Under Review
                            @if($totalUnderReviewCount > 0)
                                <span class="badge bg-dark text-white rounded-pill ms-1">{{ $totalUnderReviewCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'paid' ? 'active bg-success text-white' : 'text-dark bg-light' }}" href="{{ route('admin.payments.index', array_filter(['method' => $currentMethod, 'status' => 'paid', 'q' => $search])) }}">
                            <i class="bi bi-check-circle-fill me-1"></i> Paid
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'rejected' ? 'active bg-danger text-white' : 'text-dark bg-light' }}" href="{{ route('admin.payments.index', array_filter(['method' => $currentMethod, 'status' => 'rejected', 'q' => $search])) }}">
                            <i class="bi bi-x-circle-fill me-1"></i> Rejected
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'pending' ? 'active bg-secondary text-white' : 'text-dark bg-light' }}" href="{{ route('admin.payments.index', array_filter(['method' => $currentMethod, 'status' => 'pending', 'q' => $search])) }}">
                            Pending
                        </a>
                    </li>
                </ul>

                <span class="text-muted small mx-1">|</span>

                <!-- Method Pills -->
                <ul class="nav nav-pills gap-1 flex-wrap">
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-2.5 py-1 text-xs {{ !$currentMethod ? 'active bg-primary' : 'text-secondary' }}" href="{{ route('admin.payments.index', array_filter(['status' => $currentStatus, 'q' => $search])) }}">
                            All Channels
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-2.5 py-1 text-xs {{ $currentMethod === 'bank_transfer' ? 'active bg-primary' : 'text-secondary' }}" href="{{ route('admin.payments.index', array_filter(['method' => 'bank_transfer', 'status' => $currentStatus, 'q' => $search])) }}">
                            Bank &amp; Online
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-2.5 py-1 text-xs {{ $currentMethod === 'cod' ? 'active bg-primary' : 'text-secondary' }}" href="{{ route('admin.payments.index', array_filter(['method' => 'cod', 'status' => $currentStatus, 'q' => $search])) }}">
                            Cash on Delivery
                        </a>
                    </li>
                </ul>
            </div>

            <form action="{{ route('admin.payments.index') }}" method="GET" class="d-flex gap-2">
                @if($currentMethod)
                    <input type="hidden" name="method" value="{{ $currentMethod }}">
                @endif
                @if($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="input-group input-group-sm" style="min-width: 260px;">
                    <input type="text" name="q" class="form-control" placeholder="Search TRX ID, Buyer, Order #..." value="{{ $search }}">
                    <button type="submit" class="btn btn-outline-darkgreen">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        @if($payments->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-credit-card fs-1 text-gold d-block mb-3"></i>
                <h5 class="font-serif fw-bold text-dark">No Payment Transactions Found</h5>
                <p class="small mb-0">No payment records match your active search or filter criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Transaction / Ref</th>
                            <th>Order Group</th>
                            <th>Buyer Details</th>
                            <th>Method / Channel</th>
                            <th>Amount</th>
                            <th>Proof Receipt</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Verification Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr class="{{ $payment->isUnderReview() ? 'table-warning bg-opacity-10' : '' }}">
                                <!-- Transaction Ref / ID -->
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="fw-bold font-monospace text-dark text-break">
                                            {{ $payment->transaction_id ?? $payment->gateway_ref ?? 'N/A' }}
                                        </span>
                                        @if($payment->transaction_id || $payment->gateway_ref)
                                            <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-1" title="Copy Transaction ID" onclick="navigator.clipboard.writeText('{{ $payment->transaction_id ?? $payment->gateway_ref }}'); this.innerHTML='<i class=\'bi bi-check text-success\'></i>'; setTimeout(() => this.innerHTML='<i class=\'bi bi-clipboard\'></i>', 1500);">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        @endif
                                    </div>

                                    @if($payment->sender_name)
                                        <div class="text-xs text-muted">
                                            <i class="bi bi-person me-1"></i>{{ $payment->sender_name }} 
                                            @if($payment->sender_account)
                                                ({{ $payment->sender_account }})
                                            @endif
                                        </div>
                                    @endif

                                    <div class="text-xs text-muted">
                                        {{ $payment->created_at->format('M d, Y h:i A') }}
                                    </div>
                                </td>

                                <!-- Order Group Link -->
                                <td>
                                    @if($payment->orderGroup)
                                        <span class="badge bg-light text-dark border font-monospace d-block mb-1">
                                            {{ $payment->orderGroup->group_no }}
                                        </span>
                                        <div class="small text-muted">
                                            @foreach($payment->orderGroup->orders as $o)
                                                <a href="{{ route('admin.orders.show', $o->id) }}" class="text-success text-decoration-none fw-semibold text-xs d-inline-block me-1">
                                                    {{ $o->order_no }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>

                                <!-- Buyer -->
                                <td>
                                    <div class="fw-semibold text-dark">{{ $payment->orderGroup->user->name ?? $payment->orderGroup->shipping_full_name ?? 'Buyer' }}</div>
                                    <div class="text-xs text-muted"><i class="bi bi-telephone me-1"></i>{{ $payment->orderGroup->user->phone ?? $payment->orderGroup->shipping_phone ?? 'N/A' }}</div>
                                    <div class="text-xs text-muted">{{ $payment->orderGroup->user->email ?? '' }}</div>
                                </td>

                                <!-- Method / Channel -->
                                <td>
                                    @php
                                        $channelLabel = $payment->payment_channel ? ucfirst(str_replace('_', ' ', $payment->payment_channel)) : strtoupper($payment->method);
                                    @endphp
                                    <span class="badge bg-light text-dark border fw-semibold text-xs">
                                        <i class="bi bi-cash-coin text-success me-1"></i>
                                        {{ $channelLabel }}
                                    </span>
                                </td>

                                <!-- Amount -->
                                <td>
                                    <div class="fw-bold text-darkgreen fs-6">{{ $payment->formatted_amount }}</div>
                                </td>

                                <!-- Proof Image -->
                                <td>
                                    @if($payment->proof_image_url)
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 text-xs d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#proofModal_{{ $payment->id }}">
                                            <i class="bi bi-image"></i> View Receipt
                                        </button>

                                        <!-- Modal for this Payment Proof -->
                                        <div class="modal fade" id="proofModal_{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content rounded-4 border-0 shadow">
                                                    <div class="modal-header border-bottom py-3">
                                                        <div>
                                                            <h5 class="modal-title font-serif fw-bold text-dark">Payment Proof Receipt</h5>
                                                            <p class="text-muted small mb-0">
                                                                TRX ID: <strong class="text-dark font-monospace">{{ $payment->transaction_id ?? $payment->gateway_ref }}</strong>
                                                                @if($payment->sender_name)
                                                                    &bull; Sender: <strong>{{ $payment->sender_name }}</strong>
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-center p-4 bg-light">
                                                        <img src="{{ $payment->proof_image_url }}" class="img-fluid rounded-3 shadow-sm border max-vh-75" style="max-height: 500px; object-fit: contain;" alt="Payment Proof">
                                                    </div>
                                                    <div class="modal-footer border-top py-2 d-flex justify-content-between">
                                                        <a href="{{ $payment->proof_image_url }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                            <i class="bi bi-box-arrow-up-right me-1"></i> Open Full Image
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-muted">No screenshot</span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td>
                                    @if($payment->isPaid())
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-xs">
                                            <i class="bi bi-check-circle-fill me-1"></i> Verified &amp; Paid
                                        </span>
                                        @if($payment->verifiedByUser)
                                            <div class="text-xs text-muted mt-1">by {{ $payment->verifiedByUser->name }}</div>
                                        @endif
                                    @elseif($payment->isUnderReview())
                                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 text-xs fw-bold">
                                            <i class="bi bi-hourglass-split me-1"></i> Under Review
                                        </span>
                                    @elseif($payment->isRejected())
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 text-xs">
                                            <i class="bi bi-x-circle-fill me-1"></i> Rejected
                                        </span>
                                        @if($payment->admin_notes)
                                            <div class="text-xs text-danger mt-0.5 text-truncate" style="max-width: 140px;" title="{{ $payment->admin_notes }}">
                                                {{ $payment->admin_notes }}
                                            </div>
                                        @endif
                                    @elseif($payment->status === 'pending')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 text-xs">
                                            <i class="bi bi-clock me-1"></i> Pending
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-2.5 py-1 text-xs">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Verification Actions -->
                                <td class="text-end pe-4">
                                    @if(!$payment->isPaid())
                                        <div class="d-inline-flex gap-1">
                                            <!-- Approve Action Button -->
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 text-xs fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#approveModal_{{ $payment->id }}">
                                                <i class="bi bi-check-lg me-1"></i> Approve
                                            </button>

                                            <!-- Reject Action Button -->
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 text-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#rejectModal_{{ $payment->id }}">
                                                <i class="bi bi-x-lg me-1"></i> Reject
                                            </button>
                                        </div>

                                        <!-- Approve Modal for this Payment -->
                                        <div class="modal fade text-start" id="approveModal_{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content rounded-4 border-0 shadow">
                                                    <form action="{{ route('admin.payments.approve', $payment->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header border-bottom py-3">
                                                            <h5 class="modal-title font-serif fw-bold text-success">
                                                                <i class="bi bi-check-circle-fill me-2"></i> Approve Payment
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                            <p class="text-dark mb-3">
                                                                Are you sure you want to approve this payment? This will mark the payment as <strong>Paid</strong>, confirm the order, and reserve stock.
                                                            </p>
                                                            <div class="p-3 bg-light rounded-3 border mb-3">
                                                                <div class="d-flex justify-content-between text-sm mb-1">
                                                                    <span class="text-muted">Order Group:</span>
                                                                    <strong class="font-monospace text-dark">{{ $payment->orderGroup->group_no ?? 'Group #' . $payment->order_group_id }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between text-sm mb-1">
                                                                    <span class="text-muted">Payable Amount:</span>
                                                                    <strong class="text-success fs-6">{{ $payment->formatted_amount }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between text-sm mb-1">
                                                                    <span class="text-muted">Transaction ID:</span>
                                                                    <strong class="font-monospace text-dark">{{ $payment->transaction_id ?? $payment->gateway_ref ?? 'N/A' }}</strong>
                                                                </div>
                                                                @if($payment->sender_name)
                                                                    <div class="d-flex justify-content-between text-sm">
                                                                        <span class="text-muted">Sender:</span>
                                                                        <span class="text-dark">{{ $payment->sender_name }} ({{ $payment->sender_account ?? 'N/A' }})</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small fw-bold text-dark">Admin Verification Note (Optional)</label>
                                                                <input type="text" name="admin_notes" class="form-control form-control-sm" placeholder="e.g. Verified in Meezan Bank statement">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top py-2">
                                                            <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm">
                                                                <i class="bi bi-check2-circle me-1"></i> Confirm &amp; Approve
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reject Modal for this Payment -->
                                        <div class="modal fade text-start" id="rejectModal_{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content rounded-4 border-0 shadow">
                                                    <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header border-bottom py-3">
                                                            <h5 class="modal-title font-serif fw-bold text-danger">
                                                                <i class="bi bi-x-circle-fill me-2"></i> Reject Payment Verification
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                            <p class="text-muted small mb-3">
                                                                Please choose or enter a reason for rejecting this payment proof. The buyer will see this reason on their order tracking screen so they can re-enter their TRX ID or switch to Cash on Delivery.
                                                            </p>
                                                            <div class="p-3 bg-light rounded-3 border mb-3">
                                                                <div class="d-flex justify-content-between text-sm mb-1">
                                                                    <span class="text-muted">Order Group:</span>
                                                                    <strong class="font-monospace text-dark">{{ $payment->orderGroup->group_no ?? 'Group #' . $payment->order_group_id }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between text-sm">
                                                                    <span class="text-muted">Amount:</span>
                                                                    <strong class="text-danger">{{ $payment->formatted_amount }}</strong>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small fw-bold text-dark">Quick Reason Template</label>
                                                                <select class="form-select form-select-sm mb-2" onchange="if(this.value) document.getElementById('rejectReasonInput_{{ $payment->id }}').value = this.value;">
                                                                    <option value="">-- Select reason template --</option>
                                                                    <option value="Transaction ID was not found in bank statement.">Transaction ID not found in bank statement</option>
                                                                    <option value="Amount received does not match the order grand total.">Amount mismatch (less amount sent)</option>
                                                                    <option value="Duplicate transaction ID already used for another order.">Duplicate TRX ID already claimed</option>
                                                                    <option value="Payment proof screenshot is blurry or illegible.">Receipt proof screenshot unreadable</option>
                                                                </select>
                                                                <label class="form-label small fw-bold text-dark">Rejection Reason <span class="text-danger">*</span></label>
                                                                <textarea name="rejection_reason" id="rejectReasonInput_{{ $payment->id }}" class="form-control" rows="3" placeholder="Explain reason for rejection..." required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top py-2">
                                                            <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold shadow-sm">
                                                                <i class="bi bi-x-circle me-1"></i> Confirm Rejection
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-success fw-semibold">
                                            <i class="bi bi-check2-all"></i> Approved
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($payments->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $payments->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
