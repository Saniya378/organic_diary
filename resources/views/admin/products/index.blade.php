@extends('layouts.panel')

@section('title', 'Manage Products - Admin Center')
@section('page_heading', 'Product Catalog & Approval Pipeline')

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
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ !$currentStatus ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.products.index') }}">
                        All Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'pending' ? 'active bg-warning text-dark' : 'text-dark' }}" href="{{ route('admin.products.index', ['status' => 'pending']) }}">
                        Pending Review
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'approved' ? 'active bg-success' : 'text-dark' }}" href="{{ route('admin.products.index', ['status' => 'approved']) }}">
                        Approved
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'rejected' ? 'active bg-danger' : 'text-dark' }}" href="{{ route('admin.products.index', ['status' => 'rejected']) }}">
                        Rejected
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'inactive' ? 'active bg-secondary' : 'text-dark' }}" href="{{ route('admin.products.index', ['status' => 'inactive']) }}">
                        Inactive
                    </a>
                </li>
            </ul>

            <form action="{{ route('admin.products.index') }}" method="GET" class="d-flex gap-2">
                @if($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="input-group input-group-sm" style="min-width: 240px;">
                    <input type="text" name="q" class="form-control" placeholder="Search product, farm..." value="{{ $search }}">
                    <button type="submit" class="btn btn-outline-darkgreen">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        @if($products->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-box-seam fs-1 text-gold d-block mb-3"></i>
                <h5 class="font-serif fw-bold text-dark">No Products Found</h5>
                <p class="small mb-0">No products matching current filter criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Product Details</th>
                            <th>Farm Producer</th>
                            <th>Category</th>
                            <th>Base Price</th>
                            <th>Stock</th>
                            <th>Commission Rate</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Catalog Approval & Fees</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            @php
                                $statusBadges = [
                                    'approved' => 'bg-success-subtle text-success border border-success-subtle',
                                    'pending' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    'inactive' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                ];
                                $badgeClass = $statusBadges[$product->status] ?? 'bg-light text-dark';
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 overflow-hidden bg-light border d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                                            @if($product->images->isNotEmpty())
                                                <img src="{{ Storage::url($product->images->first()->path) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <i class="bi bi-image text-muted fs-4"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('shop.show', $product->slug) }}" target="_blank" class="fw-bold text-dark text-decoration-none hover-gold d-block">
                                                {{ $product->name }}
                                            </a>
                                            <small class="text-muted">{{ $product->unit }}</small>
                                            @if($product->rejection_reason)
                                                <div class="small text-danger mt-1">
                                                    <strong>Note:</strong> {{ $product->rejection_reason }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">&#127969; {{ $product->seller->sellerProfile->farm_name ?? $product->seller->name }}</div>
                                    <small class="text-muted">{{ $product->seller->sellerProfile->location ?? 'Gilgit' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                                </td>
                                <td class="fw-bold text-dark font-serif">
                                    Rs {{ number_format($product->price) }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $product->stock }} units</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="badge {{ $product->commission_rate !== null ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-secondary border' }} px-2 py-1 rounded-pill">
                                            {{ $product->effective_commission_rate }}%
                                        </span>
                                        @if($product->commission_rate !== null)
                                            <span class="badge bg-gold text-dark text-2xs" title="Product-Specific Custom Commission">Custom</span>
                                        @else
                                            <span class="text-2xs text-muted" title="Default System Commission">(Default)</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }} px-2 py-1 rounded-pill small">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        <!-- Edit Commission Button -->
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 text-xs" data-bs-toggle="modal" data-bs-target="#commissionModal_{{ $product->id }}" title="Set Product Commission Rate">
                                            <i class="bi bi-percent me-0.5"></i> Commission
                                        </button>

                                        @if($product->status !== 'approved')
                                            <form action="{{ route('admin.products.approve', $product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-2 text-xs" title="Approve Product">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                            </form>
                                        @endif

                                        @if($product->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 text-xs" data-bs-toggle="modal" data-bs-target="#rejectModal_{{ $product->id }}">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Commission Rate Modal -->
                                    <div class="modal fade" id="commissionModal_{{ $product->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title font-serif text-dark-green">
                                                        <i class="bi bi-percent text-gold me-1"></i> Set Admin Commission
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('admin.products.commission', $product->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body text-start">
                                                        <p class="small text-muted mb-3">
                                                            Configure the percentage commission the platform earns whenever buyers purchase <strong>{{ $product->name }}</strong> from <strong>{{ $product->seller->sellerProfile->farm_name ?? $product->seller->name }}</strong>.
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Commission Percentage (%)</label>
                                                            <div class="input-group">
                                                                <input type="number" step="0.1" name="commission_rate" class="form-control" placeholder="e.g. 7.5 (leave empty for platform default)" value="{{ $product->commission_rate }}" min="0" max="100">
                                                                <span class="input-group-text">%</span>
                                                            </div>
                                                            <div class="form-text small text-muted">
                                                                Current Effective Rate: <strong>{{ $product->effective_commission_rate }}%</strong>. Leave blank to use category/platform default rate.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">Save Commission</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reject Product Modal -->
                                    <div class="modal fade" id="rejectModal_{{ $product->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title font-serif text-danger">Reject Product</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('admin.products.reject', $product->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body text-start">
                                                        <p class="small text-muted mb-3">
                                                            Please provide a reason for rejecting <strong>{{ $product->name }}</strong>:
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                                                            <textarea name="rejection_reason" class="form-control" rows="3" placeholder="e.g. Inaccurate production date, unverified batch, or low quality photos." required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold">Reject Product</button>
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
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
