@extends('layouts.public')

@section('title', 'Deal Details - ' . $deal->deal_no)

@section('content')
<div class="py-5" style="background-color: var(--od-bg); min-height: 80vh;">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('buyer.deals.index') }}" class="text-decoration-none text-muted">My Deals</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $deal->deal_no }}</li>
            </ol>
        </nav>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Deal Hero Card -->
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

                    <h2 class="heading-serif mb-2">{{ $deal->title }}</h2>
                    <p class="text-muted small mb-3">
                        Supplied by <strong class="text-dark">{{ $deal->seller->sellerProfile->farm_name ?? 'Mountain Farm' }}</strong>
                        &bull; Milked and dispatched daily from {{ $deal->seller->sellerProfile->location ?? 'Gilgit-Baltistan' }}
                    </p>

                    <!-- Progress Bar -->
                    <div class="mb-3" style="max-width: 550px;">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Delivery Progress:</span>
                            <strong class="text-dark">{{ $deal->delivered_days }} of {{ $deal->total_days }} Drops Completed ({{ $deal->progress_percent }}%)</strong>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 8px;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $deal->progress_percent }}%;" aria-valuenow="{{ $deal->progress_percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 pt-2">
                        @if($deal->status === 'active')
                            <form action="{{ route('buyer.deals.pause', $deal->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning rounded-pill px-4" onclick="return confirm('Pause this deal temporarily?');">
                                    <i class="bi bi-pause-circle me-1"></i> Pause Deliveries
                                </button>
                            </form>
                        @elseif($deal->status === 'paused')
                            <form action="{{ route('buyer.deals.resume', $deal->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success rounded-pill px-4">
                                    <i class="bi bi-play-circle me-1"></i> Resume Deliveries
                                </button>
                            </form>
                        @endif

                        @if(!in_array($deal->status, ['completed', 'cancelled']))
                            <form action="{{ route('buyer.deals.cancel', $deal->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger rounded-pill px-3" onclick="return confirm('Are you sure you want to cancel this deal subscription?');">
                                    <i class="bi bi-x-circle me-1"></i> Cancel Deal
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <div class="p-4 rounded-4 text-start d-inline-block w-100" style="background-color: var(--od-bg); border: 1px solid var(--od-border);">
                        <div class="small text-muted mb-1">Total Agreed Deal Price:</div>
                        <div class="fs-3 fw-bold text-dark-green font-serif mb-2">Rs {{ number_format($deal->total_amount) }}</div>
                        <div class="small text-muted mb-2">
                            <i class="bi bi-cash-stack text-success me-1"></i> Payment: <strong class="text-uppercase text-dark">{{ $deal->payment_method }} ({{ $deal->payment_status }})</strong>
                        </div>
                        <div class="small text-muted mb-2">
                            <i class="bi bi-clock text-primary me-1"></i> Slot: <strong class="text-dark">{{ $deal->slot_label }}</strong>
                        </div>
                        <div class="small text-muted">
                            <i class="bi bi-box-seam text-dark me-1"></i> Daily Quantity: <strong class="text-dark">{{ $deal->quantity_per_day }} {{ $deal->product->unit }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left: Delivery Schedule Timeline -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="heading-serif mb-0"><i class="bi bi-calendar3 text-success me-2"></i> Daily Delivery Schedule</h5>
                        <span class="badge bg-light text-dark border">{{ $deal->total_days }} Total Scheduled Days</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small">
                                    <th>Day</th>
                                    <th>Scheduled Date</th>
                                    <th>Delivery Slot</th>
                                    <th>Status</th>
                                    <th>Delivered At</th>
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
                                            <small class="text-muted">{{ $deal->delivery_slot === 'evening' ? 'Evening (5-7 PM)' : 'Morning (6-8 AM)' }}</small>
                                        </td>
                                        <td>
                                            <span class="{{ $deliv->status_badge }} px-3 py-1 rounded-pill small">
                                                @if($deliv->status === 'delivered')
                                                    <i class="bi bi-check2-circle me-1"></i> Delivered
                                                @elseif($deliv->status === 'dispatched')
                                                    <i class="bi bi-bicycle me-1"></i> On the Way
                                                @else
                                                    <i class="bi bi-clock me-1"></i> Scheduled
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            @if($deliv->delivered_at)
                                                <small class="text-success fw-bold">{{ $deliv->delivered_at->format('h:i A') }}</small>
                                            @else
                                                <span class="text-muted small">&mdash;</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Address & Farm Contact -->
            <div class="col-lg-4">
                <!-- Delivery Address Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h6 class="heading-serif mb-3"><i class="bi bi-geo-alt text-danger me-2"></i> Delivery Destination</h6>
                    @if($deal->address)
                        <p class="fw-bold mb-1">{{ $deal->address->full_name }}</p>
                        <p class="text-muted small mb-2">
                            {{ $deal->address->address_line }}<br>
                            {{ $deal->address->area }}, {{ $deal->address->city }}
                        </p>
                        <p class="small text-muted mb-0"><i class="bi bi-telephone me-1"></i> {{ $deal->address->phone }}</p>
                    @else
                        <p class="text-muted small mb-0">Delivery to default registered address.</p>
                    @endif

                    @if($deal->special_instructions)
                        <div class="mt-3 p-3 rounded-3 bg-light border small">
                            <strong class="d-block text-dark mb-1">Rider Instructions:</strong>
                            <span class="text-muted">{{ $deal->special_instructions }}</span>
                        </div>
                    @endif
                </div>

                <!-- Farm Details Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h6 class="heading-serif mb-3"><i class="bi bi-shop text-success me-2"></i> Producing Farm</h6>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle overflow-hidden border" style="width: 50px; height: 50px; min-width: 50px;">
                            <img src="{{ asset('storage/' . ($deal->seller->sellerProfile->photo_path ?? 'farms/hunza.jpg')) }}" alt="Farm" class="w-100 h-100 object-fit-cover">
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">{{ $deal->seller->sellerProfile->farm_name ?? 'Farm' }}</h6>
                            <small class="text-muted">{{ $deal->seller->sellerProfile->location ?? 'Gilgit-Baltistan' }}</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">
                        {{ Str::limit($deal->seller->sellerProfile->description ?? 'Traditional organic livestock farming with cold-chain dairy delivery.', 90) }}
                    </p>
                    <a href="{{ route('farmers.show', $deal->seller->id) }}" class="btn btn-outline-darkgreen rounded-pill btn-sm w-100">
                        View Farm Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
