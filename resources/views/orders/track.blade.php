@extends('layouts.public')

@section('title', $order ? 'Track Order #' . $order->order_no . ' - Organic Dairy GB' : 'Track Your Order - Organic Dairy GB')

@push('styles')
<style>
    .live-pulse-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #28a745;
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        animation: livePulse 1.8s infinite;
    }
    @keyframes livePulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
        70% { transform: scale(1.15); box-shadow: 0 0 0 8px rgba(40, 167, 69, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
    }
    .node-pulse-active {
        box-shadow: 0 0 0 0 rgba(224, 169, 59, 0.6);
        animation: nodePulse 2s infinite cubic-bezier(0.66, 0, 0, 1);
    }
    @keyframes nodePulse {
        0% { box-shadow: 0 0 0 0 rgba(224, 169, 59, 0.7); }
        70% { box-shadow: 0 0 0 12px rgba(224, 169, 59, 0); }
        100% { box-shadow: 0 0 0 0 rgba(224, 169, 59, 0); }
    }
    .spin-icon-fast {
        display: inline-block;
        animation: spinAnim 1.4s linear infinite;
    }
    @keyframes spinAnim {
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" class="text-decoration-none">Orders</a></li>
            <li class="breadcrumb-item active" aria-current="page">Track Order</li>
        </ol>
    </nav>

    <!-- Top Order Number Lookup Card (Navbar destination & quick search) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 text-white p-4 p-md-4" style="background: linear-gradient(135deg, #1F3A27 0%, #14261a 100%);">
        <div class="row align-items-center g-3">
            <div class="col-lg-6">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-geo-alt-fill text-warning fs-4"></i>
                    <h3 class="mb-0 font-serif text-white fw-bold">Live Order Tracker</h3>
                </div>
                <p class="text-white-50 small mb-0">Enter your Himalayan dairy order number (e.g. <span class="badge bg-white bg-opacity-25 text-white font-monospace">OD-20500</span>) to track real-time cold-chain dispatch.</p>
            </div>
            <div class="col-lg-6">
                <form action="{{ route('track-order.form') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                        <input type="text" name="order_no" class="form-control border-0 py-2.5 font-monospace text-dark" placeholder="Enter Order # (e.g. OD-20500)" value="{{ $searchedOrderNo ?? ($order->order_no ?? '') }}" required>
                        <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                            <i class="bi bi-truck me-1"></i> Track
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4 shadow-sm mb-4 border-0">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger rounded-4 shadow-sm mb-4 border-0">
            <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif

    @if(isset($errorMessage))
        <div class="alert alert-warning rounded-4 shadow-sm mb-4 border-0">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errorMessage }}
        </div>
    @endif

    @if(!$order)
        <!-- Empty State / Lookup Prompt -->
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-3 bg-white">
            <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; background-color: #FAF7F0;">
                <i class="bi bi-truck fs-1 text-gold"></i>
            </div>
            <h4 class="font-serif fw-bold text-dark-green mb-2">Track Your Organic Dairy Dispatch</h4>
            <p class="text-muted small mx-auto mb-4" style="max-width: 480px;">
                Enter your order reference above to view real-time cold-storage packing updates, rider dispatch contact details, and milestone timestamps from Gilgit-Baltistan farm valleys.
            </p>
            @auth
                <div>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-darkgreen rounded-pill px-4 btn-sm fw-semibold">
                        <i class="bi bi-list-ul me-1"></i> View All My Orders
                    </a>
                </div>
            @endauth
        </div>
    @else
        <!-- Active Real-Time Order Tracker -->
        <div class="row g-4">
            
            <!-- Left Column: Vertical Real-time Timeline -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom border-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span class="text-muted small">Tracking Order</span>
                            <h4 class="mb-0 font-serif text-dark-green font-monospace fw-bold">#{{ $order->order_no }}</h4>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 small fw-semibold d-flex align-items-center gap-1.5" id="liveSyncPill">
                                <span class="live-pulse-dot"></span>
                                <span>Live Tracking</span>
                            </span>
                            @php
                                $statusBadges = [
                                    'pending_payment' => 'bg-warning text-dark',
                                    'placed' => 'bg-info text-dark',
                                    'new' => 'bg-info text-dark',
                                    'confirmed' => 'bg-primary text-white',
                                    'packing' => 'bg-secondary text-white',
                                    'in_transit' => 'bg-primary text-white',
                                    'delivered' => 'bg-success text-white',
                                    'cancelled' => 'bg-danger text-white',
                                ];
                                $badgeClass = $statusBadges[$order->status] ?? 'bg-light text-dark';
                            @endphp
                            <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill fs-6 fw-bold" id="liveStatusBadge">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        
                        @php
                            $trackPayment = $order->orderGroup->payment;
                        @endphp

                        <!-- Payment Real-Time Banner Container -->
                        <div id="paymentStatusAlertContainer">
                            @if($trackPayment && $trackPayment->isUnderReview())
                                <div class="alert alert-warning rounded-4 p-3.5 border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-hourglass-split fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Payment Verification Under Review</h6>
                                            <small class="text-muted">Transaction ID: <span class="font-monospace fw-semibold text-dark">{{ $trackPayment->transaction_id ?? $trackPayment->gateway_ref }}</span> &bull; Awaiting Farm/Seller Confirmation.</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill text-xs fw-bold">Under Review</span>
                                </div>
                            @elseif($trackPayment && $trackPayment->isRejected())
                                <div class="alert alert-danger rounded-4 p-3.5 border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-x-circle-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-danger mb-0">Payment Proof Declined</h6>
                                            <small class="text-dark">Reason: {{ $trackPayment->admin_notes ?? 'Invalid transaction ID.' }}</small>
                                        </div>
                                    </div>
                                    @auth
                                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-danger rounded-pill px-3 py-1 text-xs fw-bold">
                                            Re-submit Proof
                                        </a>
                                    @endauth
                                </div>
                            @elseif($trackPayment && $trackPayment->isPaid())
                                <div class="alert alert-success rounded-4 p-3 border-0 shadow-sm mb-4 d-flex align-items-center gap-2">
                                    <i class="bi bi-shield-fill-check text-success fs-5"></i>
                                    <span class="small fw-semibold text-success">
                                        Payment Verified &amp; Paid (Rs {{ number_format($trackPayment->amount) }}) &bull; TRX ID: {{ $trackPayment->transaction_id ?? $trackPayment->gateway_ref }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Cancellation Banner Container -->
                        <div id="cancellationAlertContainer">
                            @if($order->status === 'cancelled')
                                <div class="alert alert-danger rounded-4 p-4 border-0 mb-4 shadow-sm">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center p-2" style="width: 44px; height: 44px; min-width: 44px;">
                                            <i class="bi bi-x-octagon fs-4"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1 text-danger">
                                                @if($order->isCancelledBySeller())
                                                    Order Cancelled by Seller / Farmer
                                                @elseif($order->isCancelledByAdmin())
                                                    Order Cancelled by Platform Admin
                                                @else
                                                    Order Cancelled by Customer
                                                @endif
                                            </h5>
                                            <p class="mb-1 text-dark">
                                                @if($order->isCancelledBySeller())
                                                    This order was cancelled by <strong>{{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}</strong>.
                                                @elseif($order->isCancelledByAdmin())
                                                    This order was cancelled by platform administration.
                                                @else
                                                    This order was cancelled at the customer's request.
                                                @endif
                                            </p>
                                            <div class="small text-muted"><strong>Reason:</strong> {{ $order->cancel_reason ?? 'Not specified' }}. All reserved items returned to live farm stock.</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        <!-- Live Route Corridor (Origin -> Transit -> Destination) -->
                        <div class="card border-0 shadow-xs rounded-4 p-3.5 mb-4" style="background: linear-gradient(135deg, #FAF7F0 0%, #F5F0E6 100%); border: 1px solid #ECE6D6 !important;">
                            <div class="row align-items-center g-3">
                                <!-- Origin (Farm Valley) -->
                                <div class="col-md-5">
                                    <div class="d-flex align-items-start gap-2.5">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center p-2 shadow-2xs" style="width: 38px; height: 38px; min-width: 38px;">
                                            <i class="bi bi-geo-alt-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill text-xs px-2 py-0.5 mb-1">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Origin (Dispatched From)
                                            </span>
                                            <h6 class="fw-bold text-dark font-serif mb-0" id="routeOriginFarm">{{ $order->seller?->sellerProfile?->farm_name ?? $order->seller?->name ?? 'Valley Farm' }}</h6>
                                            <small class="text-muted d-block" id="routeOriginLoc"><i class="bi bi-geo me-1"></i> {{ $order->seller?->sellerProfile?->location ?? 'Gilgit-Baltistan' }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Middle: Moving Status Indicator -->
                                <div class="col-md-2 text-center py-2 py-md-0">
                                    <div class="d-inline-flex flex-column align-items-center">
                                        <div class="rounded-pill px-3 py-1 text-xs fw-bold shadow-2xs {{ $order->status === 'in_transit' ? 'bg-primary text-white' : ($order->status === 'delivered' ? 'bg-success text-white' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle') }}" id="routeTransitBadge">
                                            @if($order->status === 'in_transit')
                                                <i class="bi bi-truck me-1"></i> En Route
                                            @elseif($order->status === 'delivered')
                                                <i class="bi bi-check2-circle me-1"></i> Arrived
                                            @elseif($order->status === 'cancelled')
                                                <i class="bi bi-x-circle me-1"></i> Cancelled
                                            @else
                                                <i class="bi bi-clock-history me-1"></i> Processing
                                            @endif
                                        </div>
                                        <div class="w-100 my-1 position-relative" style="height: 2px; background: #D5CDBC; min-width: 70px;">
                                            <i class="bi bi-chevron-right position-absolute top-50 start-50 translate-middle text-muted" style="font-size: 10px;"></i>
                                        </div>
                                        <small class="text-xs text-muted" id="routeTransitStatusLabel">Cold-Chain</small>
                                    </div>
                                </div>

                                <!-- Destination (Customer Doorstep) -->
                                <div class="col-md-5">
                                    <div class="d-flex align-items-start gap-2.5 justify-content-md-end text-md-end">
                                        <div class="order-md-1 order-2">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill text-xs px-2 py-0.5 mb-1">
                                                <i class="bi bi-house-door-fill me-1"></i> Destination (Doorstep)
                                            </span>
                                            <h6 class="fw-bold text-dark font-serif mb-0" id="routeDestCity">{{ $order->orderGroup?->shipping_city ?? 'Gilgit' }}</h6>
                                            <small class="text-muted d-block text-truncate-2" id="routeDestAddress"><i class="bi bi-pin-map me-1"></i> {{ $order->orderGroup?->shipping_address_line ?? 'Customer Address' }}</small>
                                        </div>
                                        <div class="order-md-2 order-1 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center p-2 shadow-2xs" style="width: 38px; height: 38px; min-width: 38px;">
                                            <i class="bi bi-house-door-fill fs-5"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vertical Timeline -->
                        <div class="timeline-vertical position-relative ps-4" id="timelineContainer">
                            <div class="timeline-line position-absolute top-0 bottom-0" style="left: 17px; width: 2px; background-color: #E2DDD0;"></div>

                            @foreach($timeline['stages'] as $index => $stage)
                                <div class="timeline-step position-relative mb-4 pb-2" id="stage-{{ $stage['key'] }}" data-stage-key="{{ $stage['key'] }}">
                                    <!-- Status Icon / Check Mark Indicator -->
                                    <div class="timeline-node position-absolute d-flex align-items-center justify-content-center rounded-circle shadow-sm {{ $stage['active'] ? 'node-pulse-active' : '' }}" 
                                         id="node-{{ $stage['key'] }}"
                                         style="left: -29px; top: 0; width: 34px; height: 34px; z-index: 2;
                                         background-color: {{ $stage['completed'] ? '#2F5D3A' : ($stage['active'] ? '#E0A93B' : '#FFFFFF') }};
                                         border: 2px solid {{ $stage['completed'] ? '#2F5D3A' : ($stage['active'] ? '#E0A93B' : '#ECE6D6') }};
                                         color: {{ ($stage['completed'] || $stage['active']) ? '#FFFFFF' : '#A0AEC0' }};">
                                        @if($stage['completed'])
                                            <i class="bi bi-check-lg fw-bold"></i>
                                        @elseif($stage['active'])
                                            <i class="bi bi-arrow-repeat spin-icon-fast"></i>
                                        @else
                                            <i class="bi {{ $stage['icon'] }} small"></i>
                                        @endif
                                    </div>

                                    <!-- Step Content -->
                                    <div class="ps-2">
                                        <div class="d-flex flex-wrap justify-content-between align-items-baseline">
                                            <h6 class="fw-bold mb-1 font-serif stage-title {{ $stage['completed'] ? 'text-dark-green' : ($stage['active'] ? 'text-dark fw-bold' : 'text-muted') }}" id="title-{{ $stage['key'] }}">
                                                {{ $stage['title'] }}
                                            </h6>
                                            <span class="badge bg-light text-secondary border small stage-timestamp {{ $stage['timestamp'] ? '' : 'd-none' }}" id="time-{{ $stage['key'] }}">
                                                <i class="bi bi-clock me-1"></i> <span>{{ $stage['timestamp'] ?? '' }}</span>
                                            </span>
                                        </div>
                                        <p class="small text-muted mb-1">{{ $stage['description'] }}</p>
                                        <div class="small text-secondary fst-italic stage-note {{ $stage['note'] ? '' : 'd-none' }}" id="note-{{ $stage['key'] }}">
                                            <i class="bi bi-info-circle me-1"></i> <span class="note-text">{{ $stage['note'] ?? '' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Live Sync Indicator Bar -->
                        <div class="d-flex justify-content-between align-items-center pt-3 mt-3 border-top text-muted small flex-wrap gap-2">
                            <span><i class="bi bi-shield-check text-success me-1"></i> GB Cold-Chain Temperature Verified</span>
                            <div class="d-flex align-items-center gap-2">
                                <span id="lastSyncedTime">Last synced: {{ now()->format('h:i:s A') }}</span>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" onclick="refreshTrackingStatus(true)" title="Force Instant Sync">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rider Card Container (Only if rider_name or rider_phone is set) -->
                <div id="riderCardContainer" style="{{ ($order->rider_name || $order->rider_phone) ? '' : 'display: none;' }}">
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light border-success" id="riderCard">
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-auto">
                                    <div class="bg-success text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                        <i class="bi bi-person-badge fs-4"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle mb-1">Assigned Delivery Rider</span>
                                    <h5 class="fw-bold text-dark font-serif mb-0" id="riderNameDisplay">{{ $order->rider_name ?? 'Valley Courier Rider' }}</h5>
                                    <p class="small text-muted mb-0">Himalayan Cold-Chain Courier Fleet</p>
                                </div>
                                <div class="col-auto" id="riderPhoneActionContainer">
                                    @if($order->rider_phone)
                                        <a href="tel:{{ $order->rider_phone }}" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" id="riderPhoneLink">
                                            <i class="bi bi-telephone-outbound-fill"></i> Call Rider ({{ $order->rider_phone }})
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Details, Address & Cancel -->
            <div class="col-lg-5">
                
                <!-- Action / Cancellation Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-dark font-serif">Manage Order</span>
                            <span class="text-muted small">Need changes?</span>
                        </div>

                        <div class="d-grid gap-2" id="cancelActionContainer">
                            @if($canCancel)
                                <button type="button" class="btn btn-outline-danger rounded-pill py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#cancelTrackModal" id="btnOpenCancelModal">
                                    <i class="bi bi-x-circle me-1"></i> Cancel Order & Restore Stock
                                </button>

                                <!-- Cancel Modal -->
                                <div class="modal fade" id="cancelTrackModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <div class="modal-header border-bottom">
                                                <h5 class="modal-title font-serif text-danger">Cancel Order #{{ $order->order_no }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body text-start">
                                                    <p class="small text-muted mb-3">
                                                        Are you sure you want to cancel this order? Cancelling is only possible before packing begins. Reserved items will be restored to farm inventory.
                                                    </p>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Reason for Cancellation</label>
                                                        <select name="cancel_reason" class="form-select form-select-sm">
                                                            <option value="Changed my mind">Changed my mind</option>
                                                            <option value="Ordered by mistake">Ordered by mistake</option>
                                                            <option value="Need to change delivery address">Need to change delivery address</option>
                                                            <option value="Delivery time is too late">Delivery time is too late</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top bg-light">
                                                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Keep Order</button>
                                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold">Confirm Cancellation</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="p-3 bg-light rounded-3 text-muted small text-center" id="lockedCancelMessage">
                                    <i class="bi bi-lock-fill me-1"></i>
                                    @if($order->status === 'cancelled')
                                        @if($order->isCancelledBySeller())
                                            Order was cancelled by seller ({{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}).
                                        @elseif($order->isCancelledByAdmin())
                                            Order was cancelled by administrator.
                                        @else
                                            This order was cancelled.
                                        @endif
                                    @elseif($order->status === 'delivered')
                                        This order has already been successfully delivered.
                                    @else
                                        Cancellation unavailable (Order is already <em>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</em>).
                                    @endif
                                </div>
                            @endif

                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-light border rounded-pill py-2 text-dark fw-semibold">
                                <i class="bi bi-file-earmark-text me-1"></i> Full Order Details
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Order Items Panel -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom border-light">
                        <h6 class="mb-0 fw-bold font-serif text-dark-green">
                            <i class="bi bi-basket text-gold me-2"></i> Items in this Order
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="p-2 mb-3 bg-light rounded-3 d-flex justify-content-between align-items-center small">
                            <span class="fw-bold text-dark">&#127969; {{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}</span>
                            <span class="text-muted">{{ $order->seller->sellerProfile->location ?? 'Gilgit-Baltistan' }}</span>
                        </div>

                        <ul class="list-unstyled mb-0">
                            @foreach($order->items as $item)
                                <li class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                        <div class="text-muted small">Qty: {{ $item->quantity }} &times; Rs {{ number_format($item->unit_price) }}</div>
                                    </div>
                                    <strong class="text-dark">Rs {{ number_format($item->line_total) }}</strong>
                                </li>
                            @endforeach
                        </ul>

                        <div class="pt-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Subtotal:</span>
                                <span>Rs {{ number_format($order->subtotal) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Delivery Fee:</span>
                                <span>Rs {{ number_format($order->delivery_fee) }}</span>
                            </div>
                            <hr class="my-2 opacity-25">
                            <div class="d-flex justify-content-between fw-bold">
                                <span class="text-dark">Total:</span>
                                <span class="text-primary font-serif fs-5">Rs {{ number_format($order->total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Address & Payment Method Panel -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom border-light">
                        <h6 class="mb-0 fw-bold font-serif text-dark-green">
                            <i class="bi bi-geo-alt-fill text-gold me-2"></i> Destination &amp; Payment
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <span class="text-muted small d-block">Recipient:</span>
                            <strong class="text-dark">{{ $order->orderGroup->shipping_full_name }}</strong>
                            <div class="small text-secondary"><i class="bi bi-telephone me-1"></i> {{ $order->orderGroup->shipping_phone }}</div>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small d-block">Delivery Address:</span>
                            <div class="small text-secondary">{{ $order->orderGroup->shipping_address_line }}, {{ $order->orderGroup->shipping_area }}, <strong>{{ $order->orderGroup->shipping_city }}</strong>, Gilgit-Baltistan</div>
                        </div>
                        <div class="pt-2 border-top">
                            <span class="text-muted small d-block">Payment Method:</span>
                            <span class="badge bg-light text-dark border">
                                {{ strtoupper($order->orderGroup->payment_method) }} &bull; <span id="livePaymentStatusText">{{ strtoupper($order->payment_status) }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Live Delivery Help Card -->
                <div class="card border-0 shadow-sm rounded-4 text-center p-3 mb-4" style="background: #F4FBF6; border: 1px solid #D2EED9 !important;">
                    <div class="small fw-bold text-dark mb-1"><i class="bi bi-headset text-success me-1"></i> Live Delivery Assistance</div>
                    <div class="text-xs text-muted mb-2">Need rider ETA or direct dispatch help? Chat live with support.</div>
                    <button type="button" onclick="window.openSupportChat('Track Order #{{ $order->order_no }}')" class="btn btn-success rounded-pill btn-sm fw-bold px-3 py-1.5 shadow-xs">
                        <i class="bi bi-chat-dots-fill me-1"></i> Live Chat Support
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>

@if($order)
@push('scripts')
<script>
    // Real-Time Live Order Tracking Poller (every 5 seconds)
    const ORDER_ID = {{ $order->id }};
    const STATUS_API_URL = "{{ route('orders.track.status', $order->id) }}";

    const badgeClassMap = {
        'pending_payment': 'bg-warning text-dark',
        'placed': 'bg-info text-dark',
        'new': 'bg-info text-dark',
        'confirmed': 'bg-primary text-white',
        'packing': 'bg-secondary text-white',
        'in_transit': 'bg-primary text-white',
        'delivered': 'bg-success text-white',
        'cancelled': 'bg-danger text-white'
    };

    async function refreshTrackingStatus(isManual = false) {
        try {
            const response = await fetch(STATUS_API_URL, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) return;

            const data = await response.json();
            if (!data.success) return;

            // 1. Update Status Badge
            const badge = document.getElementById('liveStatusBadge');
            if (badge) {
                badge.innerText = data.status_label;
                badge.className = `badge ${badgeClassMap[data.status] || 'bg-light text-dark'} px-3 py-2 rounded-pill fs-6 fw-bold`;
            }

            // 1b. Update Route Corridor Transit Badge
            const transitBadge = document.getElementById('routeTransitBadge');
            if (transitBadge) {
                if (data.status === 'in_transit') {
                    transitBadge.className = 'rounded-pill px-3 py-1 text-xs fw-bold shadow-2xs bg-primary text-white';
                    transitBadge.innerHTML = '<i class="bi bi-truck me-1"></i> En Route';
                } else if (data.status === 'delivered') {
                    transitBadge.className = 'rounded-pill px-3 py-1 text-xs fw-bold shadow-2xs bg-success text-white';
                    transitBadge.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Arrived';
                } else if (data.is_cancelled) {
                    transitBadge.className = 'rounded-pill px-3 py-1 text-xs fw-bold shadow-2xs bg-danger text-white';
                    transitBadge.innerHTML = '<i class="bi bi-x-circle me-1"></i> Cancelled';
                } else {
                    transitBadge.className = 'rounded-pill px-3 py-1 text-xs fw-bold shadow-2xs bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                    transitBadge.innerHTML = '<i class="bi bi-clock-history me-1"></i> ' + (data.status_label || 'Processing');
                }
            }

            // 2. Update Payment text
            const payText = document.getElementById('livePaymentStatusText');
            if (payText) {
                payText.innerText = (data.payment_status || 'unpaid').toUpperCase();
            }

            // 3. Update Last Synced Time
            const syncEl = document.getElementById('lastSyncedTime');
            if (syncEl) {
                syncEl.innerText = `Last synced: ${data.updated_at}`;
            }

            // 4. Update Cancellation Banner if cancelled
            const cancelContainer = document.getElementById('cancellationAlertContainer');
            if (cancelContainer) {
                if (data.is_cancelled) {
                    let cancelTitle = "Order Cancelled";
                    let cancelDesc = `Reason: ${data.cancel_reason || 'Cancelled'}. All reserved items returned to live farm stock.`;

                    if (data.is_cancelled_by_seller) {
                        cancelTitle = `Order Cancelled by Seller / Farmer (${data.farm_name || 'Farm'})`;
                        cancelDesc = `This order was cancelled by ${data.farm_name || 'the seller'}. Reason: ${data.cancel_reason || 'Not specified'}.`;
                    } else if (data.is_cancelled_by_admin) {
                        cancelTitle = "Order Cancelled by Platform Admin";
                        cancelDesc = `This order was cancelled by administration. Reason: ${data.cancel_reason || 'Not specified'}.`;
                    } else if (data.is_cancelled_by_buyer) {
                        cancelTitle = "Order Cancelled by Customer";
                        cancelDesc = `This order was cancelled by customer. Reason: ${data.cancel_reason || 'Customer request'}.`;
                    }

                    cancelContainer.innerHTML = `
                        <div class="alert alert-danger rounded-4 p-4 border-0 mb-4 shadow-sm">
                            <div class="d-flex align-items-start gap-3">
                                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center p-2" style="width: 44px; height: 44px; min-width: 44px;">
                                    <i class="bi bi-x-octagon fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 text-danger">${cancelTitle}</h5>
                                    <p class="mb-1 text-dark">${cancelDesc}</p>
                                    <div class="small text-muted">All reserved items returned to live farm inventory.</div>
                                </div>
                            </div>
                        </div>
                    `;

                    // Disable cancel button if open
                    const cancelActionBox = document.getElementById('cancelActionContainer');
                    const btnModal = document.getElementById('btnOpenCancelModal');
                    if (btnModal) {
                        btnModal.remove();
                    }
                }
            }

            // 5. Update Timeline Stages dynamically
            if (data.timeline && data.timeline.stages) {
                data.timeline.stages.forEach(stage => {
                    const stepEl = document.getElementById(`stage-${stage.key}`);
                    const nodeEl = document.getElementById(`node-${stage.key}`);
                    const titleEl = document.getElementById(`title-${stage.key}`);
                    const timeEl = document.getElementById(`time-${stage.key}`);
                    const noteEl = document.getElementById(`note-${stage.key}`);

                    if (nodeEl) {
                        if (stage.completed) {
                            nodeEl.style.backgroundColor = '#2F5D3A';
                            nodeEl.style.borderColor = '#2F5D3A';
                            nodeEl.style.color = '#FFFFFF';
                            nodeEl.className = 'timeline-node position-absolute d-flex align-items-center justify-content-center rounded-circle shadow-sm';
                            nodeEl.innerHTML = '<i class="bi bi-check-lg fw-bold"></i>';
                        } else if (stage.active) {
                            nodeEl.style.backgroundColor = '#E0A93B';
                            nodeEl.style.borderColor = '#E0A93B';
                            nodeEl.style.color = '#FFFFFF';
                            nodeEl.className = 'timeline-node position-absolute d-flex align-items-center justify-content-center rounded-circle shadow-sm node-pulse-active';
                            nodeEl.innerHTML = '<i class="bi bi-arrow-repeat spin-icon-fast"></i>';
                        } else {
                            nodeEl.style.backgroundColor = '#FFFFFF';
                            nodeEl.style.borderColor = '#ECE6D6';
                            nodeEl.style.color = '#A0AEC0';
                            nodeEl.className = 'timeline-node position-absolute d-flex align-items-center justify-content-center rounded-circle shadow-sm';
                            nodeEl.innerHTML = `<i class="bi ${stage.icon} small"></i>`;
                        }
                    }

                    if (titleEl) {
                        titleEl.className = `fw-bold mb-1 font-serif stage-title ${stage.completed ? 'text-dark-green' : (stage.active ? 'text-dark fw-bold' : 'text-muted')}`;
                    }

                    if (timeEl) {
                        if (stage.timestamp) {
                            timeEl.classList.remove('d-none');
                            const span = timeEl.querySelector('span');
                            if (span) span.innerText = stage.timestamp;
                        } else {
                            timeEl.classList.add('d-none');
                        }
                    }

                    if (noteEl) {
                        if (stage.note) {
                            noteEl.classList.remove('d-none');
                            const span = noteEl.querySelector('.note-text');
                            if (span) span.innerText = stage.note;
                        } else {
                            noteEl.classList.add('d-none');
                        }
                    }
                });
            }

            // 6. Update Rider Card
            const riderContainer = document.getElementById('riderCardContainer');
            if (riderContainer) {
                if (data.rider_name || data.rider_phone) {
                    riderContainer.style.display = 'block';
                    const nameEl = document.getElementById('riderNameDisplay');
                    if (nameEl && data.rider_name) nameEl.innerText = data.rider_name;

                    const actionCol = document.getElementById('riderPhoneActionContainer');
                    if (actionCol && data.rider_phone) {
                        actionCol.innerHTML = `
                            <a href="tel:${data.rider_phone}" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" id="riderPhoneLink">
                                <i class="bi bi-telephone-outbound-fill"></i> Call Rider (${data.rider_phone})
                            </a>
                        `;
                    }
                } else if (!data.rider_name && !data.rider_phone && data.status !== 'in_transit') {
                    riderContainer.style.display = 'none';
                }
            }

        } catch (err) {
            console.error('Real-time tracking poll error:', err);
        }
    }

    // Start 5-second real-time tracking heartbeat
    setInterval(() => refreshTrackingStatus(false), 5000);
</script>
@endpush
@endif
@endsection

