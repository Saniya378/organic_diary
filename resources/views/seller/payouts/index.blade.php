@extends('layouts.panel')

@section('title', 'Payouts & Earnings Wallet - Farmer Portal')
@section('page_heading', 'Payouts & Wallet')

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Breadcrumb & Top Action -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payouts &amp; Wallet</li>
                </ol>
            </nav>
            <h4 class="font-serif fw-bold text-dark mb-0">Farmer Earnings &amp; Withdrawal Center</h4>
        </div>
        <div>
            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#requestPayoutModal" {{ $availableBalance < 500 ? 'disabled' : '' }}>
                <i class="bi bi-wallet2 me-1.5"></i> Request Payout
            </button>
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

    @if($errors->any())
        <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 4-Card Balance Overview -->
    <div class="row g-3 mb-4">
        <!-- Available for Withdrawal -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid var(--od-primary, #2F5D3A) !important;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Withdrawable Balance</span>
                        <h3 class="font-serif fw-bold text-dark-green mt-1 mb-0">Rs {{ number_format($availableBalance) }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5 bg-success-subtle text-success">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                </div>
                <div class="mt-3 small text-muted">
                    @if($availableBalance >= 500)
                        <span class="text-success fw-semibold"><i class="bi bi-check2-circle"></i> Ready to withdraw (Min Rs 500)</span>
                    @else
                        <span class="text-muted"><i class="bi bi-info-circle"></i> Minimum withdrawal is Rs 500</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Payouts -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid #E0A93B !important;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Under Admin Review</span>
                        <h3 class="font-serif fw-bold text-warning-emphasis mt-1 mb-0">Rs {{ number_format($pendingPayouts) }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5 bg-warning-subtle text-warning-emphasis">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
                <div class="mt-3 small text-muted">
                    <span>Funds being processed by bank / admin</span>
                </div>
            </div>
        </div>

        <!-- Total Transferred / Paid -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid #0d6efd !important;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Withdrawn</span>
                        <h3 class="font-serif fw-bold text-primary mt-1 mb-0">Rs {{ number_format($completedPayouts) }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5 bg-primary-subtle text-primary">
                        <i class="bi bi-check-all fs-4"></i>
                    </div>
                </div>
                <div class="mt-3 small text-muted">
                    <span>Successfully deposited to your account</span>
                </div>
            </div>
        </div>

        <!-- Lifetime Net Earnings -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid #6c757d !important;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Lifetime Farm Profit</span>
                        <h3 class="font-serif fw-bold text-dark mt-1 mb-0">Rs {{ number_format($lifetimeEarnings) }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5 bg-light text-secondary">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                </div>
                <div class="mt-3 small text-muted">
                    <span>Total delivered orders (net sales)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- COD vs Online Commission Settlement Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h6 class="font-serif fw-bold text-dark-green mb-1">
                    <i class="bi bi-arrow-left-right text-gold me-2"></i> Automated COD Commission Settlement Ledger
                </h6>
                <p class="small text-muted mb-0">
                    When customers pay via Cash on Delivery (COD), your delivery rider collects 100% of the cash. The platform commission on COD orders is automatically offset against your online order credits.
                </p>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small">
                <i class="bi bi-shield-check me-1"></i> Auto-Settled
            </span>
        </div>

        <div class="row g-3 pt-2">
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border h-100">
                    <div class="small text-muted fw-semibold mb-1"><i class="bi bi-credit-card me-1 text-primary"></i> Online Sales Held by Platform</div>
                    <div class="fs-5 fw-bold text-primary">Rs {{ number_format($onlineEarningsHeld) }}</div>
                    <div class="text-xs text-muted mt-1">Orders paid via Online Transfer / Card / Wallet (net of fee).</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border h-100">
                    <div class="small text-muted fw-semibold mb-1"><i class="bi bi-cash-coin me-1 text-danger"></i> COD Commission Deducted</div>
                    <div class="fs-5 fw-bold text-danger">- Rs {{ number_format($codCommissionOwed) }}</div>
                    <div class="text-xs text-muted mt-1">Platform commission on COD orders collected directly by your riders.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border h-100 {{ $outstandingDues > 0 ? 'border-danger bg-danger-subtle' : 'border-success bg-success-subtle' }}">
                    <div class="small fw-semibold mb-1 {{ $outstandingDues > 0 ? 'text-danger' : 'text-success' }}">
                        <i class="bi {{ $outstandingDues > 0 ? 'bi-exclamation-octagon' : 'bi-check-circle' }} me-1"></i> 
                        {{ $outstandingDues > 0 ? 'Outstanding COD Dues' : 'Net Platform Balance' }}
                    </div>
                    <div class="fs-5 fw-bold {{ $outstandingDues > 0 ? 'text-danger' : 'text-success' }}">
                        Rs {{ number_format($outstandingDues > 0 ? $outstandingDues : ($onlineEarningsHeld - $codCommissionOwed)) }}
                    </div>
                    <div class="text-xs text-muted mt-1">
                        {{ $outstandingDues > 0 ? 'Pending platform commission to be offset against future online orders.' : 'Net balance ready for withdrawal.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payout Request History Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom border-light d-flex justify-content-between align-items-center">
            <h5 class="font-serif fw-bold text-dark-green mb-0">
                <i class="bi bi-clock-history me-1.5 text-gold"></i> Withdrawal History
            </h5>
            <span class="badge bg-light text-dark border">
                {{ $payouts->total() }} Total {{ Str::plural('Request', $payouts->total()) }}
            </span>
        </div>

        @if($payouts->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-wallet2 fs-1 text-gold d-block mb-3"></i>
                <h5 class="font-serif fw-bold text-dark">No Withdrawal Requests Yet</h5>
                <p class="small mb-3" style="max-width: 420px; margin: 0 auto;">
                    When your customers' orders are marked delivered, your net earnings will accumulate in your available balance. You can withdraw directly to EasyPaisa, JazzCash, or your Bank Account.
                </p>
                @if($availableBalance >= 500)
                    <button type="button" class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#requestPayoutModal">
                        <i class="bi bi-plus-circle me-1"></i> Submit First Request
                    </button>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Ref #</th>
                            <th>Date Requested</th>
                            <th>Amount</th>
                            <th>Destination Account</th>
                            <th>Status</th>
                            <th>Admin TRX Reference</th>
                            <th class="text-end pe-4">Action</th>
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
                                    <div class="small fw-semibold text-dark">{{ $payout->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-muted">{{ $payout->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="fw-bold text-dark-green font-monospace">
                                    {{ $payout->formatted_amount }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1.5 mb-0.5">
                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;">
                                            {{ str_replace('_', ' ', $payout->payment_method) }}
                                        </span>
                                        @if($payout->bank_name)
                                            <span class="small text-muted">({{ $payout->bank_name }})</span>
                                        @endif
                                    </div>
                                    <div class="small fw-bold text-dark">{{ $payout->account_title }}</div>
                                    <div class="text-xs text-muted font-monospace">{{ $payout->account_number }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }} px-2.5 py-1 rounded-pill text-xs">
                                        @if($payout->isPending())
                                            <i class="bi bi-hourglass-split me-1"></i> Under Review
                                        @elseif($payout->isCompleted())
                                            <i class="bi bi-check-circle-fill me-1"></i> Transferred
                                        @else
                                            <i class="bi bi-x-circle-fill me-1"></i> Declined
                                        @endif
                                    </span>
                                    @if($payout->isRejected() && $payout->admin_notes)
                                        <div class="text-xs text-danger mt-1" style="max-width: 220px;">
                                            <strong>Reason:</strong> {{ $payout->admin_notes }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($payout->transaction_reference)
                                        <div class="font-monospace small fw-bold text-primary">{{ $payout->transaction_reference }}</div>
                                        <div class="text-xs text-muted">Transferred: {{ $payout->processed_at?->format('M d, Y') }}</div>
                                    @else
                                        <span class="text-muted text-xs">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1.5 align-items-center">
                                        @if($payout->transaction_proof)
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 text-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#proofModal_{{ $payout->id }}">
                                                <i class="bi bi-receipt me-1"></i> Receipt
                                            </button>

                                            <!-- Proof Modal -->
                                            <div class="modal fade" id="proofModal_{{ $payout->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content rounded-4 border-0 shadow">
                                                        <div class="modal-header border-bottom">
                                                            <h6 class="modal-title font-serif fw-bold text-dark-green">
                                                                Transfer Receipt #{{ $payout->reference_no }}
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

                                        @if($payout->isPending())
                                            <form action="{{ route('seller.payouts.cancel', $payout->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this payout request?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 text-xs fw-semibold">
                                                    Cancel
                                                </button>
                                            </form>
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

<!-- Request Payout Modal -->
<div class="modal fade" id="requestPayoutModal" tabindex="-1" aria-labelledby="requestPayoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4" style="background-color: #FAF7F0;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle p-2 bg-success-subtle text-success">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-serif fw-bold text-dark-green mb-0" id="requestPayoutModalLabel">Request Fund Withdrawal</h5>
                        <small class="text-muted">Available: <strong>Rs {{ number_format($availableBalance) }}</strong></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('seller.payouts.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Amount Input -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Withdrawal Amount (PKR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light font-monospace fw-bold">Rs</span>
                            <input type="number" name="amount" class="form-control font-monospace fw-bold fs-5" min="500" max="{{ $availableBalance }}" value="{{ old('amount', min($availableBalance, 1000)) }}" placeholder="500" required>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Min: Rs 500</small>
                            <small class="text-primary cursor-pointer fw-semibold" onclick="document.querySelector('input[name=amount]').value = {{ $availableBalance }}">
                                Withdraw Max (Rs {{ number_format($availableBalance) }})
                            </small>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Payout Destination Channel <span class="text-danger">*</span></label>
                        <select name="payment_method" id="sellerPaymentMethod" class="form-select" required onchange="toggleBankField(this.value)">
                            <option value="easypaisa" {{ old('payment_method', $lastPayout->payment_method ?? '') === 'easypaisa' ? 'selected' : '' }}>EasyPaisa</option>
                            <option value="jazzcash" {{ old('payment_method', $lastPayout->payment_method ?? '') === 'jazzcash' ? 'selected' : '' }}>JazzCash</option>
                            <option value="sadapay" {{ old('payment_method', $lastPayout->payment_method ?? '') === 'sadapay' ? 'selected' : '' }}>SadaPay</option>
                            <option value="nayapay" {{ old('payment_method', $lastPayout->payment_method ?? '') === 'nayapay' ? 'selected' : '' }}>NayaPay</option>
                            <option value="bank_transfer" {{ old('payment_method', $lastPayout->payment_method ?? '') === 'bank_transfer' ? 'selected' : '' }}>Bank Account Transfer</option>
                        </select>
                    </div>

                    <!-- Bank Name (Conditional) -->
                    <div class="mb-3" id="bankNameContainer" style="display: {{ old('payment_method', $lastPayout->payment_method ?? '') === 'bank_transfer' ? 'block' : 'none' }};">
                        <label class="form-label small fw-bold text-dark">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" id="bankNameInput" class="form-control" placeholder="e.g. Meezan Bank, HBL, Bank of Punjab" value="{{ old('bank_name', $lastPayout->bank_name ?? '') }}">
                    </div>

                    <!-- Account Title -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Account Holder Name (Title) <span class="text-danger">*</span></label>
                        <input type="text" name="account_title" class="form-control" placeholder="Exact name as on bank/wallet account" value="{{ old('account_title', $lastPayout->account_title ?? Auth::user()->name) }}" required>
                    </div>

                    <!-- Account Number / Mobile / IBAN -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Account Number / Mobile / IBAN <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" class="form-control font-monospace" placeholder="e.g. 03451234567 or PK36MEZN..." value="{{ old('account_number', $lastPayout->account_number ?? Auth::user()->phone) }}" required>
                    </div>

                    <!-- Seller Notes -->
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark">Additional Notes (Optional)</label>
                        <textarea name="seller_notes" class="form-control" rows="2" placeholder="Any specific instructions for finance team...">{{ old('seller_notes') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer border-top py-3 px-4 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleBankField(method) {
    const container = document.getElementById('bankNameContainer');
    const input = document.getElementById('bankNameInput');
    if (method === 'bank_transfer') {
        container.style.display = 'block';
        input.required = true;
    } else {
        container.style.display = 'none';
        input.required = false;
    }
}
</script>
@endsection
