@extends('layouts.panel')

@section('title', 'Admin Dashboard - Organic Dairy GB')
@section('page_heading', 'Marketplace Administration Center')

@section('menu_items')
    @include('admin.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Total Sellers -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Sellers</span>
                    <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-shop fs-5"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark-green mb-1">{{ $totalSellers }}</h3>
                <div class="small text-muted">{{ $pendingSellers->count() }} awaiting verification</div>
            </div>
        </div>

        <!-- KPI 2: Total Buyers -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Registered Buyers</span>
                    <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark mb-1">{{ $totalBuyers }}</h3>
                <div class="small text-muted">Across Gilgit-Baltistan</div>
            </div>
        </div>

        <!-- KPI 3: Orders Today -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Orders Today</span>
                    <div class="rounded-circle p-2 bg-warning-subtle text-gold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-calendar-check-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark mb-1">{{ $ordersToday }}</h3>
                <div class="small text-muted">Daily fresh dairy dispatch</div>
            </div>
        </div>

        <!-- KPI 4: Monthly Revenue -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Monthly Gross Sales</span>
                    <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-currency-dollar fs-5"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark-green mb-1">Rs {{ number_format($monthlyRevenue) }}</h3>
                <div class="small text-muted">{{ date('F Y') }} sales</div>
            </div>
        </div>
    </div>

    <!-- Orders by Status Bars -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <h5 class="font-serif fw-bold text-dark-green mb-3">Marketplace Order Status Overview</h5>
        <div class="row g-3 text-center">
            @php
                $statusColors = [
                    'placed' => ['bg' => 'bg-info', 'label' => 'New / Placed'],
                    'confirmed' => ['bg' => 'bg-primary', 'label' => 'Confirmed'],
                    'packing' => ['bg' => 'bg-secondary', 'label' => 'Packing'],
                    'in_transit' => ['bg' => 'bg-warning', 'label' => 'In Transit'],
                    'delivered' => ['bg' => 'bg-success', 'label' => 'Delivered'],
                    'cancelled' => ['bg' => 'bg-danger', 'label' => 'Cancelled'],
                ];
            @endphp
            @foreach($statusCounts as $statusKey => $count)
                @php
                    $percentage = round(($count / $totalActiveOrders) * 100);
                    $color = $statusColors[$statusKey]['bg'] ?? 'bg-primary';
                    $label = $statusColors[$statusKey]['label'] ?? ucfirst($statusKey);
                @endphp
                <div class="col-6 col-md-2">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="small text-muted mb-1">{{ $label }}</div>
                        <h4 class="font-serif fw-bold text-dark mb-2">{{ $count }}</h4>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar {{ $color }}" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 2 Pending Verification Tables (Sellers & Products) -->
    <div class="row g-4 mb-4">
        
        <!-- Pending Sellers Table -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom border-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="font-serif fw-bold text-dark-green mb-0">Pending Farm Registrations</h5>
                        <small class="text-muted">{{ $pendingSellers->count() }} farmers awaiting verification</small>
                    </div>
                    <a href="{{ route('admin.sellers.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-darkgreen rounded-pill px-3">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($pendingSellers->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle-fill fs-2 text-success mb-2 d-block"></i>
                            <span class="small">No pending seller registrations to review.</span>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="text-muted small">
                                        <th class="ps-4">Farm & Owner</th>
                                        <th>Location</th>
                                        <th class="text-end pe-4">Review Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingSellers as $seller)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-dark">{{ $seller->farm_name }}</div>
                                                <small class="text-muted">{{ $seller->user->name }} &bull; {{ $seller->user->phone }}</small>
                                            </td>
                                            <td class="small text-secondary">
                                                {{ $seller->location }}
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-inline-flex gap-1">
                                                    <form action="{{ route('admin.sellers.approve', $seller->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-2" title="Approve Seller">
                                                            <i class="bi bi-check-lg"></i> Approve
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#rejectSellerModal_{{ $seller->id }}">
                                                        <i class="bi bi-x-lg"></i> Reject
                                                    </button>
                                                </div>

                                                <!-- Reject Modal -->
                                                <div class="modal fade" id="rejectSellerModal_{{ $seller->id }}" tabindex="-1" aria-hidden="true">
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
                                                                        Please provide a reason for rejecting <strong>{{ $seller->farm_name }}</strong>. This feedback will be emailed to the applicant.
                                                                    </p>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                                                                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="e.g. Farm location outside Gilgit-Baltistan service radius, or missing organic certification documents." required></textarea>
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
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Products Table -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom border-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="font-serif fw-bold text-dark-green mb-0">Pending Catalog Products</h5>
                        <small class="text-muted">{{ $pendingProducts->count() }} dairy items awaiting approval</small>
                    </div>
                    <a href="{{ route('admin.products.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-darkgreen rounded-pill px-3">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($pendingProducts->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle-fill fs-2 text-success mb-2 d-block"></i>
                            <span class="small">No pending dairy products in review queue.</span>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="text-muted small">
                                        <th class="ps-4">Product</th>
                                        <th>Farm</th>
                                        <th>Price</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingProducts as $prod)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-dark">{{ $prod->name }}</div>
                                                <small class="text-muted">{{ $prod->unit }} &bull; {{ $prod->category->name }}</small>
                                            </td>
                                            <td class="small text-secondary">
                                                &#127969; {{ $prod->seller->sellerProfile->farm_name ?? $prod->seller->name }}
                                            </td>
                                            <td class="fw-bold text-dark font-serif">
                                                Rs {{ number_format($prod->price) }}
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-inline-flex gap-1">
                                                    <form action="{{ route('admin.products.approve', $prod->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-2" title="Approve Product">
                                                            <i class="bi bi-check-lg"></i> Approve
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#rejectProdModal_{{ $prod->id }}">
                                                        <i class="bi bi-x-lg"></i> Reject
                                                    </button>
                                                </div>

                                                <!-- Reject Product Modal -->
                                                <div class="modal fade" id="rejectProdModal_{{ $prod->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content rounded-4 border-0 shadow">
                                                            <div class="modal-header border-bottom">
                                                                <h5 class="modal-title font-serif text-danger">Reject Product</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="{{ route('admin.products.reject', $prod->id) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-body text-start">
                                                                    <p class="small text-muted mb-3">
                                                                        Provide reason for rejecting <strong>{{ $prod->name }}</strong>:
                                                                    </p>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-semibold">Reason <span class="text-danger">*</span></label>
                                                                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="e.g. Missing required batch information, low quality image, or price out of range." required></textarea>
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
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
