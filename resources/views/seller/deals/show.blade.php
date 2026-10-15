@extends('layouts.panel')

@section('title', 'Deal Schedule - ' . $deal->deal_no)
@section('page_heading', 'Subscription Schedule: ' . $deal->deal_no)

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <div class="mb-4">
        <a href="{{ route('seller.deals.index') }}" class="btn btn-outline-darkgreen rounded-pill btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Farm Deals
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Deal Overview Banner -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge {{ $deal->status_badge }} px-3 py-1 rounded-pill text-uppercase">
                        {{ $deal->status }}
                    </span>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                        {{ $deal->duration_label }}
                    </span>
                    <span class="badge bg-light text-muted border font-monospace">
                        {{ $deal->deal_no }}
                    </span>
                </div>

                <h3 class="heading-serif mb-2">{{ $deal->title }}</h3>
                <p class="text-muted small mb-3">
                    Customer: <strong class="text-dark">{{ $deal->buyer->name }}</strong> &bull;
                    Phone: <a href="tel:{{ $deal->address->phone ?? $deal->buyer->phone }}" class="text-decoration-none fw-semibold">{{ $deal->address->phone ?? $deal->buyer->phone }}</a>
                </p>

                <!-- Progress Bar -->
                <div class="mb-3" style="max-width: 500px;">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Fulfillment:</span>
                        <strong class="text-dark">{{ $deal->delivered_days }} of {{ $deal->total_days }} Drops Completed ({{ $deal->progress_percent }}%)</strong>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $deal->progress_percent }}%;"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 text-lg-end">
                <div class="p-3 rounded-4 text-start d-inline-block w-100 bg-light border">
                    <div class="small text-muted mb-1">Total Deal Payout:</div>
                    <div class="fs-4 fw-bold text-dark-green font-serif mb-2">Rs {{ number_format($deal->total_amount) }}</div>
                    <div class="small text-muted mb-1">
                        <i class="bi bi-clock me-1 text-primary"></i> Slot: <strong>{{ $deal->slot_label }}</strong>
                    </div>
                    <div class="small text-muted">
                        <i class="bi bi-box-seam me-1"></i> Drop: <strong>{{ $deal->quantity_per_day }} {{ $deal->product->unit }}</strong> per morning
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Delivery Destination -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="heading-serif mb-3"><i class="bi bi-geo-alt text-danger me-2"></i> Drop-off Address</h6>
                @if($deal->address)
                    <h6 class="fw-bold mb-1">{{ $deal->address->full_name }}</h6>
                    <p class="text-muted small mb-2">
                        {{ $deal->address->address_line }}<br>
                        {{ $deal->address->area }}, {{ $deal->address->city }}
                    </p>
                    <p class="small text-muted mb-0"><i class="bi bi-telephone me-1"></i> {{ $deal->address->phone }}</p>
                @else
                    <p class="text-muted small mb-0">Standard buyer address.</p>
                @endif

                @if($deal->special_instructions)
                    <div class="mt-3 p-3 rounded-3 bg-light border small">
                        <strong class="d-block text-dark mb-1">Customer Delivery Note:</strong>
                        <span class="text-muted">{{ $deal->special_instructions }}</span>
                    </div>
                @endif
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h6 class="heading-serif mb-3"><i class="bi bi-box-seam text-success me-2"></i> Subscribed Product</h6>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 overflow-hidden border bg-light" style="width: 50px; height: 50px; min-width: 50px;">
                        @if($deal->product->primary_image)
                            <img src="{{ $deal->product->primary_image_url }}" alt="Product" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null; this.src='{{ asset('images/products/fresh_milk.jpg') }}';">
                        @else
                            <i class="bi bi-egg-fried fs-4 text-muted"></i>
                        @endif
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">{{ $deal->product->name }}</h6>
                        <small class="text-muted">Rs {{ number_format($deal->product->price) }} / {{ $deal->product->unit }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Delivery Schedule Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="heading-serif mb-0"><i class="bi bi-calendar-check text-success me-2"></i> Deal Schedule Days</h5>
                    <span class="badge bg-light text-dark border">{{ $deal->total_days }} Total Days</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small">
                                <th>Day</th>
                                <th>Scheduled Date</th>
                                <th>Slot</th>
                                <th>Status</th>
                                <th>Delivered At</th>
                                <th class="text-end">Fulfillment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deal->deliveries as $deliv)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill">
                                            Day {{ $deliv->day_number }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">
                                        {{ $deliv->scheduled_date->format('D, d M Y') }}
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $deal->delivery_slot === 'evening' ? 'Evening' : 'Morning' }}</small>
                                    </td>
                                    <td>
                                        <span class="{{ $deliv->status_badge }} px-3 py-1 rounded-pill small">
                                            {{ ucfirst($deliv->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($deliv->delivered_at)
                                            <small class="text-success fw-bold">{{ $deliv->delivered_at->format('h:i A') }}</small>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($deliv->status !== 'delivered')
                                            <form action="{{ route('seller.deals.deliveries.mark', $deliv->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="delivered">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" title="Mark this day delivered">
                                                    <i class="bi bi-check2 me-1"></i> Delivered
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill"></i> Done</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
