@extends('layouts.panel')

@section('title', 'Farmer Dashboard - Organic Dairy GB')
@section('page_heading', 'Farmer Control Center')

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Welcome & Quick Action Banner -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill mb-2">
                    <i class="bi bi-patch-check-fill text-gold me-1"></i> Verified Himalayan Farmer
                </span>
                <h3 class="font-serif fw-bold text-dark-green mb-1">
                    Welcome back, {{ $profile->farm_name ?? Auth::user()->name }}!
                </h3>
                <p class="text-muted small mb-0">
                    <i class="bi bi-geo-alt-fill text-gold me-1"></i> {{ $profile->location ?? 'Gilgit-Baltistan' }} &bull; Cold-chain dairy producer
                </p>
            </div>
            <div class="d-flex gap-2">
                @if($profile && $profile->status === 'approved')
                    <a href="{{ route('seller.products.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add New Product
                    </a>
                @endif
                <a href="{{ route('seller.orders.index') }}" class="btn btn-outline-darkgreen rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-cart-check me-1"></i> View Orders
                </a>
            </div>
        </div>
    </div>

    @if($lowStockCount > 0)
        <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-4 p-3.5 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Low Stock Alert: {{ $lowStockCount }} {{ Str::plural('item', $lowStockCount) }} need restocking</h6>
                    <small class="text-muted">Products at or below the {{ $lowStockThreshold }}-unit threshold risk selling out soon.</small>
                </div>
            </div>
            <a href="{{ route('seller.inventory.index') }}" class="btn btn-sm btn-darkgreen rounded-pill px-3.5 py-1.5 fw-semibold shadow-xs">
                <i class="bi bi-stack me-1"></i> Quick Restock &rarr;
            </a>
        </div>
    @endif

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Today's Sales -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Today's Sales</span>
                    <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-currency-dollar fs-5"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark-green mb-1">Rs {{ number_format($todaysSales) }}</h3>
                <div class="small text-muted">From today's confirmed customer orders</div>
            </div>
        </div>

        <!-- KPI 2: New Orders -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">New Orders</span>
                    <div class="rounded-circle p-2 bg-warning-subtle text-gold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-bell-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark mb-1">{{ $newOrdersCount }}</h3>
                <div class="small text-muted">Awaiting confirmation and packing</div>
            </div>
        </div>

        <!-- KPI 3: Active Products -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Active Products</span>
                    <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-box-seam-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark mb-1">{{ $activeProductsCount }}</h3>
                <div class="small text-muted">Live in public marketplace catalog</div>
            </div>
        </div>

        <!-- KPI 4: Low Stock Count -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 {{ $lowStockCount > 0 ? 'border-danger-subtle bg-danger-subtle bg-opacity-10' : '' }}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Low Stock Alerts</span>
                    <div class="rounded-circle p-2 {{ $lowStockCount > 0 ? 'bg-danger text-white' : 'bg-secondary-subtle text-secondary' }} d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold {{ $lowStockCount > 0 ? 'text-danger' : 'text-dark' }} mb-1">{{ $lowStockCount }}</h3>
                <div class="small text-muted">Items at or under {{ $lowStockThreshold }} units</div>
            </div>
        </div>
    </div>

    <!-- Charts and Stock Alerts Row -->
    <div class="row g-4 mb-4">
        <!-- Weekly Sales Bar Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="font-serif fw-bold text-dark-green mb-0">Weekly Sales Overview</h5>
                        <small class="text-muted">Daily dairy sales volume over the past 7 days (in PKR)</small>
                    </div>
                    <span class="badge bg-light text-dark border">Past 7 Days</span>
                </div>
                <div style="min-height: 260px;">
                    <canvas id="weeklySalesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Stock Alerts Section -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-serif fw-bold text-dark-green mb-0">Stock Alerts</h5>
                    <a href="{{ route('seller.inventory.index') }}" class="small text-decoration-none fw-semibold">Manage All</a>
                </div>

                @if($lowStockProducts->isEmpty() && $lowStockVariants->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle-fill fs-2 text-success mb-2 d-block"></i>
                        <span class="small">All product and variant inventories are well-stocked above the {{ $lowStockThreshold }}-unit threshold.</span>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($lowStockProducts as $p)
                            <div class="list-group-item px-0 py-2 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark small">{{ $p->name }}</div>
                                    <small class="text-muted">{{ $p->unit }}</small>
                                </div>
                                <span class="badge bg-danger text-white">{{ $p->stock }} left</span>
                            </div>
                        @endforeach
                        @foreach($lowStockVariants as $v)
                            <div class="list-group-item px-0 py-2 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark small">{{ $v->product->name }}</div>
                                    <small class="text-secondary">{{ $v->label }}</small>
                                </div>
                                <span class="badge bg-danger text-white">{{ $v->stock }} left</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom border-light d-flex justify-content-between align-items-center">
            <h5 class="font-serif fw-bold text-dark-green mb-0">Recent Farm Orders</h5>
            <a href="{{ route('seller.orders.index') }}" class="btn btn-sm btn-outline-darkgreen rounded-pill px-3 fw-semibold">
                View All Orders
            </a>
        </div>
        <div class="card-body p-0">
            @if($recentOrders->isEmpty())
                <div class="text-center py-5 text-muted">
                    <p class="mb-0">No orders received yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small">
                                <th class="ps-4">Order #</th>
                                <th>Buyer Name</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $ord)
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
                                    $badgeClass = $statusBadges[$ord->status] ?? 'bg-light text-dark';
                                @endphp
                                <tr>
                                    <td class="ps-4 font-monospace fw-bold text-dark">
                                        {{ $ord->order_no }}
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $ord->buyer->name }}</div>
                                        <small class="text-muted">{{ $ord->orderGroup->shipping_city ?? 'Gilgit' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $ord->items->count() }} items</span>
                                    </td>
                                    <td class="fw-bold text-dark-green">
                                        Rs {{ number_format($ord->total) }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClass }} px-2 py-1 rounded-pill small">
                                            {{ ucfirst(str_replace('_', ' ', $ord->status)) }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        {{ $ord->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('seller.orders.show', $ord->id) }}" class="btn btn-sm btn-outline-darkgreen rounded-pill px-3">
                                            Manage
                                        </a>
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

@push('scripts')
<!-- Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('weeklySalesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [{
                        label: 'Sales (PKR)',
                        data: {!! json_encode($chartData) !!},
                        backgroundColor: '#2F5D3A',
                        borderRadius: 8,
                        hoverBackgroundColor: '#1F3A27',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rs ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#F0ECE1' },
                            ticks: {
                                callback: function(value) {
                                    return 'Rs ' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
