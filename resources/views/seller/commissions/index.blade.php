@extends('layouts.panel')

@section('title', 'Platform Commission Ledger - Farmer Portal')
@section('page_heading', 'Admin Platform Commission')

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Direct Sales & Commission Summary Metrics -->
    <div class="row g-4 mb-4">
        <!-- Direct Customer Sales Received -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid #2D5A3C !important;">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Customer Payments Received</span>
                <h3 class="font-serif fw-bold text-success mt-1 mb-0">Rs {{ number_format($totalDirectSales) }}</h3>
                <div class="small text-muted mt-2">
                    Directly deposited into your account / cash
                </div>
            </div>
        </div>

        <!-- Total Admin Commission Accrued -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid #198754 !important;">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Product-Base Commission</span>
                <h3 class="font-serif fw-bold text-dark mt-1 mb-0">Rs {{ number_format($totalCommissionAccrued) }}</h3>
                <div class="small text-muted mt-2">
                    Accrued on all delivered customer orders
                </div>
            </div>
        </div>

        <!-- Commission Settled / Paid -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid #0D6EFD !important;">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Commission Paid to Admin</span>
                <h3 class="font-serif fw-bold text-primary mt-1 mb-0">Rs {{ number_format($totalCommissionSettled) }}</h3>
                <div class="small text-muted mt-2">
                    Verified and cleared by administration
                </div>
            </div>
        </div>

        <!-- Outstanding Balance Due -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid {{ $outstandingDue > 0 ? '#DC3545' : '#2D5A3C' }} !important;">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Current Outstanding Due</span>
                <h3 class="font-serif fw-bold {{ $outstandingDue > 0 ? 'text-danger' : 'text-success' }} mt-1 mb-0">
                    Rs {{ number_format($outstandingDue) }}
                </h3>
                <div class="small text-muted mt-2">
                    @if($pendingCommissionSettlements > 0)
                        <span class="badge bg-warning text-dark text-xs">Rs {{ number_format($pendingCommissionSettlements) }} under review</span>
                    @else
                        {{ $outstandingDue > 0 ? 'Payable to website administration' : 'All platform commissions cleared' }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Section: Pay Commission Card & Official Accounts -->
    <div class="row g-4 mb-4">
        <!-- Submit Commission Payment Proof -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-serif fw-bold text-dark mb-0">
                        <i class="bi bi-send-check text-success me-2"></i> Deposit Commission to Admin
                    </h5>
                    @if($outstandingDue > 0)
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill text-xs fw-bold">Payment Due</span>
                    @endif
                </div>
                <p class="small text-muted mb-4">
                    Transfer the commission amount to any official administration account on the right, and submit your Transaction ID (TRX ID) below for verification.
                </p>

                <form action="{{ route('seller.commissions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="amount" class="form-label small fw-bold text-dark">Amount to Pay (Rs) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rs</span>
                            <input type="number" min="1" max="1000000" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $outstandingDue > 0 ? $outstandingDue : '') }}" placeholder="e.g. 500" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_channel" class="form-label small fw-bold text-dark">Transfer Channel <span class="text-danger">*</span></label>
                        <select name="payment_channel" id="payment_channel" class="form-select @error('payment_channel') is-invalid @enderror" required>
                            <option value="easypaisa" {{ old('payment_channel') === 'easypaisa' ? 'selected' : '' }}>EasyPaisa</option>
                            <option value="jazzcash" {{ old('payment_channel') === 'jazzcash' ? 'selected' : '' }}>JazzCash</option>
                            <option value="sadapay" {{ old('payment_channel') === 'sadapay' ? 'selected' : '' }}>SadaPay</option>
                            <option value="nayapay" {{ old('payment_channel') === 'nayapay' ? 'selected' : '' }}>NayaPay</option>
                            <option value="bank_transfer" {{ old('payment_channel') === 'bank_transfer' ? 'selected' : '' }}>Bank Account Transfer</option>
                        </select>
                        @error('payment_channel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="transaction_id" class="form-label small fw-bold text-dark">Transaction ID / TRX ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace @error('transaction_id') is-invalid @enderror" id="transaction_id" name="transaction_id" value="{{ old('transaction_id') }}" placeholder="e.g. 29384729103" required>
                        @error('transaction_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="proof_image" class="form-label small fw-bold text-dark">Payment Screenshot (Optional)</label>
                        <input type="file" class="form-control form-control-sm @error('proof_image') is-invalid @enderror" id="proof_image" name="proof_image" accept="image/jpeg,image/png,image/webp">
                        @error('proof_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="seller_notes" class="form-label small fw-bold text-dark">Notes (Optional)</label>
                        <textarea class="form-control form-control-sm" id="seller_notes" name="seller_notes" rows="2" placeholder="e.g. Commission for Order batch OD-20501 to OD-20510">{{ old('seller_notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-darkgreen rounded-pill px-4 py-2 w-100 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Submit Commission Payment
                    </button>
                </form>
            </div>
        </div>

        <!-- Official Administration Accounts -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h5 class="font-serif fw-bold text-dark mb-3">
                    <i class="bi bi-bank text-primary me-2"></i> Official Admin Accounts
                </h5>
                <p class="small text-muted mb-3">
                    Please transfer commission dues to any of the following accounts:
                </p>

                <!-- Bank -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-primary text-white text-xs fw-bold">Bank Account</span>
                        <small class="text-muted">{{ $adminBankDetails['bank_name'] }}</small>
                    </div>
                    <div class="small fw-semibold text-dark">{{ $adminBankDetails['bank_account_title'] }}</div>
                    <div class="font-monospace fw-bold text-darkgreen fs-6 my-1">{{ $adminBankDetails['bank_account_number'] }}</div>
                    @if(!empty($adminBankDetails['bank_iban']))
                        <div class="text-xs text-muted font-monospace">IBAN: {{ $adminBankDetails['bank_iban'] }}</div>
                    @endif
                </div>

                <!-- EasyPaisa -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-success text-white text-xs fw-bold">EasyPaisa</span>
                        <small class="text-muted">{{ $adminBankDetails['easypaisa_title'] }}</small>
                    </div>
                    <div class="font-monospace fw-bold text-dark fs-6">{{ $adminBankDetails['easypaisa_number'] }}</div>
                </div>

                <!-- JazzCash -->
                <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-danger text-white text-xs fw-bold">JazzCash</span>
                        <small class="text-muted">{{ $adminBankDetails['jazzcash_title'] }}</small>
                    </div>
                    <div class="font-monospace fw-bold text-dark fs-6">{{ $adminBankDetails['jazzcash_number'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement History Table -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h5 class="font-serif fw-bold text-dark mb-0">Commission Settlement History</h5>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small">
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Channel</th>
                        <th>TRX ID</th>
                        <th>Receipt</th>
                        <th>Status</th>
                        <th>Admin Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settlements as $item)
                        <tr>
                            <td>{{ $item->created_at->format('M d, Y h:i A') }}</td>
                            <td class="fw-bold text-dark">Rs {{ number_format($item->amount) }}</td>
                            <td class="text-uppercase small">{{ str_replace('_', ' ', $item->payment_channel) }}</td>
                            <td class="font-monospace small">{{ $item->transaction_id }}</td>
                            <td>
                                @if($item->proof_image)
                                    <a href="{{ Storage::url($item->proof_image) }}" target="_blank" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5">
                                        <i class="bi bi-image"></i> View
                                    </a>
                                @else
                                    <span class="text-muted text-xs">No receipt</span>
                                @endif
                            </td>
                            <td>
                                @if($item->isPending())
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">Under Review</span>
                                @elseif($item->isApproved())
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Approved &amp; Cleared</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">Rejected</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $item->admin_notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted small">
                                No commission payment submissions yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($settlements->hasPages())
            <div class="p-3 border-top">
                {{ $settlements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
