@extends('layouts.panel')

@section('title', 'Orders - Farmer Portal')
@section('page_heading', 'Farm Orders Management')

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Status Filters -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <ul class="nav nav-pills gap-1 flex-wrap">
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1 text-sm {{ !$currentStatus ? 'active bg-primary' : 'text-dark' }}" href="{{ route('seller.orders.index') }}">
                    All Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'placed' ? 'active bg-info text-dark' : 'text-dark' }}" href="{{ route('seller.orders.index', ['status' => 'placed']) }}">
                    New / Placed
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'confirmed' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('seller.orders.index', ['status' => 'confirmed']) }}">
                    Confirmed
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'packing' ? 'active bg-secondary' : 'text-dark' }}" href="{{ route('seller.orders.index', ['status' => 'packing']) }}">
                    Packing
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'in_transit' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('seller.orders.index', ['status' => 'in_transit']) }}">
                    Out for Delivery
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'delivered' ? 'active bg-success' : 'text-dark' }}" href="{{ route('seller.orders.index', ['status' => 'delivered']) }}">
                    Delivered
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'cancelled' ? 'active bg-danger' : 'text-dark' }}" href="{{ route('seller.orders.index', ['status' => 'cancelled']) }}">
                    Cancelled
                </a>
            </li>
        </ul>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        @if($orders->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-cart-x fs-1 text-gold d-block mb-3"></i>
                <h5 class="font-serif fw-bold text-dark">No Orders Found</h5>
                <p class="small mb-0">No customer orders matching this status filter.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Order #</th>
                            <th>Customer & Location</th>
                            <th>Items Snapshot</th>
                            <th>Subtotal</th>
                            <th>Delivery</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
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
                            @endphp
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-dark">
                                    {{ $order->order_no }}
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $order->buyer->name }}</div>
                                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i> {{ $order->orderGroup->shipping_city ?? 'Gilgit' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $order->items->count() }} items</span>
                                </td>
                                <td class="small">
                                    Rs {{ number_format($order->subtotal) }}
                                </td>
                                <td class="small text-muted">
                                    Rs {{ number_format($order->delivery_fee) }}
                                </td>
                                <td class="fw-bold text-dark-green font-serif">
                                    Rs {{ number_format($order->total) }}
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }} px-2 py-1 rounded-pill small">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success text-white' : 'bg-warning text-dark' }} px-2 py-1 rounded-pill small">
                                        {{ strtoupper($order->payment_status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        <a href="{{ route('seller.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2.5" title="Print Packing Slip">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a href="{{ route('seller.orders.show', $order->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                            Manage &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
