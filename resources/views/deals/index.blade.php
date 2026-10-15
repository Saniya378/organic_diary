@extends('layouts.public')

@section('title', 'Farm Deals & Daily Subscriptions - Organic Dairy')
@section('meta_description', 'Get fresh Himalayan organic milk, ghee, and curd delivered daily with our 3-Day Trial, 1-Week Routine, and 1-Month Family Supply Deals. Save up to 15%.')

@section('content')
<!-- Hero Section -->
<section class="py-5 text-white position-relative" style="background: linear-gradient(135deg, var(--od-dark-green) 0%, #17321e 100%);">
    <div class="container py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge rounded-pill px-3 py-2 text-dark mb-3" style="background-color: var(--od-gold); font-size: 0.85rem;">
                    <i class="bi bi-stars me-1"></i> Direct From Himalayan Pastures &bull; Save Up to 15%
                </span>
                <h1 class="display-5 heading-serif fw-bold text-white mb-3">
                    Daily Farm Deals &amp; Doorstep Subscriptions
                </h1>
                <p class="lead text-white-50 fs-6 mb-4" style="max-width: 600px;">
                    Never run out of pure, unadulterated milk and curd again. Subscribe directly with verified mountain farms in Hunza, Skardu, and Ghizer for regular morning deliveries.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#browse-deals" class="btn btn-gold px-4 py-2 fw-bold shadow">
                        <i class="bi bi-cart-plus me-1"></i> Browse Eligible Products
                    </a>
                    @auth
                        <a href="{{ route('buyer.deals.index') }}" class="btn btn-outline-light px-4 py-2">
                            <i class="bi bi-calendar-check me-1"></i> My Active Deals
                        </a>
                    @endauth
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card bg-white bg-opacity-10 border-0 rounded-4 p-4 text-white shadow-lg backdrop-blur">
                    <h5 class="heading-serif mb-3 text-gold">
                        <i class="bi bi-shield-check me-1"></i> The Mountain Dairy Deal Promise
                    </h5>
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="rounded-circle p-2 bg-white bg-opacity-20 d-flex align-items-center justify-content-center text-gold" style="width: 40px; height: 40px; min-width: 40px;">
                            <i class="bi bi-sunrise fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Morning Doorstep Drop (6-8 AM)</h6>
                            <small class="text-white-50">Milked at dawn, dispatched immediately in cold-chain containers to your gate.</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="rounded-circle p-2 bg-white bg-opacity-20 d-flex align-items-center justify-content-center text-gold" style="width: 40px; height: 40px; min-width: 40px;">
                            <i class="bi bi-pause-circle fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Pause &amp; Resume Anytime</h6>
                            <small class="text-white-50">Going out of town? Pause deliveries with 1 click and resume whenever you return.</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle p-2 bg-white bg-opacity-20 d-flex align-items-center justify-content-center text-gold" style="width: 40px; height: 40px; min-width: 40px;">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Cash on Delivery or Card</h6>
                            <small class="text-white-50">Flexible payment terms directly agreed between you and the local farmer.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3 Plan Types Highlights -->
<section class="py-5" style="background-color: var(--od-bg);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-uppercase text-muted fw-bold small" style="letter-spacing: 1px;">Flexible Commitment</span>
            <h2 class="heading-serif mb-2">Choose Your Ideal Delivery Deal</h2>
            <p class="text-muted small mx-auto" style="max-width: 550px;">
                Whether you want to sample pure mountain milk or guarantee a monthly supply for your family, we have curated plans for every household.
            </p>
        </div>

        <div class="row g-4">
            <!-- 3 Days Trial Deal -->
            <div class="col-md-4">
                <div class="card h-100 border-0 rounded-4 shadow-sm p-4 text-center transition-hover bg-white position-relative">
                    <span class="position-absolute top-0 end-0 m-3 badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                        5% OFF
                    </span>
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 64px; height: 64px; background-color: var(--od-primary);">
                        <i class="bi bi-calendar-check fs-3"></i>
                    </div>
                    <h4 class="heading-serif mb-1">3 Days Trial Deal</h4>
                    <span class="badge bg-light text-dark border mb-3">3 Consecutive Morning Deliveries</span>
                    <p class="text-muted small mb-4">
                        Test the purity, aroma, and rich creaminess of Himalayan pasture milk. Great for newcomers with zero long-term commitment.
                    </p>
                    <ul class="list-unstyled text-start small text-muted mb-4 border-top pt-3">
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> 3 Morning Deliveries</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> 5% Deal Discount</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> Daily Cash on Delivery</li>
                    </ul>
                    <a href="{{ route('deals.index', ['duration' => '3_days']) }}#browse-deals" class="btn btn-outline-darkgreen rounded-pill w-100 mt-auto">
                        Explore 3-Day Deals
                    </a>
                </div>
            </div>

            <!-- 1 Week Routine Deal -->
            <div class="col-md-4">
                <div class="card h-100 border-2 rounded-4 shadow p-4 text-center transition-hover bg-white position-relative" style="border-color: var(--od-gold) !important;">
                    <span class="position-absolute top-0 end-0 m-3 badge rounded-pill px-3 py-2 text-dark fw-bold" style="background-color: var(--od-gold);">
                        10% OFF &bull; Popular
                    </span>
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 64px; height: 64px; background-color: var(--od-gold);">
                        <i class="bi bi-calendar-week fs-3 text-dark"></i>
                    </div>
                    <h4 class="heading-serif mb-1">1 Week Routine Deal</h4>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle mb-3">7 Consecutive Days</span>
                    <p class="text-muted small mb-4">
                        A full week of fresh farm dairy for your family's morning chai, smoothies, and breakfast butter. Save 10% off retail shelf price.
                    </p>
                    <ul class="list-unstyled text-start small text-muted mb-4 border-top pt-3">
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> 7 Days Continuous Supply</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> 10% Flat Deal Discount</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> 1-Click Delivery Pause</li>
                    </ul>
                    <a href="{{ route('deals.index', ['duration' => '1_week']) }}#browse-deals" class="btn btn-primary rounded-pill w-100 mt-auto">
                        Explore 1-Week Deals
                    </a>
                </div>
            </div>

            <!-- 1 Month Family Supply -->
            <div class="col-md-4">
                <div class="card h-100 border-0 rounded-4 shadow-sm p-4 text-center transition-hover bg-white position-relative">
                    <span class="position-absolute top-0 end-0 m-3 badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2">
                        15% OFF &bull; Best Value
                    </span>
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 64px; height: 64px; background-color: var(--od-dark-green);">
                        <i class="bi bi-calendar-month fs-3"></i>
                    </div>
                    <h4 class="heading-serif mb-1">1 Month Family Supply Deal</h4>
                    <span class="badge bg-light text-dark border mb-3">30 Days Household Plan</span>
                    <p class="text-muted small mb-4">
                        The ultimate peace of mind. Reserve monthly quota directly with your trusted farm. Guaranteed morning slot &amp; maximum 15% discount.
                    </p>
                    <ul class="list-unstyled text-start small text-muted mb-4 border-top pt-3">
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> 30 Days Household Milk Supply</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> 15% Maximum Savings</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> Dedicated Farmer Support</li>
                    </ul>
                    <a href="{{ route('deals.index', ['duration' => '1_month']) }}#browse-deals" class="btn btn-outline-darkgreen rounded-pill w-100 mt-auto">
                        Explore 1-Month Deals
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Products Available for Deal Subscription -->
<section id="browse-deals" class="py-5 bg-white">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom">
            <div>
                <span class="badge badge-status-new mb-1">Farm Direct</span>
                <h3 class="heading-serif mb-0">Products Eligible for Deals</h3>
            </div>
            
            <!-- Filter Pills -->
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('deals.index') }}" class="btn btn-sm rounded-pill {{ !$selectedDuration ? 'btn-primary' : 'btn-outline-secondary' }}">
                    All Deals
                </a>
                <a href="{{ route('deals.index', ['duration' => '3_days']) }}" class="btn btn-sm rounded-pill {{ $selectedDuration === '3_days' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    3 Days Deal (5% Off)
                </a>
                <a href="{{ route('deals.index', ['duration' => '1_week']) }}" class="btn btn-sm rounded-pill {{ $selectedDuration === '1_week' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    1 Week Deal (10% Off)
                </a>
                <a href="{{ route('deals.index', ['duration' => '1_month']) }}" class="btn btn-sm rounded-pill {{ $selectedDuration === '1_month' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    1 Month Deal (15% Off)
                </a>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row g-4">
            @forelse($products as $product)
                @php
                    $durationKey = $selectedDuration ?: '1_week';
                    $dealDays = match($durationKey) { '3_days' => 3, '1_month' => 30, default => 7 };
                    $discountPct = match($durationKey) { '3_days' => 5, '1_month' => 15, default => 10 };
                    $standardTotal = $product->price * $dealDays;
                    $discountedTotal = (int) round($standardTotal * (1 - $discountPct / 100));
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 p-3 shadow-sm border rounded-4 d-flex flex-column justify-content-between transition-hover">
                        <div>
                            <!-- Product Image -->
                            <div class="rounded-3 mb-3 d-flex align-items-center justify-content-center position-relative overflow-hidden" style="height: 190px; background: linear-gradient(135deg, #F5F1E8 0%, #E8E2D2 100%);">
                                @if($product->primary_image)
                                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null; this.src='{{ asset('images/products/fresh_milk.jpg') }}';">
                                @else
                                    <i class="bi bi-egg-fried fs-1 text-success"></i>
                                @endif
                                <span class="position-absolute top-0 start-0 m-2 badge badge-status-ok">100% Organic</span>
                                <span class="position-absolute top-0 end-0 m-2 badge bg-danger text-white fw-bold">
                                    {{ $discountPct }}% OFF DEAL
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ Str::limit($product->seller->sellerProfile->farm_name ?? 'Farm', 18) }}</small>
                                <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                            </div>

                            <h5 class="heading-serif mb-1">
                                <a href="{{ route('deals.create', ['product' => $product->id, 'duration' => $durationKey]) }}" class="text-dark text-decoration-none hover-gold">
                                    {{ $product->name }}
                                </a>
                            </h5>
                            <p class="text-muted small mb-3">
                                {{ Str::limit($product->description, 70) }}
                            </p>

                            <!-- Deal Price Breakdown Box -->
                            <div class="p-3 rounded-3 mb-3" style="background-color: var(--od-bg); border: 1px dashed var(--od-border);">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-muted">{{ match($durationKey) { '3_days' => '3 Days Trial', '1_month' => '1 Month (30 Days)', default => '1 Week (7 Days)' } }}</span>
                                    <span class="text-decoration-line-through text-muted small">Rs {{ number_format($standardTotal) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark-green font-serif fs-5">Rs {{ number_format($discountedTotal) }}</span>
                                    <span class="badge bg-success text-white">Save Rs {{ number_format($standardTotal - $discountedTotal) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-top d-flex gap-2">
                            <a href="{{ route('deals.create', ['product' => $product->id, 'duration' => $durationKey]) }}" class="btn btn-primary rounded-pill w-100 fw-bold d-flex align-items-center justify-content-center gap-1 shadow-sm">
                                <i class="bi bi-calendar-plus"></i> Configure Deal
                            </a>
                            <a href="{{ route('shop.show', $product->slug) }}" class="btn btn-outline-darkgreen rounded-pill px-3" title="Product Details">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No products found matching this filter.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $products->links() }}
        </div>
    </div>
</section>
@endsection
