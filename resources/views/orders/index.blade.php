@extends('layouts.public')

@section('title', 'My Orders - Himalayan Organic Dairy GB')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Orders</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="font-serif fw-bold text-dark-green mb-1">My Orders</h2>
            <p class="text-muted small mb-0">Track and manage all your organic dairy orders from Gilgit-Baltistan farms.</p>
        </div>
        <a href="{{ route('track-order.form') }}" class="btn btn-outline-darkgreen rounded-pill px-4 btn-sm fw-semibold shadow-sm">
            <i class="bi bi-geo-alt-fill text-gold me-1"></i> Live Order Tracker
        </a>
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

    @if($orderGroups->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
            <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center text-muted mb-3" style="width: 80px; height: 80px; background-color: #FAF7F0;">
                <i class="bi bi-bag-x fs-1 text-gold"></i>
            </div>
            <h3 class="font-serif fw-bold text-dark-green mb-2">No Orders Placed Yet</h3>
            <p class="text-muted small mx-auto mb-4" style="max-width: 440px;">
                You haven't placed any dairy orders yet. Discover high-altitude pasture milk, artisan yak cheese, and bilona ghee from local mountain farmers.
            </p>
            <div>
                <a href="{{ route('shop.index') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                    <i class="bi bi-shop me-1"></i> Explore Dairy Marketplace
                </a>
            </div>
        </div>
    @else
        <!-- Order Groups List -->
        <div class="d-flex flex-column gap-4">
            @foreach($orderGroups as $group)
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <!-- Group Header -->
                    <div class="card-header bg-light py-3 px-4 border-bottom border-light">
                        <div class="row align-items-center g-2">
                            <div class="col-sm-6 col-md-3">
                                <span class="text-muted small d-block">Order Group</span>
                                <strong class="text-dark font-monospace">#{{ $group->group_no }}</strong>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <span class="text-muted small d-block">Date Placed</span>
                                <span class="text-secondary small fw-semibold">{{ $group->created_at->format('M d, Y - h:i A') }}</span>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <span class="text-muted small d-block">Grand Total</span>
                                <strong class="text-primary fs-6 font-serif">Rs {{ number_format($group->grand_total) }}</strong>
                            </div>
                            <div class="col-sm-6 col-md-3 text-sm-end d-flex flex-column align-items-sm-end gap-1">
                                <span class="badge {{ $group->payment && $group->payment->status === 'paid' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }}">
                                    {{ strtoupper($group->payment_method) }} &bull; {{ strtoupper($group->payment?->status ?? 'unpaid') }}
                                </span>
                                <a href="{{ route('orders.group.invoice', $group->id) }}" target="_blank" class="text-decoration-none text-muted small">
                                    <i class="bi bi-printer me-1"></i> Master Invoice
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Individual Sub-Orders Under this Group -->
                    <div class="card-body p-3 p-md-4">
                        <div class="small fw-bold text-uppercase text-muted mb-3">
                            <i class="bi bi-truck text-gold me-1"></i> {{ count($group->orders) }} Farm {{ Str::plural('Shipment', count($group->orders)) }}
                        </div>

                        <div class="d-flex flex-column gap-3">
                            @foreach($group->orders as $order)
                                @php
                                    $statusBadges = [
                                        'pending_payment' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                        'placed' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                        'new' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                        'confirmed' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'packing' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
                                        'in_transit' => 'bg-primary text-white',
                                        'delivered' => 'bg-success-subtle text-success border border-success-subtle',
                                        'cancelled' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    ];
                                    $badgeClass = $statusBadges[$order->status] ?? 'bg-light text-dark';
                                    $canCancel = in_array($order->status, ['placed', 'new', 'confirmed']);
                                @endphp

                                <div class="p-3 border rounded-3 bg-white">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom g-2">
                                        <div>
                                            <span class="fw-bold text-dark-green font-serif">
                                                &#127969; {{ $order->seller->sellerProfile->farm_name ?? $order->seller->name }}
                                            </span>
                                            <span class="badge bg-light text-muted border font-monospace ms-2">
                                                {{ $order->order_no }}
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($order->status === 'cancelled')
                                                @if($order->isCancelledBySeller())
                                                    <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill small">
                                                        <i class="bi bi-shop me-1"></i> Cancelled by Seller
                                                    </span>
                                                @elseif($order->isCancelledByAdmin())
                                                    <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill small">
                                                        <i class="bi bi-shield-x me-1"></i> Cancelled by Admin
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary text-white px-2.5 py-1 rounded-pill small">
                                                        <i class="bi bi-person-x me-1"></i> Cancelled by You
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge {{ $badgeClass }} px-3 py-1 rounded-pill">
                                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($order->status === 'cancelled')
                                        <div class="alert alert-danger-subtle text-danger py-1.5 px-3 rounded-3 small mb-3 border border-danger-subtle">
                                            <strong>Cancellation Note:</strong> {{ $order->cancel_reason ?? 'Order cancelled.' }}
                                        </div>
                                    @endif

                                    <!-- Items Row -->
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-7">
                                            <ul class="list-unstyled mb-0">
                                                @foreach($order->items as $item)
                                                    <li class="d-flex justify-content-between align-items-center py-1 small">
                                                        <span class="text-dark">
                                                            <strong>{{ $item->product_name }}</strong> &times; {{ $item->quantity }}
                                                        </span>
                                                        <span class="text-muted fw-semibold">Rs {{ number_format($item->line_total) }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>

                                        <div class="col-md-5 d-flex flex-wrap justify-content-md-end gap-2 align-items-center pt-2 pt-md-0">
                                            <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2.5 fw-semibold" title="Print Sub-Order Invoice">
                                                <i class="bi bi-printer me-1"></i> Invoice
                                            </a>
                                            <a href="{{ route('orders.track', $order->order_no) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-semibold">
                                                <i class="bi bi-geo-alt-fill me-1"></i> Track Order
                                            </a>
                                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                                                Details
                                            </a>

                                            @if($canCancel)
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#cancelModal_{{ $order->id }}">
                                                    Cancel
                                                </button>

                                                <!-- Cancel Modal -->
                                                <div class="modal fade" id="cancelModal_{{ $order->id }}" tabindex="-1" aria-hidden="true">
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
                                                                        Are you sure you want to cancel this order? The reserved dairy items will be released back to the farm's live inventory.
                                                                    </p>
                                                                    <div class="mb-3">
                                                                        <label for="reason_{{ $order->id }}" class="form-label small fw-semibold">Reason for Cancellation (Optional)</label>
                                                                        <select name="cancel_reason" id="reason_{{ $order->id }}" class="form-select form-select-sm">
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
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Group Footer -->
                    <div class="card-footer bg-light px-4 py-2 border-top d-flex flex-wrap justify-content-between align-items-center small text-muted">
                        <span><i class="bi bi-geo-alt me-1"></i> Deliver to: {{ $group->shipping_city }} ({{ $group->shipping_area }})</span>
                        <a href="{{ route('checkout.success', $group->group_no) }}" class="text-decoration-none text-muted">
                            View Receipt <i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $orderGroups->links() }}
        </div>
    @endif
</div>
@endsection
