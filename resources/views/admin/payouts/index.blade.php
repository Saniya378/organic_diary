@extends('layouts.panel')

@section('title', 'Farmer Payout Requests - Admin Panel')
@section('page_heading', 'Seller Payouts & Disbursements')

@section('menu_items')
    @include('admin.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Top Heading & Search -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Seller Payouts</li>
                </ol>
            </nav>
            <h4 class="font-serif fw-bold text-dark mb-0">Farmer Withdrawal Management</h4>
        </div>
        <div>
            <form action="{{ route('admin.payouts.index') }}" method="GET" class="d-flex gap-2">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="input-group" style="min-width: 260px;">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search Ref, Farm, Account #...">
                    <button class="btn btn-outline-darkgreen btn-sm" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Financial KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid #E0A93B !important;">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Pending Disbursements</span>
                <h3 class="font-serif fw-bold text-warning-emphasis mt-1 mb-0">Rs {{ number_format($totalPendingAmount) }}</h3>
                <div class="mt-2 text-xs text-muted">
                    <span class="badge bg-warning text-dark rounded-pill">{{ $totalPendingCount }}</span> Pending {{ Str::plural('request', $totalPendingCount) }} awaiting transfer
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid var(--od-primary, #2F5D3A) !important;">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Disbursed</span>
                <h3 class="font-serif fw-bold text-dark-green mt-1 mb-0">Rs {{ number_format($totalCompletedAmount) }}</h3>
                <div class="mt-2 text-xs text-muted">
                    <span class="badge bg-success text-white rounded-pill">{{ $totalCompletedCount }}</span> Completed {{ Str::plural('payout', $totalCompletedCount) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <ul class="nav nav-pills mb-3 gap-2">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-1.5 fw-semibold {{ $currentStatus === 'all' ? 'active bg-darkgreen text-white' : 'bg-white text-dark border' }}" href="{{ route('admin.payouts.index', ['status' => 'all']) }}">
                All Requests
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-1.5 fw-semibold position-relative {{ $currentStatus === 'pending' ? 'active bg-darkgreen text-white' : 'bg-white text-dark border' }}" href="{{ route('admin.payouts.index', ['status' => 'pending']) }}">
                Pending Review
                @if($totalPendingCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill ms-1">{{ $totalPendingCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-1.5 fw-semibold {{ $currentStatus === 'completed' ? 'active bg-darkgreen text-white' : 'bg-white text-dark border' }}" href="{{ route('admin.payouts.index', ['status' => 'completed']) }}">
                Completed / Transferred
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-1.5 fw-semibold {{ $currentStatus === 'rejected' ? 'active bg-darkgreen text-white' : 'bg-white text-dark border' }}" href="{{ route('admin.payouts.index', ['status' => 'rejected']) }}">
                Declined
            </a>
        </li>
    </ul>

    <!-- Payouts Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        @if($payouts->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-wallet2 fs-1 text-gold d-block mb-3"></i>
                <h5 class="font-serif fw-bold text-dark">No Payout Requests Found</h5>
                <p class="small mb-0">No withdrawal requests matching your current status filter.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Ref #</th>
                            <th>Farmer &amp; Farm</th>
                            <th>Amount</th>
                            <th>Destination Account</th>
                            <th>Status</th>
                            <th>Date Requested</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payouts as $payout)
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'completed' => 'bg-success-subtle text-success border border-success-subtle',
                                    'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                ];
                                $badgeClass = $statusClasses[$payout->status] ?? 'bg-light text-dark';
                            @endphp
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-dark">
                                    {{ $payout->reference_no }}
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">&#127969; {{ $payout->seller->sellerProfile->farm_name ?? $payout->seller->name }}</div>
                                    <div class="text-xs text-muted">{{ $payout->seller->email }} &bull; {{ $payout->seller->phone }}</div>
                                </td>
                                <td class="fw-bold text-dark-green font-monospace fs-6">
                                    {{ $payout->formatted_amount }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1.5 mb-0.5">
                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;">
                                            {{ str_replace('_', ' ', $payout->payment_method) }}
                                        </span>
                                        @if($payout->bank_name)
                                            <span class="small text-muted fw-semibold">({{ $payout->bank_name }})</span>
                                        @endif
                                    </div>
                                    <div class="small fw-bold text-dark">{{ $payout->account_title }}</div>
                                    <div class="text-xs text-muted font-monospace">{{ $payout->account_number }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }} px-2.5 py-1 rounded-pill text-xs">
                                        {{ ucfirst($payout->status) }}
                                    </span>
                                    @if($payout->seller_notes)
                                        <div class="text-xs text-muted mt-1" title="{{ $payout->seller_notes }}">
                                            <i class="bi bi-chat-left-text me-1"></i> {{ Str::limit($payout->seller_notes, 28) }}
                                        </div>
                                    @endif
                                    @if($payout->isRejected() && $payout->admin_notes)
                                        <div class="text-xs text-danger mt-1">
                                            <strong>Declined:</strong> {{ $payout->admin_notes }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ $payout->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-muted">{{ $payout->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1.5 align-items-center">
                                        @if($payout->isPending())
                                            <!-- Approve Modal Button -->
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold text-xs shadow-xs" data-bs-toggle="modal" data-bs-target="#approveModal_{{ $payout->id }}">
                                                <i class="bi bi-check2-circle me-1"></i> Disburse
                                            </button>

                                            <!-- Reject Modal Button -->
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 fw-semibold text-xs" data-bs-toggle="modal" data-bs-target="#rejectModal_{{ $payout->id }}">
                                                <i class="bi bi-x-circle me-1"></i> Reject
                                            </button>

                                            <!-- Approve & Disburse Modal -->
                                            <div class="modal fade text-start" id="approveModal_{{ $payout->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                                                        <div class="modal-header border-bottom py-3 px-4 bg-success-subtle">
                                                            <div>
                                                                <h5 class="modal-title font-serif fw-bold text-success mb-0">
                                                                    <i class="bi bi-cash-coin me-1"></i> Disburse Payout #{{ $payout->reference_no }}
                                                                </h5>
                                                                <small class="text-muted">Farmer: <strong>{{ $payout->seller->sellerProfile->farm_name ?? $payout->seller->name }}</strong></small>
                                                            </div>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('admin.payouts.approve', $payout->id) }}" method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            <div class="modal-body p-4">
                                                                <div class="alert alert-light border rounded-3 p-3 mb-3">
                                                                    <div class="d-flex justify-content-between mb-1">
                                                                        <span class="text-muted small">Amount to Transfer:</span>
                                                                        <strong class="font-serif fs-5 text-dark-green">{{ $payout->formatted_amount }}</strong>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between mb-1">
                                                                        <span class="text-muted small">Channel:</span>
                                                                        <span class="fw-semibold text-dark text-uppercase">{{ str_replace('_', ' ', $payout->payment_method) }}</span>
                                                                    </div>
                                                                    @if($payout->bank_name)
                                                                        <div class="d-flex justify-content-between mb-1">
                                                                            <span class="text-muted small">Bank:</span>
                                                                            <span class="fw-semibold text-dark">{{ $payout->bank_name }}</span>
                                                                        </div>
                                                                    @endif
                                                                    <div class="d-flex justify-content-between mb-1">
                                                                        <span class="text-muted small">Account Title:</span>
                                                                        <span class="fw-semibold text-dark">{{ $payout->account_title }}</span>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between">
                                                                        <span class="text-muted small">Account Number / IBAN:</span>
                                                                        <strong class="font-monospace text-dark">{{ $payout->account_number }}</strong>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold text-dark">Bank / Wallet Transaction Reference (TRX ID) <span class="text-danger">*</span></label>
                                                                    <input type="text" name="transaction_reference" class="form-control font-monospace" placeholder="e.g. 98472910482 or FT241007..." required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold text-dark">Upload Transfer Receipt / Screenshot (Optional)</label>
                                                                    <input type="file" name="transaction_proof" class="form-control" accept="image/*,application/pdf">
                                                                    <small class="text-muted">Supports JPG, PNG, WEBP, PDF (Max 5MB)</small>
                                                                </div>

                                                                <div class="mb-2">
                                                                    <label class="form-label small fw-bold text-dark">Admin Internal Note (Optional)</label>
                                                                    <textarea name="admin_notes" class="form-control" rows="2" placeholder="e.g. Sent via Meezan Bank Online Transfer..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-top py-3 px-4 bg-light d-flex justify-content-between">
                                                                <button type="button" class="btn btn-light rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                                                                    <i class="bi bi-check2-circle me-1"></i> Confirm Transfer
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Reject Modal -->
                                            <div class="modal fade text-start" id="rejectModal_{{ $payout->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                                                        <div class="modal-header border-bottom py-3 px-4 bg-danger-subtle">
                                                            <h5 class="modal-title font-serif fw-bold text-danger mb-0">
                                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Decline Payout Request
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('admin.payouts.reject', $payout->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body p-4">
                                                                <p class="text-muted small mb-3">
                                                                    Declining this payout request will automatically restore <strong>{{ $payout->formatted_amount }}</strong> back to the farmer's available balance and notify them.
                                                                </p>
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold text-dark">Reason for Declining <span class="text-danger">*</span></label>
                                                                    <textarea name="admin_notes" class="form-control" rows="3" placeholder="e.g. Invalid account title / account number mismatch. Please update and re-submit." required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-top py-3 px-4 bg-light d-flex justify-content-between">
                                                                <button type="button" class="btn btn-light rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                                                                    <i class="bi bi-x-circle me-1"></i> Decline Request
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            @if($payout->transaction_proof)
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 text-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#adminProofModal_{{ $payout->id }}">
                                                    <i class="bi bi-receipt me-1"></i> Proof
                                                </button>

                                                <!-- Admin Proof Modal -->
                                                <div class="modal fade text-start" id="adminProofModal_{{ $payout->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content rounded-4 border-0 shadow">
                                                            <div class="modal-header border-bottom">
                                                                <h6 class="modal-title font-serif fw-bold text-dark-green">
                                                                    Transfer Proof #{{ $payout->reference_no }}
                                                                </h6>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body p-3 text-center">
                                                                <img src="{{ $payout->proof_url }}" alt="Transfer Proof" class="img-fluid rounded-3 shadow-sm" style="max-height: 480px;">
                                                                <div class="mt-3 small text-muted">
                                                                    TRX ID: <strong class="font-monospace text-dark">{{ $payout->transaction_reference }}</strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($payout->transaction_reference)
                                                <span class="badge bg-light text-muted border font-monospace text-xs" title="TRX: {{ $payout->transaction_reference }}">
                                                    TRX: {{ Str::limit($payout->transaction_reference, 12) }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $payouts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
