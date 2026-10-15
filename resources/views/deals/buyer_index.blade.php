@extends('layouts.public')

@section('title', 'My Deals & Subscriptions - Organic Dairy')

@section('content')
<div class="py-5" style="background-color: var(--od-bg); min-height: 80vh;">
    <div class="container">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <span class="badge badge-status-new mb-1">Customer Portal</span>
                <h2 class="heading-serif mb-0">My Deals &amp; Subscriptions</h2>
                <p class="text-muted small mb-0">Manage your ongoing 3-day, weekly, and monthly morning dairy deliveries.</p>
            </div>
            <a href="{{ route('deals.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Start New Deal
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($deals->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center text-gold mb-3" style="width: 72px; height: 72px; background-color: var(--od-bg);">
                    <i class="bi bi-calendar2-range fs-1"></i>
                </div>
                <h4 class="heading-serif mb-2">No Active Deals Yet</h4>
                <p class="text-muted small mx-auto mb-4" style="max-width: 440px;">
                    You haven't subscribed to any farm deals yet. Try our 3-Day Trial Deal or 1-Week Routine Deal to enjoy fresh morning milk delivered directly to your doorstep.
                </p>
                <div>
                    <a href="{{ route('deals.index') }}" class="btn btn-primary rounded-pill px-4 py-2">
                        Browse Farm Deals
                    </a>
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach($deals as $deal)
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 d-flex flex-column justify-content-between">
                            <div>
                                <!-- Top Bar -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <span class="badge {{ $deal->status_badge }} px-3 py-1 rounded-pill mb-1 text-uppercase" style="font-size: 0.75rem;">
                                            {{ $deal->status }}
                                        </span>
                                        <small class="text-muted d-block font-monospace">{{ $deal->deal_no }}</small>
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                                        {{ $deal->duration_label }}
                                    </span>
                                </div>

                                <!-- Product Info -->
                                <div class="d-flex gap-3 align-items-center mb-3">
                                    <div class="rounded-3 overflow-hidden border bg-light" style="width: 64px; height: 64px; min-width: 64px;">
                                        @if($deal->product->primary_image)
                                            <img src="{{ $deal->product->primary_image_url }}" alt="{{ $deal->product->name }}" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null; this.src='{{ asset('images/products/fresh_milk.jpg') }}';">
                                        @else
                                            <i class="bi bi-egg-fried fs-3 text-muted"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="heading-serif mb-1">
                                            <a href="{{ route('buyer.deals.show', $deal->id) }}" class="text-dark text-decoration-none hover-gold">
                                                {{ $deal->product->name }}
                                            </a>
                                        </h5>
                                        <div class="small text-muted">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $deal->seller->sellerProfile->farm_name ?? 'Farm' }} &bull;
                                            <strong>{{ $deal->quantity_per_day }}x</strong> daily drop
                                        </div>
                                    </div>
                                </div>

                                <!-- Delivery Progress Bar -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Delivery Progress</span>
                                        <strong>{{ $deal->delivered_days }} of {{ $deal->total_days }} Days Delivered ({{ $deal->progress_percent }}%)</strong>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $deal->progress_percent }}%;" aria-valuenow="{{ $deal->progress_percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <!-- Detail Snippets -->
                                <div class="p-3 rounded-3 bg-light mb-3 small">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <span class="text-muted d-block">Slot:</span>
                                            <strong><i class="bi bi-clock me-1 text-primary"></i> {{ $deal->slot_label }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block">Total Deal Amount:</span>
                                            <strong class="text-dark-green font-serif fs-6">Rs {{ number_format($deal->total_amount) }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block">Start Date:</span>
                                            <span>{{ $deal->start_date->format('d M, Y') }}</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block">End Date:</span>
                                            <span>{{ $deal->end_date->format('d M, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <a href="{{ route('buyer.deals.show', $deal->id) }}" class="btn btn-outline-darkgreen rounded-pill btn-sm px-3">
                                    <i class="bi bi-calendar3 me-1"></i> View Schedule
                                </a>

                                <div class="d-flex gap-2">
                                    @if($deal->status === 'active')
                                        <form action="{{ route('buyer.deals.pause', $deal->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning rounded-pill btn-sm px-3" onclick="return confirm('Pause your daily deliveries temporarily?');">
                                                <i class="bi bi-pause-circle me-1"></i> Pause Deal
                                            </button>
                                        </form>
                                    @elseif($deal->status === 'paused')
                                        <form action="{{ route('buyer.deals.resume', $deal->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success rounded-pill btn-sm px-3">
                                                <i class="bi bi-play-circle me-1"></i> Resume Deliveries
                                            </button>
                                        </form>
                                    @endif

                                    @if(!in_array($deal->status, ['completed', 'cancelled']))
                                        <form action="{{ route('buyer.deals.cancel', $deal->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary rounded-pill btn-sm px-2 text-danger" title="Cancel Deal" onclick="return confirm('Are you sure you want to cancel this deal?');">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $deals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
