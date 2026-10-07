@extends('layouts.panel')

@section('title', 'Manage Sellers - Admin Center')
@section('page_heading', 'Farmer & Seller Verification')

@section('menu_items')
    @include('admin.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <ul class="nav nav-pills gap-1 flex-wrap">
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ !$currentStatus ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.sellers.index') }}">
                        All Sellers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'pending' ? 'active bg-warning text-dark' : 'text-dark' }}" href="{{ route('admin.sellers.index', ['status' => 'pending']) }}">
                        Pending Verification
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'approved' ? 'active bg-success' : 'text-dark' }}" href="{{ route('admin.sellers.index', ['status' => 'approved']) }}">
                        Approved
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'suspended' ? 'active bg-danger' : 'text-dark' }}" href="{{ route('admin.sellers.index', ['status' => 'suspended']) }}">
                        Suspended
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'rejected' ? 'active bg-secondary' : 'text-dark' }}" href="{{ route('admin.sellers.index', ['status' => 'rejected']) }}">
                        Rejected
                    </a>
                </li>
            </ul>

            <form action="{{ route('admin.sellers.index') }}" method="GET" class="d-flex gap-2">
                @if($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="input-group input-group-sm" style="min-width: 240px;">
                    <input type="text" name="q" class="form-control" placeholder="Search farm, owner, city..." value="{{ $search }}">
                    <button type="submit" class="btn btn-outline-darkgreen">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sellers Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        @if($sellers->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-person-x fs-1 text-gold d-block mb-3"></i>
                <h5 class="font-serif fw-bold text-dark">No Sellers Found</h5>
                <p class="small mb-0">No farm accounts matching current filters.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Farm Details</th>
                            <th>Owner & Contact</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                            <th class="text-end pe-4">Verification Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sellers as $seller)
                            @php
                                $statusBadges = [
                                    'approved' => 'bg-success-subtle text-success border border-success-subtle',
                                    'pending' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'suspended' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    'rejected' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                ];
                                $badgeClass = $statusBadges[$seller->status] ?? 'bg-light text-dark';
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark-green font-serif fs-6">&#127969; {{ $seller->farm_name }}</div>
                                    <div class="small text-muted text-truncate" style="max-width: 280px;">{{ $seller->description }}</div>
                                    @if($seller->rejection_reason)
                                        <div class="small text-danger mt-1">
                                            <strong>Rejection Note:</strong> {{ $seller->rejection_reason }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $seller->user->name }}</div>
                                    <small class="text-muted d-block">{{ $seller->user->email }}</small>
                                    <small class="text-secondary">{{ $seller->user->phone }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-geo-alt me-1"></i> {{ $seller->location }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }} px-2 py-1 rounded-pill small">
                                        {{ ucfirst($seller->status) }}
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    {{ $seller->created_at->format('M d, Y') }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        @if($seller->status !== 'approved')
                                            <form action="{{ route('admin.sellers.approve', $seller->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-2" title="Approve Farm">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                            </form>
                                        @endif

                                        @if($seller->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#rejectModal_{{ $seller->id }}">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        @endif

                                        @if($seller->status === 'approved')
                                            <form action="{{ route('admin.sellers.suspend', $seller->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to suspend this seller? Their products will be hidden from the shop.');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Suspend Farm">
                                                    <i class="bi bi-slash-circle"></i> Suspend
                                                </button>
                                            </form>
                                        @endif

                                        @if($seller->status === 'suspended')
                                            <form action="{{ route('admin.sellers.reactivate', $seller->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-2" title="Reactivate Farm">
                                                    <i class="bi bi-arrow-clockwise"></i> Reactivate
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <!-- Rejection Modal -->
                                    <div class="modal fade" id="rejectModal_{{ $seller->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title font-serif text-danger">Reject Farm Application</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('admin.sellers.reject', $seller->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body text-start">
                                                        <p class="small text-muted mb-3">
                                                            Please provide feedback for <strong>{{ $seller->farm_name }}</strong>. This explanation will be emailed to the farmer.
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                                                            <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Reason for rejection..." required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold">Confirm Rejection</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top d-flex justify-content-center">
                {{ $sellers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
