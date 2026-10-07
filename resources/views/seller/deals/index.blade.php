@extends('layouts.panel')

@section('title', 'Farm Deals & Subscriptions - Farmer Portal')
@section('page_heading', 'Farm Deals & Subscriptions')

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">Active Subscriptions</span>
                    <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-calendar2-check fs-5"></i>
                    </div>
                </div>
                <h3 class="heading-serif mb-1 fw-bold text-dark">{{ $kpis['active_deals'] }}</h3>
                <small class="text-muted">Ongoing active deals</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">Today's Deliveries</span>
                    <div class="rounded-circle p-2 bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-sunrise fs-5"></i>
                    </div>
                </div>
                <h3 class="heading-serif mb-1 fw-bold text-dark">{{ $kpis['today_pending_deliveries'] }}</h3>
                <small class="text-muted">Pending doorstep drops today</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">Completed Deals</span>
                    <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-check-all fs-5"></i>
                    </div>
                </div>
                <h3 class="heading-serif mb-1 fw-bold text-dark">{{ $kpis['completed_deals'] }}</h3>
                <small class="text-muted">Fully fulfilled subscriptions</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">Total Deals Revenue</span>
                    <div class="rounded-circle p-2 bg-info-subtle text-info-emphasis d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </div>
                </div>
                <h3 class="heading-serif mb-1 fw-bold text-dark-green">Rs {{ number_format($kpis['total_deals_revenue']) }}</h3>
                <small class="text-muted">Committed &amp; earned</small>
            </div>
        </div>
    </div>

    <!-- Section 1: Today's Delivery Runs Checklist -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="heading-serif mb-1"><i class="bi bi-bicycle text-success me-2"></i> Today's Doorstep Delivery Run</h5>
                <p class="text-muted small mb-0">{{ \Carbon\Carbon::today()->format('l, d F Y') }} &bull; Morning &amp; Evening Batches</p>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2">
                {{ $todayDeliveries->count() }} Customers Scheduled Today
            </span>
        </div>

        @if($todayDeliveries->isEmpty())
            <div class="p-4 text-center text-muted bg-light rounded-3">
                <i class="bi bi-cup-hot fs-2 text-gold d-block mb-2"></i>
                <h6 class="fw-bold mb-1">No Deliveries Scheduled for Today</h6>
                <small>All scheduled subscription drops for today are either completed or start on upcoming dates.</small>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th>Customer &amp; Phone</th>
                            <th>Drop Destination</th>
                            <th>Item &amp; Quantity</th>
                            <th>Time Slot</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($todayDeliveries as $delivery)
                            <tr>
                                <td>
                                    <strong class="d-block">{{ $delivery->deal->buyer->name }}</strong>
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i> {{ $delivery->deal->address->phone ?? $delivery->deal->buyer->phone }}</small>
                                </td>
                                <td>
                                    <span class="d-block small fw-semibold">{{ $delivery->deal->address->city ?? 'Gilgit' }} - {{ $delivery->deal->address->area ?? '' }}</span>
                                    <small class="text-muted">{{ Str::limit($delivery->deal->address->address_line ?? 'Standard address', 40) }}</small>
                                </td>
                                <td>
                                    <strong class="text-dark">{{ $delivery->deal->quantity_per_day }}x</strong> {{ $delivery->deal->product->name }}
                                    <small class="text-muted d-block font-monospace">Day {{ $delivery->day_number }} of {{ $delivery->deal->total_days }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $delivery->deal->delivery_slot === 'evening' ? 'bg-info-subtle text-info-emphasis' : 'bg-warning-subtle text-warning-emphasis' }} rounded-pill">
                                        <i class="bi bi-clock me-1"></i> {{ $delivery->deal->slot_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="{{ $delivery->status_badge }} px-3 py-1 rounded-pill small">
                                        {{ ucfirst($delivery->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if($delivery->status === 'scheduled')
                                        <form action="{{ route('seller.deals.deliveries.mark', $delivery->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="delivered">
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                                                <i class="bi bi-check2 me-1"></i> Mark Delivered
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill"></i> Completed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Section 2: All Deals & Subscriptions Table -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h5 class="heading-serif mb-1">Customer Subscription Agreements</h5>
                <p class="text-muted small mb-0">Track all 3-Day Trial, 1-Week Routine, and 1-Month Family Supply deals.</p>
            </div>

            <!-- Filter Pills -->
            <ul class="nav nav-pills gap-1">
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ !$currentStatus && !$currentDuration ? 'active bg-primary' : 'text-dark' }}" href="{{ route('seller.deals.index') }}">
                        All ({{ $deals->total() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentDuration === '3_days' ? 'active bg-success' : 'text-dark' }}" href="{{ route('seller.deals.index', ['duration' => '3_days']) }}">
                        3 Days ({{ $kpis['count_3_days'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentDuration === '1_week' ? 'active bg-warning text-dark' : 'text-dark' }}" href="{{ route('seller.deals.index', ['duration' => '1_week']) }}">
                        1 Week ({{ $kpis['count_1_week'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentDuration === '1_month' ? 'active bg-info text-dark' : 'text-dark' }}" href="{{ route('seller.deals.index', ['duration' => '1_month']) }}">
                        1 Month ({{ $kpis['count_1_month'] }})
                    </a>
                </li>
            </ul>
        </div>

        @if($deals->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-calendar2-x fs-1 text-gold d-block mb-3"></i>
                <h5 class="heading-serif fw-bold text-dark">No Deals Found</h5>
                <p class="small mb-0">No active or past customer deals match this criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th>Deal Ref</th>
                            <th>Buyer</th>
                            <th>Product &amp; Deal Plan</th>
                            <th>Progress</th>
                            <th>Timeline</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deals as $deal)
                            <tr>
                                <td>
                                    <strong class="font-monospace text-dark">{{ $deal->deal_no }}</strong>
                                </td>
                                <td>
                                    <strong class="d-block">{{ $deal->buyer->name }}</strong>
                                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i> {{ $deal->address->city ?? 'Gilgit' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $deal->product->name }}</strong>
                                    <div class="small text-muted">
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill">
                                            {{ $deal->duration_label }}
                                        </span>
                                        &bull; {{ $deal->quantity_per_day }}x daily
                                    </div>
                                </td>
                                <td style="min-width: 150px;">
                                    <div class="small text-muted mb-1 d-flex justify-content-between">
                                        <span>{{ $deal->delivered_days }}/{{ $deal->total_days }} Days</span>
                                        <strong>{{ $deal->progress_percent }}%</strong>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ $deal->progress_percent }}%;"></div>
                                    </div>
                                </td>
                                <td>
                                    <small class="d-block text-muted">{{ $deal->start_date->format('d M') }} &ndash; {{ $deal->end_date->format('d M, Y') }}</small>
                                    <small class="text-dark"><i class="bi bi-clock me-1"></i> {{ $deal->delivery_slot === 'evening' ? 'Evening' : 'Morning' }}</small>
                                </td>
                                <td class="fw-bold font-serif text-dark-green">
                                    Rs {{ number_format($deal->total_amount) }}
                                </td>
                                <td>
                                    <span class="badge {{ $deal->status_badge }} px-3 py-1 rounded-pill small">
                                        {{ ucfirst($deal->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('seller.deals.show', $deal->id) }}" class="btn btn-sm btn-outline-darkgreen rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i> View Schedule
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top d-flex justify-content-center">
                {{ $deals->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
