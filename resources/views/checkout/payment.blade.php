@extends('layouts.public')

@section('title', 'Complete Payment - Order #' . $orderGroup->group_no)

@section('content')
<div class="container py-4 py-md-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" class="text-decoration-none">Orders</a></li>
            <li class="breadcrumb-item active" aria-current="page">Real-Time Payment Transfer</li>
        </ol>
    </nav>

    <!-- Header Banner -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
        <div class="p-4 p-md-5 text-white position-relative" style="background: linear-gradient(135deg, #1F3A27 0%, #15271A 100%);">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold text-xs mb-2">
                        <i class="bi bi-shield-lock-fill me-1"></i> Official Direct Account Transfer
                    </span>
                    <h2 class="font-serif fw-bold mb-2">Complete Your Payment Transfer</h2>
                    <p class="text-white-50 mb-0 fs-6">
                        Order Group Reference: <strong class="text-white font-monospace">{{ $orderGroup->group_no }}</strong> &bull; Total Payable: <span class="badge bg-gold text-dark fs-6 px-3 py-1 fw-bold">Rs {{ number_format($orderGroup->grand_total) }}</span>
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-inline-flex flex-column align-items-lg-end">
                        <span class="text-xs text-white-50 text-uppercase fw-semibold mb-1">Payment Status</span>
                        @if($payment && $payment->isUnderReview())
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-6 fw-bold">
                                <i class="bi bi-hourglass-split me-1"></i> Under Admin Review
                            </span>
                        @elseif($payment && $payment->isRejected())
                            <span class="badge bg-danger text-white px-3 py-2 rounded-pill fs-6 fw-bold">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Verification Rejected
                            </span>
                        @else
                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill fs-6 fw-bold">
                                <i class="bi bi-clock-history me-1"></i> Awaiting Transfer
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('info'))
        <div class="alert alert-info rounded-4 border-0 shadow-sm mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
        </div>
    @endif

    @if($payment && $payment->isRejected())
        <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4 p-4">
            <div class="d-flex gap-3 align-items-start">
                <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bi bi-x-circle-fill fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Previous Payment Verification Declined</h5>
                    <p class="mb-2 text-danger-emphasis">
                        <strong>Reason from Administrator:</strong> {{ $payment->admin_notes ?? 'Transaction ID not verified or amount mismatch.' }}
                    </p>
                    <p class="small text-muted mb-0">Please check your bank statement, re-enter your valid Transaction ID below, or switch to Cash on Delivery.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Official Account Numbers & Instructions -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="font-serif fw-bold text-dark mb-0">
                            <i class="bi bi-wallet2 text-success me-2"></i> Step 1: Transfer Money to Seller
                        </h5>
                        <div class="small text-muted mt-0.5">
                            Recipient Farm: <strong class="text-dark-green">{{ $bankDetails['seller_name'] }}</strong>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 text-xs fw-bold">
                        <i class="bi bi-shield-check me-1"></i> Direct to Seller
                    </span>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">
                        {{ $bankDetails['payment_instructions'] }}
                    </p>

                    <!-- Account 1: Easypaisa -->
                    @if(!empty($bankDetails['easypaisa_number']))
                    <div class="p-3.5 rounded-4 border mb-3 bg-light transition-all hover-shadow">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 text-xs fw-bold">Easypaisa</span>
                                <strong class="text-dark">{{ $bankDetails['easypaisa_title'] ?: $bankDetails['seller_name'] }}</strong>
                            </div>
                            <span class="text-xs text-muted">Instant Mobile Wallet</span>
                        </div>
                        <div>
                            <small class="text-muted d-block text-xs">Easypaisa Mobile Number:</small>
                            <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded-3 border">
                                <span class="font-monospace fw-bold text-dark fs-6" id="easypaisaNo">{{ $bankDetails['easypaisa_number'] }}</span>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-0.5" onclick="copyToClipboard('{{ $bankDetails['easypaisa_number'] }}', this)">
                                    <i class="bi bi-clipboard me-1"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Account 2: JazzCash -->
                    @if(!empty($bankDetails['jazzcash_number']))
                    <div class="p-3.5 rounded-4 border mb-3 bg-light transition-all hover-shadow">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 text-xs fw-bold">JazzCash</span>
                                <strong class="text-dark">{{ $bankDetails['jazzcash_title'] ?: $bankDetails['seller_name'] }}</strong>
                            </div>
                            <span class="text-xs text-muted">Instant Mobile Wallet</span>
                        </div>
                        <div>
                            <small class="text-muted d-block text-xs">JazzCash Mobile Number:</small>
                            <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded-3 border">
                                <span class="font-monospace fw-bold text-dark fs-6" id="jazzcashNo">{{ $bankDetails['jazzcash_number'] }}</span>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-0.5" onclick="copyToClipboard('{{ $bankDetails['jazzcash_number'] }}', this)">
                                    <i class="bi bi-clipboard me-1"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Account 3: Bank Account -->
                    @if(!empty($bankDetails['bank_account_number']))
                    <div class="p-3.5 rounded-4 border mb-3 position-relative bg-light transition-all hover-shadow">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success text-white px-2.5 py-1 text-xs fw-bold">Bank Transfer</span>
                                <strong class="text-dark">{{ $bankDetails['bank_name'] }}</strong>
                            </div>
                            @if(!empty($bankDetails['bank_branch']))
                                <span class="text-xs text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $bankDetails['bank_branch'] }}</span>
                            @endif
                        </div>

                        <div class="mb-2">
                            <small class="text-muted d-block text-xs">Account Title:</small>
                            <span class="fw-bold text-dark">{{ $bankDetails['bank_account_title'] }}</span>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted d-block text-xs">Account Number:</small>
                            <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded-3 border">
                                <span class="font-monospace fw-bold text-darkgreen fs-6" id="bankAccNo">{{ $bankDetails['bank_account_number'] }}</span>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-0.5" onclick="copyToClipboard('{{ $bankDetails['bank_account_number'] }}', this)">
                                    <i class="bi bi-clipboard me-1"></i> Copy
                                </button>
                            </div>
                        </div>

                        @if(!empty($bankDetails['bank_iban']))
                            <div>
                                <small class="text-muted d-block text-xs">IBAN Number:</small>
                                <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded-3 border">
                                    <span class="font-monospace text-xs text-secondary text-break" id="bankIban">{{ $bankDetails['bank_iban'] }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 text-xs" onclick="copyToClipboard('{{ $bankDetails['bank_iban'] }}', this)">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- Order Summary Card -->
                    <div class="bg-white p-3 rounded-4 border mt-4">
                        <div class="d-flex justify-content-between align-items-center text-sm mb-1">
                            <span class="text-muted">Items Subtotal:</span>
                            <span class="fw-semibold">Rs {{ number_format($orderGroup->subtotal) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-sm mb-1">
                            <span class="text-muted">Cold-Chain Delivery:</span>
                            <span class="fw-semibold">Rs {{ number_format($orderGroup->delivery_total) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                            <span class="fw-bold text-dark">Exact Total to Transfer:</span>
                            <span class="fw-bold text-success fs-5">Rs {{ number_format($orderGroup->grand_total) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Buyer Proof Submission Form -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="font-serif fw-bold text-dark mb-0">
                        <i class="bi bi-receipt-cutoff text-gold me-2"></i> Step 2: Submit Your Transaction ID
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if($payment && $payment->isUnderReview())
                        <div class="card border-warning bg-warning-subtle p-4 rounded-4 mb-4 text-center">
                            <div class="rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 54px; height: 54px;">
                                <i class="bi bi-hourglass-split fs-3"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Payment Proof Already Submitted!</h5>
                            <p class="small text-muted mb-2">
                                Transaction ID: <strong class="text-dark font-monospace">{{ $payment->transaction_id ?? $payment->gateway_ref }}</strong> &bull; Channel: <strong>{{ ucfirst(str_replace('_', ' ', $payment->payment_channel ?? 'Online')) }}</strong>
                            </p>
                            <p class="small text-muted mb-3">Our admin team is actively verifying this transfer with our bank statement. You can re-submit if you made any error below.</p>
                            <a href="{{ route('checkout.success', $orderGroup->group_no) }}" class="btn btn-outline-dark rounded-pill px-4 py-2 text-sm fw-bold">
                                View Order Status <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @endif

                    <form action="{{ route('checkout.payment.submit', $orderGroup->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">
                                Payment Channel / Method Used <span class="text-danger">*</span>
                            </label>
                            <select name="payment_channel" class="form-select @error('payment_channel') is-invalid @enderror" required>
                                <option value="meezan_bank" {{ old('payment_channel', $payment->payment_channel ?? 'meezan_bank') === 'meezan_bank' ? 'selected' : '' }}>Meezan Bank Direct Transfer</option>
                                <option value="bank_transfer" {{ old('payment_channel', $payment->payment_channel ?? '') === 'bank_transfer' ? 'selected' : '' }}>Other Bank (1Link / IBFT)</option>
                                <option value="easypaisa" {{ old('payment_channel', $payment->payment_channel ?? '') === 'easypaisa' ? 'selected' : '' }}>Easypaisa Account Transfer</option>
                                <option value="jazzcash" {{ old('payment_channel', $payment->payment_channel ?? '') === 'jazzcash' ? 'selected' : '' }}>JazzCash Account Transfer</option>
                                <option value="nayapay" {{ old('payment_channel', $payment->payment_channel ?? '') === 'nayapay' ? 'selected' : '' }}>NayaPay / SadaPay</option>
                                <option value="other" {{ old('payment_channel', $payment->payment_channel ?? '') === 'other' ? 'selected' : '' }}>Other Digital Channel</option>
                            </select>
                            @error('payment_channel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">
                                    Your Name on Bank/Wallet Account <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="sender_name" class="form-control @error('sender_name') is-invalid @enderror" value="{{ old('sender_name', $payment->sender_name ?? Auth::user()->name) }}" placeholder="e.g. Muhammad Ali" required>
                                @error('sender_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">
                                    Your Account / Mobile No. <span class="text-muted">(Optional)</span>
                                </label>
                                <input type="text" name="sender_account" class="form-control @error('sender_account') is-invalid @enderror" value="{{ old('sender_account', $payment->sender_account ?? Auth::user()->phone) }}" placeholder="e.g. 0345-0000000">
                                @error('sender_account')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">
                                Transaction ID / TRX ID / Reference Code <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-hash"></i></span>
                                <input type="text" name="transaction_id" class="form-control form-control-lg font-monospace @error('transaction_id') is-invalid @enderror" value="{{ old('transaction_id', $payment->transaction_id ?? '') }}" placeholder="e.g. 84729103948" required>
                            </div>
                            <div class="form-text text-muted">Found in your bank app confirmation SMS or transfer receipt.</div>
                            @error('transaction_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark">
                                Upload Receipt Screenshot <span class="text-muted">(Optional but Recommended)</span>
                            </label>
                            <input type="file" name="proof_image" class="form-control @error('proof_image') is-invalid @enderror" accept="image/*">
                            <div class="form-text text-muted">Upload PNG, JPG, or JPEG image (max 5MB).</div>
                            @error('proof_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if($payment && $payment->proof_image)
                                <div class="mt-2 small text-muted">
                                    <i class="bi bi-paperclip me-1"></i> Current proof: <a href="{{ $payment->proof_image_url }}" target="_blank" class="text-success fw-semibold">View Uploaded Image</a>
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-darkgreen btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm mb-3">
                            <i class="bi bi-send-check me-2"></i> Submit Payment Verification
                        </button>
                    </form>

                    <!-- Alternative: Switch to Cash on Delivery -->
                    <div class="text-center pt-3 border-top">
                        <span class="text-muted small">Prefer to pay in cash when the order arrives?</span>
                        <form action="{{ route('checkout.payment.switch-cod', $orderGroup->id) }}" method="POST" class="d-inline ms-1">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 text-success fw-bold small text-decoration-none">
                                Switch to Cash on Delivery (COD) &rarr;
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text, btn) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
        btn.classList.replace('btn-outline-success', 'btn-success');
        btn.classList.replace('btn-outline-danger', 'btn-danger');
        btn.classList.replace('btn-outline-secondary', 'btn-secondary');
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.replace('btn-success', 'btn-outline-success');
            btn.classList.replace('btn-danger', 'btn-outline-danger');
            btn.classList.replace('btn-secondary', 'btn-outline-secondary');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
}
</script>
@endsection
