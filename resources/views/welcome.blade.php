@extends('layouts.public')

@section('title', 'Pristine Himalayan Organic Dairy Marketplace')
@section('meta_description', 'Pure, natural, grass-fed organic dairy products sourced directly from the high-altitude mountain pastures of Gilgit-Baltistan, Pakistan.')

@section('content')
<!-- 1. Hero Section -->
<section class="py-5 position-relative text-white" style="background: linear-gradient(135deg, rgba(31, 58, 39, 0.94) 0%, rgba(21, 39, 26, 0.96) 100%), url('{{ asset('images/banners/hero.jpg') }}') center/cover no-repeat; overflow: hidden;">
    <div class="container py-lg-5 position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge rounded-pill px-3 py-2 mb-3 border border-warning border-opacity-50 d-inline-flex align-items-center gap-1.5" style="background-color: rgba(0, 0, 0, 0.45); backdrop-filter: blur(8px); color: #E0A93B !important; font-size: 0.85rem; font-weight: 600;">
                    <i class="bi bi-geo-alt-fill text-warning"></i> Direct from Hunza, Skardu &amp; Ghizer Pastures
                </span>
                <h1 class="display-4 fw-bold heading-serif text-white mb-3" style="line-height: 1.15;">
                    Pure, Grass-Fed Mountain Dairy Directly from Local Herders
                </h1>
                <p class="lead text-white-50 mb-3" style="max-width: 580px;">
                    Handcrafted artisanal bilona ghee, raw high-altitude milk, cave-aged cheeses, and probiotic yogurt produced at 8,000+ feet in the pure alpine air of Gilgit-Baltistan.
                </p>

                <!-- Hero Live Search Input -->
                <form action="{{ route('shop.index') }}" method="GET" class="mb-4 position-relative" style="max-width: 540px;" id="heroSearchForm">
                    <div class="d-flex align-items-center bg-white rounded-pill shadow-lg p-1.5 ps-3 border">
                        <i class="bi bi-search text-success fs-5 me-2 flex-shrink-0"></i>
                        <input type="text" name="q" class="form-control border-0 bg-transparent text-dark live-search-input shadow-none fs-6 px-1 flex-grow-1" placeholder="Search fresh yak milk, desi ghee, artisan cheese..." autocomplete="off">
                        <button class="btn btn-gold rounded-pill px-4 py-2 fw-bold flex-shrink-0" type="submit">
                            Search
                        </button>
                    </div>
                </form>

                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('shop.index') }}" class="btn btn-outline-light btn-lg px-4 shadow-sm">
                        <i class="bi bi-grid me-1"></i> Browse Marketplace
                    </a>
                    <a href="{{ route('farmers.index') }}" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-people me-1"></i> Meet Our Farmers
                    </a>
                </div>
            </div>

            <div class="col-lg-5 text-center">
                <div class="card bg-white p-4 shadow-lg border-0 rounded-4 text-dark text-start position-relative overflow-hidden">
                    <div class="position-relative rounded-3 overflow-hidden mb-3" style="height: 160px;">
                        <img src="{{ asset('images/banners/hero.jpg') }}" alt="Himalayan Mountain Pasture Dairy" class="w-100 h-100 object-fit-cover">
                        <div class="position-absolute top-0 start-0 m-2 badge bg-success shadow-sm">
                            <i class="bi bi-patch-check-fill me-1"></i> 100% Certified Organic
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 42px; height: 42px; background-color: var(--od-primary);">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="heading-serif mb-0">Our Himalayan Promise</h5>
                            <small class="text-muted">Direct high-altitude supply chain</small>
                        </div>
                    </div>
                    <ul class="list-unstyled d-flex flex-column gap-2 small text-muted mb-0 pt-2 border-top">
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Free grazing on alpine medicinal pastures</li>
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Zero synthetic hormones, antibiotics, or additives</li>
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Traditional slow churned bilona desi ghee</li>
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Same-day cold dispatch across Gilgit-Baltistan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Green Feature Strip -->
<section class="py-3 text-white" style="background-color: var(--od-primary); border-bottom: 2px solid var(--od-gold);">
    <div class="container">
        <div class="row g-3 text-center text-md-start">
            <div class="col-6 col-md-3 d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                <i class="bi bi-flower1 fs-3 text-warning"></i>
                <div>
                    <strong class="d-block small leading-tight">100% Organic Pastures</strong>
                    <small class="text-white-50" style="font-size: 0.75rem;">8,000+ ft wild flora</small>
                </div>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                <i class="bi bi-snow fs-3 text-warning"></i>
                <div>
                    <strong class="d-block small leading-tight">Cold-Chain Guaranteed</strong>
                    <small class="text-white-50" style="font-size: 0.75rem;">Temperature-controlled</small>
                </div>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                <i class="bi bi-geo-alt-fill fs-3 text-warning"></i>
                <div>
                    <strong class="d-block small leading-tight">Direct from GB Herders</strong>
                    <small class="text-white-50" style="font-size: 0.75rem;">Hunza, Skardu & Ghizer</small>
                </div>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                <i class="bi bi-shield-lock-fill fs-3 text-warning"></i>
                <div>
                    <strong class="d-block small leading-tight">Pure Bilona Method</strong>
                    <small class="text-white-50" style="font-size: 0.75rem;">Zero artificial chemicals</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Category Tiles -->
<section class="py-5" style="background-color: var(--od-bg);">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="text-uppercase text-muted fw-bold small" style="letter-spacing: 1px;">Fresh Categories</span>
                <h2 class="heading-serif mb-0">Browse by Dairy Type</h2>
            </div>
            <a href="{{ route('shop.index') }}" class="text-success fw-bold text-decoration-none">View All Categories <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-3">
            @foreach($categories as $category)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('shop.index', ['category' => $category->slug]) }}" class="text-decoration-none">
                        <div class="card overflow-hidden h-100 shadow-sm transition-hover border-0 rounded-4 position-relative" style="min-height: 140px;">
                            @php
                                $catImg = file_exists(public_path('images/categories/' . $category->slug . '.jpg'))
                                    ? asset('images/categories/' . $category->slug . '.jpg')
                                    : (file_exists(public_path('images/products/' . $category->slug . '.jpg'))
                                        ? asset('images/products/' . $category->slug . '.jpg')
                                        : null);
                            @endphp
                            @if($catImg)
                                <img src="{{ $catImg }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0">
                                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.2) 0%, rgba(31,58,39,0.88) 100%);"></div>
                            @else
                                <div class="w-100 h-100 position-absolute top-0 start-0" style="background: linear-gradient(135deg, #EBF5EE 0%, #D7E8DC 100%);"></div>
                            @endif
                            <div class="position-relative p-3 h-100 d-flex flex-column justify-content-end text-white">
                                <h5 class="heading-serif mb-0 fw-bold {{ $catImg ? 'text-white' : 'text-dark' }}">{{ $category->name }}</h5>
                                <small class="{{ $catImg ? 'text-white-50' : 'text-muted' }}">{{ $category->products_count }} {{ Str::plural('product', $category->products_count) }}</small>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 4. "Fresh Today" (4 Latest Approved Products) -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="badge badge-status-new mb-1">Today's Morning Batches</span>
                <h2 class="heading-serif mb-0">Fresh Today</h2>
            </div>
            <a href="{{ route('shop.index') }}" class="text-success fw-bold text-decoration-none">Explore All Items <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            @foreach($freshTodayProducts as $product)
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100 p-3 shadow-sm d-flex flex-column justify-content-between">
                        <div>
                            <!-- Product Image / Placeholder -->
                            <div class="rounded-3 mb-3 d-flex align-items-center justify-content-center position-relative overflow-hidden" style="height: 180px; background: linear-gradient(135deg, #F5F1E8 0%, #E8E2D2 100%);">
                                @if($product->primary_image)
                                    <img src="{{ asset('storage/' . $product->primary_image) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <div class="text-center text-muted">
                                        <div class="rounded-circle d-inline-flex p-3 bg-white shadow-sm mb-1 text-success">
                                            <i class="bi bi-egg-fried fs-2"></i>
                                        </div>
                                        <div class="small fw-semibold text-dark">{{ $product->category->name }}</div>
                                    </div>
                                @endif
                                <span class="position-absolute top-0 start-0 m-2 badge badge-status-ok">100% Organic</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ Str::limit($product->seller->sellerProfile->farm_name ?? 'Farm', 18) }}</small>
                                <div class="small text-warning d-flex align-items-center gap-1">
                                    <i class="bi bi-star-fill"></i> <span class="text-dark fw-bold">{{ $product->average_rating > 0 ? $product->average_rating : '5.0' }}</span> <span class="text-muted" style="font-size: 0.72rem;">({{ $product->review_count }})</span>
                                </div>
                            </div>

                            <h5 class="heading-serif mb-1">
                                <a href="{{ route('shop.show', $product->slug) }}" class="text-dark text-decoration-none hover-gold">
                                    {{ $product->name }}
                                </a>
                            </h5>
                            <p class="text-muted small mb-3">
                                {{ Str::limit($product->description, 60) }}
                            </p>
                        </div>

                        <div class="pt-3 border-top d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <span class="d-block heading-serif fs-5 fw-bold text-dark">{{ $product->formatted_price }}</span>
                                <small class="text-muted">per {{ $product->unit }}</small>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-primary px-3" onclick="addToCart({{ $product->id }}, null, 1, this)" title="Quick Add">
                                    <i class="bi bi-bag-plus me-1"></i> Add
                                </button>
                                <a href="{{ route('shop.show', $product->slug) }}" class="btn btn-sm btn-outline-darkgreen px-2" title="Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Farm Deals Showcase (3 Days, 1 Week, 1 Month) -->
<section class="py-5" style="background: linear-gradient(180deg, #FFFFFF 0%, #F7F3EA 100%);">
    <div class="container py-2">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="badge rounded-pill px-3 py-1 text-dark mb-1 fw-bold" style="background-color: var(--od-gold);">
                    <i class="bi bi-tag-fill me-1"></i> Save Up to 15%
                </span>
                <h2 class="heading-serif mb-0">Daily Farm Deals Between Buyer &amp; Seller</h2>
                <p class="text-muted small mb-0">Set up a recurring morning milk deal directly with your favorite valley farmer.</p>
            </div>
            <a href="{{ route('deals.index') }}" class="btn btn-outline-darkgreen rounded-pill px-4 mt-3 mt-md-0">
                View All Deals <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <!-- 3 Days Deal Card -->
            <div class="col-md-4">
                <div class="card h-100 p-4 border-0 rounded-4 shadow-sm bg-white transition-hover position-relative">
                    <span class="position-absolute top-0 end-0 m-3 badge bg-success-subtle text-success border border-success-subtle rounded-pill">5% OFF</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white mb-3" style="width: 50px; height: 50px; background-color: var(--od-primary);">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </div>
                    <h4 class="heading-serif mb-1">3 Days Trial Deal</h4>
                    <span class="badge bg-light text-dark border align-self-start mb-3">3 Days Morning Batch</span>
                    <p class="text-muted small mb-4">
                        Sample fresh raw pasture milk or curd directly from morning milking. Zero long-term lock-in.
                    </p>
                    <a href="{{ route('deals.index', ['duration' => '3_days']) }}" class="btn btn-outline-darkgreen rounded-pill btn-sm w-100 mt-auto">
                        Explore 3-Day Deals
                    </a>
                </div>
            </div>

            <!-- 1 Week Deal Card -->
            <div class="col-md-4">
                <div class="card h-100 p-4 border-2 rounded-4 shadow bg-white transition-hover position-relative" style="border-color: var(--od-gold) !important;">
                    <span class="position-absolute top-0 end-0 m-3 badge rounded-pill px-3 py-1 text-dark fw-bold" style="background-color: var(--od-gold);">10% OFF</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white mb-3" style="width: 50px; height: 50px; background-color: var(--od-gold);">
                        <i class="bi bi-calendar-week fs-4 text-dark"></i>
                    </div>
                    <h4 class="heading-serif mb-1">1 Week Routine Deal</h4>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle align-self-start mb-3">7 Consecutive Days</span>
                    <p class="text-muted small mb-4">
                        Guaranteed morning delivery every single day for a complete week. Loved by families in Gilgit &amp; Skardu.
                    </p>
                    <a href="{{ route('deals.index', ['duration' => '1_week']) }}" class="btn btn-primary rounded-pill btn-sm w-100 mt-auto">
                        Explore 1-Week Deals
                    </a>
                </div>
            </div>

            <!-- 1 Month Deal Card -->
            <div class="col-md-4">
                <div class="card h-100 p-4 border-0 rounded-4 shadow-sm bg-white transition-hover position-relative">
                    <span class="position-absolute top-0 end-0 m-3 badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">15% OFF</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white mb-3" style="width: 50px; height: 50px; background-color: var(--od-dark-green);">
                        <i class="bi bi-calendar-month fs-4"></i>
                    </div>
                    <h4 class="heading-serif mb-1">1 Month Family Supply</h4>
                    <span class="badge bg-light text-dark border align-self-start mb-3">30 Days Supply</span>
                    <p class="text-muted small mb-4">
                        Full month of pure mountain dairy deliveries with maximum savings. Easily pause deliveries if traveling.
                    </p>
                    <a href="{{ route('deals.index', ['duration' => '1_month']) }}" class="btn btn-outline-darkgreen rounded-pill btn-sm w-100 mt-auto">
                        Explore 1-Month Deals
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. How It Works (4 Steps) -->
<section class="py-5" style="background-color: var(--od-bg);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-uppercase text-muted fw-bold small" style="letter-spacing: 1px;">Our Ethical Process</span>
            <h2 class="heading-serif mb-1">How Mountain Dairy Reaches You</h2>
            <p class="text-muted small mx-auto" style="max-width: 500px;">Direct connection between Himalayan farmers and your kitchen.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card p-4 text-center h-100 shadow-sm border-0">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 60px; height: 60px; background-color: var(--od-primary);">
                        <span class="fs-4 fw-bold">1</span>
                    </div>
                    <h5 class="heading-serif mb-2">Alpine Grazing</h5>
                    <p class="text-muted small mb-0">Cows and yaks graze freely on high-altitude pastures rich with medicinal mountain herbs and glacial spring water.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card p-4 text-center h-100 shadow-sm border-0">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 60px; height: 60px; background-color: var(--od-primary);">
                        <span class="fs-4 fw-bold">2</span>
                    </div>
                    <h5 class="heading-serif mb-2">Traditional Crafting</h5>
                    <p class="text-muted small mb-0">Raw milk is slow-churned in wooden bilonas into pure golden ghee, cultured butter, and live artisan curd.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card p-4 text-center h-100 shadow-sm border-0">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 60px; height: 60px; background-color: var(--od-primary);">
                        <span class="fs-4 fw-bold">3</span>
                    </div>
                    <h5 class="heading-serif mb-2">Order Fresh Batches</h5>
                    <p class="text-muted small mb-0">Choose your favorite verified farm in Hunza, Skardu, or Ghizer with transparent batch and milking dates.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card p-4 text-center h-100 shadow-sm border-0">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 60px; height: 60px; background-color: var(--od-primary);">
                        <span class="fs-4 fw-bold">4</span>
                    </div>
                    <h5 class="heading-serif mb-2">Cold-Chain Delivery</h5>
                    <p class="text-muted small mb-0">Our insulated riders ensure your fresh organic dairy arrives cold and intact at your doorstep.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. Seller CTA Band -->
<section class="py-5" style="background: linear-gradient(135deg, var(--od-dark-green) 0%, #17321e 100%); color: white;">
    <div class="container py-3">
        <div class="row align-items-center justify-content-between g-4">
            <div class="col-lg-8">
                <span class="badge rounded-pill px-3 py-2 text-dark mb-2" style="background-color: var(--od-gold);">
                    <i class="bi bi-shop me-1"></i> For Local Dairy Producers
                </span>
                <h2 class="display-6 heading-serif text-white fw-bold mb-2">
                    Are you a dairy farmer in Gilgit-Baltistan?
                </h2>
                <p class="text-white-50 lead fs-6 mb-0">
                    Register your pasture farm on Organic Dairy to list your fresh milk, ghee, and cheeses directly to thousands of households. Fair prices with zero middlemen.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('register') }}" class="btn btn-gold btn-lg px-4 shadow">
                    <i class="bi bi-person-plus me-1"></i> Register Your Farm
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
