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
                            <th>CNIC Document</th>
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
                                $hasCnic = !empty($seller->cnic_front) || !empty($seller->cnic_back) || !empty($seller->cnic_no);
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark-green font-serif fs-6">&#127969; {{ $seller->farm_name }}</div>
                                    <div class="small text-muted text-truncate" style="max-width: 250px;">{{ $seller->description }}</div>
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
                                    @if($hasCnic)
                                        <div class="d-flex flex-column gap-1 align-items-start">
                                            <span class="badge bg-light text-dark font-monospace border px-2 py-1" style="font-size: 0.78rem;">
                                                <i class="bi bi-person-vcard text-primary me-1"></i> {{ $seller->cnic_no ?? 'No Number' }}
                                            </span>
                                            <button type="button" class="btn btn-sm btn-outline-darkgreen rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1 shadow-2xs" data-bs-toggle="modal" data-bs-target="#cnicModal_{{ $seller->id }}" title="Inspect Real CNIC Document">
                                                <i class="bi bi-eye-fill text-success"></i>
                                                <span class="small fw-semibold">Inspect CNIC</span>
                                                @if($seller->cnic_front && $seller->cnic_back)
                                                    <span class="badge bg-success text-white rounded-pill ms-1" style="font-size: 0.62rem;">Front & Back</span>
                                                @elseif($seller->cnic_front || $seller->cnic_back)
                                                    <span class="badge bg-warning text-dark rounded-pill ms-1" style="font-size: 0.62rem;">1 Card</span>
                                                @endif
                                            </button>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1 small">
                                            <i class="bi bi-dash-circle me-1"></i> No CNIC Uploaded
                                        </span>
                                    @endif
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
                                        @if($seller->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#cnicModal_{{ $seller->id }}" title="Review CNIC & Approve">
                                                <i class="bi bi-patch-check-fill"></i> Review & Approve
                                            </button>
                                        @elseif($seller->status !== 'approved')
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
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#cnicModal_{{ $seller->id }}" title="View CNIC">
                                                <i class="bi bi-person-vcard"></i> CNIC
                                            </button>
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

                                    <!-- CNIC Full Inspection & Verification Modal -->
                                    <div class="modal fade" id="cnicModal_{{ $seller->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-bottom bg-light">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-dark" style="width: 36px; height: 36px; background-color: var(--od-gold, #E3A83F);">
                                                            <i class="bi bi-person-vcard-fill fs-5"></i>
                                                        </div>
                                                        <div class="text-start">
                                                            <h5 class="modal-title font-serif text-dark-green fw-bold mb-0">Farmer CNIC Verification</h5>
                                                            <small class="text-muted">{{ $seller->farm_name }} &bull; {{ $seller->user->name }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge {{ $badgeClass }} px-3 py-1 rounded-pill">
                                                            {{ ucfirst($seller->status) }}
                                                        </span>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                </div>

                                                <div class="modal-body text-start p-4">
                                                    <!-- Farmer Summary Card -->
                                                    <div class="card border rounded-4 p-3 bg-light mb-4">
                                                        <div class="row g-3">
                                                            <div class="col-sm-6 col-md-3">
                                                                <small class="text-muted d-block" style="font-size: 0.72rem;">FARMER NAME</small>
                                                                <span class="fw-bold text-dark">{{ $seller->user->name }}</span>
                                                            </div>
                                                            <div class="col-sm-6 col-md-3">
                                                                <small class="text-muted d-block" style="font-size: 0.72rem;">CNIC NUMBER</small>
                                                                <span class="fw-bold text-primary font-monospace fs-6">
                                                                    {{ $seller->cnic_no ?? 'Not Specified' }}
                                                                </span>
                                                            </div>
                                                            <div class="col-sm-6 col-md-3">
                                                                <small class="text-muted d-block" style="font-size: 0.72rem;">PHONE NUMBER</small>
                                                                <span class="fw-semibold text-dark">{{ $seller->user->phone }}</span>
                                                            </div>
                                                            <div class="col-sm-6 col-md-3">
                                                                <small class="text-muted d-block" style="font-size: 0.72rem;">FARM LOCATION</small>
                                                                <span class="fw-semibold text-dark">{{ $seller->location }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Verification Banner -->
                                                    <div class="alert alert-info py-2 px-3 rounded-3 small mb-4 border-0 d-flex align-items-center gap-2" style="background-color: #EBF5FB; color: #1B4F72;">
                                                        <i class="bi bi-shield-check fs-5"></i>
                                                        <div>
                                                            <strong>Admin Verification Notice:</strong> Please carefully review both Front & Back images of the Pakistani CNIC below. Ensure the photo, name, and 13-digit CNIC match before approving.
                                                        </div>
                                                    </div>

                                                    <!-- CNIC Documents Display: Front & Back -->
                                                    <div class="row g-3 mb-3">
                                                        <!-- Front Side -->
                                                        <div class="col-md-6">
                                                            <div class="border rounded-4 p-3 bg-white h-100 shadow-2xs">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <div class="fw-bold text-dark small d-flex align-items-center gap-1">
                                                                        <i class="bi bi-card-heading text-success"></i> CNIC Front Side (سامنے)
                                                                    </div>
                                                                    @if($seller->cnic_front)
                                                                        <span class="badge bg-success-subtle text-success border border-success-subtle text-xs">Uploaded</span>
                                                                    @else
                                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-xs">Missing</span>
                                                                    @endif
                                                                </div>

                                                                @if($seller->cnic_front)
                                                                    @if($seller->is_cnic_front_pdf)
                                                                        <div class="rounded-3 border bg-light p-4 text-center my-2">
                                                                            <i class="bi bi-file-earmark-pdf-fill fs-1 text-danger d-block mb-2"></i>
                                                                            <span class="small fw-semibold text-dark d-block mb-3">CNIC Front (PDF Document)</span>
                                                                            <a href="{{ Storage::disk('public')->url($seller->cnic_front) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                                                <i class="bi bi-box-arrow-up-right me-1"></i> Open PDF in New Tab
                                                                            </a>
                                                                        </div>
                                                                    @else
                                                                        <div class="rounded-3 border bg-light p-1 text-center position-relative overflow-hidden mb-2" style="height: 220px;">
                                                                            <a href="{{ Storage::disk('public')->url($seller->cnic_front) }}" target="_blank" title="Click to view full image in new tab">
                                                                                <img src="{{ Storage::disk('public')->url($seller->cnic_front) }}" alt="CNIC Front Side" class="img-fluid rounded w-100 h-100" style="object-fit: contain;">
                                                                            </a>
                                                                        </div>
                                                                        <div class="text-center">
                                                                            <a href="{{ Storage::disk('public')->url($seller->cnic_front) }}" target="_blank" class="btn btn-xs btn-outline-darkgreen rounded-pill px-3 py-1 text-decoration-none">
                                                                                <i class="bi bi-arrows-fullscreen me-1"></i> View Full Resolution &rarr;
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    <div class="rounded-3 border bg-light p-5 text-center text-muted">
                                                                        <i class="bi bi-file-earmark-x fs-2 text-secondary d-block mb-2"></i>
                                                                        <span class="small">No Front Side CNIC uploaded</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- Back Side -->
                                                        <div class="col-md-6">
                                                            <div class="border rounded-4 p-3 bg-white h-100 shadow-2xs">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <div class="fw-bold text-dark small d-flex align-items-center gap-1">
                                                                        <i class="bi bi-card-heading text-secondary"></i> CNIC Back Side (پیچھے)
                                                                    </div>
                                                                    @if($seller->cnic_back)
                                                                        <span class="badge bg-success-subtle text-success border border-success-subtle text-xs">Uploaded</span>
                                                                    @else
                                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-xs">Missing</span>
                                                                    @endif
                                                                </div>

                                                                @if($seller->cnic_back)
                                                                    @if($seller->is_cnic_back_pdf)
                                                                        <div class="rounded-3 border bg-light p-4 text-center my-2">
                                                                            <i class="bi bi-file-earmark-pdf-fill fs-1 text-danger d-block mb-2"></i>
                                                                            <span class="small fw-semibold text-dark d-block mb-3">CNIC Back (PDF Document)</span>
                                                                            <a href="{{ Storage::disk('public')->url($seller->cnic_back) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                                                <i class="bi bi-box-arrow-up-right me-1"></i> Open PDF in New Tab
                                                                            </a>
                                                                        </div>
                                                                    @else
                                                                        <div class="rounded-3 border bg-light p-1 text-center position-relative overflow-hidden mb-2" style="height: 220px;">
                                                                            <a href="{{ Storage::disk('public')->url($seller->cnic_back) }}" target="_blank" title="Click to view full image in new tab">
                                                                                <img src="{{ Storage::disk('public')->url($seller->cnic_back) }}" alt="CNIC Back Side" class="img-fluid rounded w-100 h-100" style="object-fit: contain;">
                                                                            </a>
                                                                        </div>
                                                                        <div class="text-center">
                                                                            <a href="{{ Storage::disk('public')->url($seller->cnic_back) }}" target="_blank" class="btn btn-xs btn-outline-darkgreen rounded-pill px-3 py-1 text-decoration-none">
                                                                                <i class="bi bi-arrows-fullscreen me-1"></i> View Full Resolution &rarr;
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    <div class="rounded-3 border bg-light p-5 text-center text-muted">
                                                                        <i class="bi bi-file-earmark-x fs-2 text-secondary d-block mb-2"></i>
                                                                        <span class="small">No Back Side CNIC uploaded</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Rejection Reason if previously rejected -->
                                                    @if($seller->rejection_reason)
                                                        <div class="alert alert-danger rounded-3 p-3 small mb-0">
                                                            <strong>Previous Rejection Reason:</strong> {{ $seller->rejection_reason }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Modal Verification Action Footer -->
                                                <div class="modal-footer border-top bg-light d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                                                    </div>

                                                    <div class="d-flex align-items-center gap-2">
                                                        @if($seller->status === 'pending')
                                                            <!-- Reject trigger -->
                                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#rejectModal_{{ $seller->id }}">
                                                                <i class="bi bi-x-circle me-1"></i> Reject CNIC
                                                            </button>

                                                            <!-- Approve directly from CNIC modal -->
                                                            <form action="{{ route('admin.sellers.approve', $seller->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm">
                                                                    <i class="bi bi-patch-check-fill me-1"></i> Approve Seller (CNIC Verified)
                                                                </button>
                                                            </form>
                                                        @elseif($seller->status !== 'approved')
                                                            <form action="{{ route('admin.sellers.approve', $seller->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold">
                                                                    <i class="bi bi-patch-check-fill me-1"></i> Approve Seller
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="text-success small fw-semibold">
                                                                <i class="bi bi-check-circle-fill me-1"></i> This seller is currently Approved
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                                                            <textarea name="rejection_reason" class="form-control" rows="3" placeholder="e.g. CNIC photo is blurry or CNIC details do not match..." required></textarea>
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
