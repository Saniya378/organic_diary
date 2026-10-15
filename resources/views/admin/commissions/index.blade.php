@extends('layouts.panel')

@section('title', 'Seller Commission Collections - Admin Portal')
@section('page_heading', 'Seller Commission Collections')

@section('menu_items')
    @include('admin.partials.menu')
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

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill fs-5"></i>
                <div>{{ session('info') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Cleared Commission -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid #2D5A3C !important;">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Commission Collected</span>
                <h3 class="font-serif fw-bold text-success mt-1 mb-0">Rs {{ number_format($totalApprovedSum) }}</h3>
                <div class="small text-muted mt-2">
                    Verified and cleared from sellers
                </div>
            </div>
        </div>

        <!-- Pending Review -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden" style="border-left: 5px solid #E0A93B !important;">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Awaiting Verification</span>
                <h3 class="font-serif fw-bold text-warning-emphasis mt-1 mb-0">Rs {{ number_format($totalPendingSum) }}</h3>
                <div class="small text-muted mt-2">
                    <span class="badge bg-warning text-dark rounded-pill">{{ $totalPendingCount }}</span> submissions pending verification
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3">
        <form action="{{ route('admin.commissions.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ $search }}" class="form-control border-start-0 ps-0" placeholder="Search by TRX ID, Farmer Name...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>Pending Verification</option>
                    <option value="approved" {{ $currentStatus === 'approved' ? 'selected' : '' }}>Approved &amp; Cleared</option>
                    <option value="rejected" {{ $currentStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('admin.commissions.index') }}" class="btn btn-light rounded-pill px-3 me-2">Reset</a>
                <button type="submit" class="btn btn-darkgreen rounded-pill px-4">Filter</button>
            </div>
        </form>
    </div>

    <!-- Table of Settlements -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small">
                        <th>Date</th>
                        <th>Farmer / Seller</th>
                        <th>Amount</th>
                        <th>Channel</th>
                        <th>TRX ID</th>
                        <th>Receipt</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settlements as $item)
                        <tr>
                            <td>{{ $item->created_at->format('M d, Y h:i A') }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->seller->sellerProfile->farm_name ?? $item->seller->name }}</div>
                                <div class="small text-muted">{{ $item->seller->email }} &bull; {{ $item->seller->phone }}</div>
                            </td>
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
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Approved</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">Rejected</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($item->isPending())
                                    <div class="d-inline-flex gap-1">
                                        <!-- Approve Form -->
                                        <form action="{{ route('admin.commissions.approve', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success rounded-pill px-3" onclick="return confirm('Verify and approve this commission deposit of Rs {{ number_format($item->amount) }}?')">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </form>

                                        <!-- Reject Trigger Modal Button -->
                                        <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2.5" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $item->id }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0">
                                                <form action="{{ route('admin.commissions.reject', $item->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header border-light">
                                                        <h5 class="modal-title font-serif fw-bold text-dark">Reject Commission Submission</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <p class="small text-muted mb-3">Please specify the reason for rejection (e.g. invalid TRX ID or funds not received in bank):</p>
                                                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Reason for rejection..." required></textarea>
                                                    </div>
                                                    <div class="modal-footer border-light">
                                                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4">Confirm Rejection</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="small text-muted">{{ $item->verified_at ? $item->verified_at->format('M d') : '-' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No commission payments found matching current filters.
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
