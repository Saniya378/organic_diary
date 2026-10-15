@extends('layouts.public')

@section('title', 'Organic Dairy Shop | Fresh Himalayan Batches')
@section('meta_description', 'Shop fresh organic cow and yak milk, desi ghee, cultured butter, raw yogurt, and cave-aged cheeses from verified Gilgit-Baltistan farms.')

@section('content')
<div class="container py-4 py-lg-5">
    <!-- Breadcrumb & Title -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-success text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Shop</li>
            </ol>
        </nav>
        <h2 class="heading-serif mb-1">Mountain Farm Dairy Marketplace</h2>
        <p class="text-muted small mb-0">Browse pure, unadulterated grass-fed dairy products delivered cold across Gilgit-Baltistan.</p>
    </div>

    <div class="row g-4">
        <!-- Left Filter Panel -->
        <div class="col-lg-3">
            <div class="card p-4 shadow-sm border-0 sticky-top" style="top: 100px; z-index: 10;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="heading-serif mb-0"><i class="bi bi-sliders me-1 text-success"></i> Filters</h5>
                    @if(request()->hasAny(['category', 'min_price', 'max_price', 'sellers', 'rating', 'q', 'sort']))
                        <a href="{{ route('shop.index') }}" class="small text-danger text-decoration-none fw-semibold">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </a>
                    @endif
                </div>

                <form id="filterForm" action="{{ route('shop.index') }}" method="GET">
                    @if(request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif

                    <!-- Category Filter -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark text-uppercase" style="letter-spacing: 0.5px;">Categories</label>
                        <div class="d-flex flex-column gap-2">
                            @foreach($categories as $category)
                                @php
                                    $selectedCategories = (array) request('category', []);
                                    $isChecked = in_array($category->slug, $selectedCategories);
                                @endphp
                                <div class="form-check d-flex justify-content-between align-items-center ps-0">
                                    <div class="form-check-label d-flex align-items-center gap-2">
                                        <input class="form-check-input ms-0" type="checkbox" name="category[]" value="{{ $category->slug }}" id="cat_{{ $category->id }}" {{ $isChecked ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit()">
                                        <label for="cat_{{ $category->id }}" class="small text-dark mb-0 cursor-pointer">
                                            {{ $category->name }}
                                        </label>
                                    </div>
                                    <span class="badge bg-light text-muted border rounded-pill">{{ $category->products_count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Price Range Filter -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark text-uppercase" style="letter-spacing: 0.5px;">Price Range (PKR)</label>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <input type="number" name="min_price" value="{{ request('min_price') }}" class="form-control form-control-sm" placeholder="Min Rs">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" value="{{ request('max_price') }}" class="form-control form-control-sm" placeholder="Max Rs">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-darkgreen w-100 py-1">Apply Price</button>
                    </div>

                    <hr class="my-3">

                    <!-- Farm / Seller Filter -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark text-uppercase" style="letter-spacing: 0.5px;">Verified Farms</label>
                        <div class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
                            @foreach($approvedSellers as $seller)
                                @php
                                    $selectedSellers = (array) request('sellers', []);
                                    $isSellerChecked = in_array($seller->user_id, $selectedSellers);
                                @endphp
                                <div class="form-check d-flex justify-content-between align-items-center ps-0">
                                    <div class="form-check-label d-flex align-items-center gap-2">
                                        <input class="form-check-input ms-0" type="checkbox" name="sellers[]" value="{{ $seller->user_id }}" id="seller_{{ $seller->id }}" {{ $isSellerChecked ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit()">
                                        <label for="seller_{{ $seller->id }}" class="small text-dark mb-0 cursor-pointer">
                                            {{ Str::limit($seller->farm_name, 18) }}
                                        </label>
                                    </div>
                                    <span class="badge bg-light text-muted border rounded-pill">{{ $seller->approved_products_count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Minimum Rating Filter -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark text-uppercase" style="letter-spacing: 0.5px;">Customer Rating</label>
                        <div class="d-flex flex-column gap-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rating" id="rating_all" value="" {{ !request('rating') ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit()">
                                <label class="form-check-label small text-dark" for="rating_all">All Ratings</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rating" id="rating_4" value="4" {{ request('rating') == '4' ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit()">
                                <label class="form-check-label small text-warning" for="rating_4">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star"></i> <span class="text-dark">& Above (4★)</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rating" id="rating_3" value="3" {{ request('rating') == '3' ? 'checked' : '' }} onchange="document.getElementById('filterForm').submit()">
                                <label class="form-check-label small text-warning" for="rating_3">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star"></i><i class="bi bi-star"></i> <span class="text-dark">& Above (3★)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Products Section -->
        <div class="col-lg-9">
            <!-- Top Filter & Search Bar -->
            <div class="card p-3 shadow-sm border-0 mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <span class="text-muted small">Showing <strong>{{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}</strong> of <strong>{{ $products->total() }}</strong> organic dairy products</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <!-- Search Box in Shop -->
                        <form action="{{ route('shop.index') }}" method="GET" class="d-flex align-items-center">
                            @foreach(request()->except(['q', 'page']) as $k => $v)
                                @if(is_array($v))
                                    @foreach($v as $val)
                                        <input type="hidden" name="{{ $k }}[]" value="{{ $val }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endif
                            @endforeach
                            <div class="input-group input-group-sm position-relative" style="width: 220px;">
                                <input type="text" name="q" value="{{ request('q') }}" class="form-control live-search-input" placeholder="Search dairy..." autocomplete="off">
                                <button class="btn btn-outline-custom" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>

                        <!-- Sort Dropdown -->
                        <form action="{{ route('shop.index') }}" method="GET" class="d-flex align-items-center">
                            @foreach(request()->except(['sort', 'page']) as $k => $v)
                                @if(is_array($v))
                                    @foreach($v as $val)
                                        <input type="hidden" name="{{ $k }}[]" value="{{ $val }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endif
                            @endforeach
                            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 150px;">
                                <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Sort: Newest First</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 3-Column Product Grid -->
            <div class="row g-4">
                @forelse($products as $product)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 p-3 shadow-sm d-flex flex-column justify-content-between position-relative">
                            <div>
                                <!-- Product Image / Placeholder -->
                                <div class="rounded-3 mb-3 d-flex align-items-center justify-content-center position-relative overflow-hidden" style="height: 190px; background: linear-gradient(135deg, #F5F1E8 0%, #E8E2D2 100%);">
                                    @if($product->primary_image)
                                        <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null; this.src='{{ asset('images/products/fresh_milk.jpg') }}';">
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
                                    {{ Str::limit($product->description, 65) }}
                                </p>
                            </div>

                            <div class="pt-3 border-top d-flex justify-content-between align-items-center gap-2">
                                <div>
                                    <span class="d-block heading-serif fs-5 fw-bold text-dark">{{ $product->formatted_price }}</span>
                                    <small class="text-muted">per {{ $product->unit }}</small>
                                </div>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-primary px-3" onclick="addToCart({{ $product->id }}, null, 1, this)" title="Quick Add to Basket">
                                        <i class="bi bi-bag-plus me-1"></i> Add
                                    </button>
                                    <a href="{{ route('shop.show', $product->slug) }}" class="btn btn-sm btn-outline-darkgreen px-2" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card p-5 text-center shadow-sm">
                            <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center text-muted mb-3" style="width: 72px; height: 72px; background-color: #FAF7F0;">
                                <i class="bi bi-search fs-1 text-warning"></i>
                            </div>
                            <h4 class="heading-serif mb-2">No Matching Products Found</h4>
                            <p class="text-muted small mx-auto mb-4" style="max-width: 420px;">
                                We couldn't find any products matching your active filters. Try adjusting your categories, price range, or search term.
                            </p>
                            <div>
                                <a href="{{ route('shop.index') }}" class="btn btn-outline-darkgreen px-4">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Clear All Filters
                                </a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
