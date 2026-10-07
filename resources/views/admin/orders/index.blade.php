@extends('layouts.panel')

@section('title', 'Manage Orders - Admin Center')
@section('page_heading', 'All Platform Orders')

@section('menu_items')
    @include('admin.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Filters & Search Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <ul class="nav nav-pills gap-1 flex-wrap">
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ !$currentStatus ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.orders.index') }}">
                        All Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'placed' ? 'active bg-warning text-dark' : 'text-dark' }}" href="{{ route('admin.orders.index', ['status' => 'placed']) }}">
                        Placed / New
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'confirmed' ? 'active bg-info text-dark' : 'text-dark' }}" href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}">
                        Confirmed
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'packing' ? 'active bg-secondary text-white' : 'text-dark' }}" href="{{ route('admin.orders.index', ['status' => 'packing']) }}">
                        Packing
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'in_transit' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.orders.index', ['status' => 'in_transit']) }}">
                        In Transit
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'delivered' ? 'active bg-success' : 'text-dark' }}" href="{{ route('admin.orders.index', ['status' => 'delivered']) }}">
                        Delivered
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'cancelled' ? 'active bg-danger' : 'text-dark' }}" href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}">
                        Cancelled
                    </a>
                </li>
            </ul>

            <form action="{{ route('admin.orders.index') }}" method="GET" class="d-flex gap-2">
                @if($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="input-group input-group-sm" style="min-width: 260px;">
                    <input type="text" name="q" class="form-control" placeholder="Search Order #, buyer, farm..." value="{{ $search }}">
                    <button type="submit" class="btn btn-outline-darkgreen">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        @if($orders->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-cart-x fs-1 text-gold d-block mb-3"></i>
                <h5 class="font-serif fw-bold text-dark">No Orders Found</h5>
                <p class="small mb-0">No orders match the current search or status filters.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Order #</th>
                            <th>Buyer</th>
                            <th>Seller Farm</th>
                            <th>Items & Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            @php
                                $statusBadges = [
                                    'placed' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'new' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'confirmed' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                    'packing' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
                                    'in_transit' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'delivered' => 'bg-success-subtle text-success border border-success-subtle',
                                    'cancelled' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                ];
                                $badgeClass = $statusBadges[$order->status] ?? 'bg-light text-dark';
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold font-monospace text-dark">{{ $order->order_no }}</span>
                                    <div class="text-xs text-muted">Grp: {{ $order->orderGroup->group_no ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $order->buyer->name ?? 'Guest Buyer' }}</div>
                                    <div class="text-xs text-muted">{{ $order->buyer->phone ?? $order->buyer->email ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">{{ $order->seller->sellerProfile->farm_name ?? $order->seller->name ?? 'Seller' }}</div>
                                    <div class="text-xs text-muted">{{ $order->seller->sellerProfile->location ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-darkgreen">{{ $order->formatted_total }}</div>
                                    <div class="text-xs text-muted">{{ $order->items->count() }} item(s) (Sub: {{ $order->formatted_subtotal }})</div>
                                </td>
                                <td>
                                    @if($order->status === 'cancelled')
                                        <span class="badge bg-danger rounded-pill px-2.5 py-1 text-xs">Cancelled</span>
                                        <div class="text-xs text-danger fw-semibold mt-1">
                                            @if($order->isCancelledBySeller())
                                                <i class="bi bi-shop me-0.5"></i> By Seller
                                            @elseif($order->isCancelledByAdmin())
                                                <i class="bi bi-shield-x me-0.5"></i> By Admin
                                            @else
                                                <i class="bi bi-person-x me-0.5"></i> By Buyer
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge {{ $badgeClass }} rounded-pill px-2.5 py-1 text-xs text-capitalize">
                                            {{ str_replace('_', ' ', $order->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis' }} rounded-pill px-2 py-1 text-xs">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i>{{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-sm text-dark">{{ $order->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-muted">{{ $order->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2" title="Print Invoice">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-darkgreen rounded-pill px-3">
                                            <i class="bi bi-eye me-1"></i> View
                                        </a>

                                        @if($order->status !== 'cancelled')
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#cancelModal-{{ $order->id }}" title="Cancel Order">
                                                <i class="bi bi-x-circle"></i>
                                            </button>

                                            <!-- Cancel Modal -->
                                            <div class="modal fade text-start" id="cancelModal-{{ $order->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content rounded-4 border-0 shadow">
                                                        <form action="{{ route('admin.orders.cancel', $order) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header border-0 pb-0">
                                                                <h5 class="modal-title font-serif fw-bold text-danger">
                                                                    <i class="bi bi-exclamation-triangle me-2"></i> Cancel Order #{{ $order->order_no }}
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body py-3">
                                                                <p class="text-muted small mb-3">
                                                                    Cancelling this order will restore the inventory stock for all its products/variants and log the reason in the status timeline.
                                                                </p>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold text-sm">Cancellation Reason <span class="text-danger">*</span></label>
                                                                    <textarea name="cancel_reason" class="form-control" rows="3" placeholder="State the reason for cancelling this order..." required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-0 pt-0">
                                                                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-danger rounded-pill px-4">Confirm Cancellation</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
