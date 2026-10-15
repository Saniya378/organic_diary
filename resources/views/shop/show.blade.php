@extends('layouts.public')

@section('title', $product->name . ' - Organic Dairy')
@section('meta_description', Str::limit($product->description, 150))

@section('content')
<div class="container py-4 py-lg-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-success text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop.index') }}" class="text-success text-decoration-none">Shop</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="text-success text-decoration-none">{{ $product->category->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-5 mb-5">
        <!-- Left: Image Gallery -->
        <div class="col-lg-6">
            <div class="card p-3 shadow-sm border-0 mb-3">
                <div class="rounded-4 d-flex align-items-center justify-content-center position-relative overflow-hidden" style="height: 380px; background: linear-gradient(135deg, #F5F1E8 0%, #E8E2D2 100%);">
                    @if($product->images->isNotEmpty())
                        <img id="mainProductImage" src="{{ $product->images->first()->url }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null; this.src='{{ asset('images/products/fresh_milk.jpg') }}';">
                    @else
                        <div class="text-center text-muted">
                            <div class="rounded-circle d-inline-flex p-4 bg-white shadow-sm mb-2 text-success">
                                <i class="bi bi-egg-fried fs-1"></i>
                            </div>
                            <h5 class="heading-serif text-dark mb-0">{{ $product->name }}</h5>
                            <small class="text-muted">{{ $product->category->name }} &bull; {{ $product->unit }}</small>
                        </div>
                    @endif
                    <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-2">
                        <span class="badge badge-status-ok shadow-sm"><i class="bi bi-flower1 me-1"></i> 100% Organic</span>
                        <span class="badge badge-status-transit shadow-sm"><i class="bi bi-patch-check-fill me-1"></i> Farm Verified</span>
                    </div>
                </div>
            </div>

            <!-- Image Thumbnails (if multiple exist) -->
            @if($product->images->count() > 1)
                <div class="d-flex gap-2">
                    @foreach($product->images as $img)
                        <div class="card p-1 shadow-sm border-0 cursor-pointer thumbnail-card" onclick="document.getElementById('mainProductImage').src='{{ $img->url }}'" style="width: 70px; height: 70px;">
                            <img src="{{ $img->url }}" class="w-100 h-100 object-fit-cover rounded-2" alt="Thumbnail" onerror="this.onerror=null; this.src='{{ asset('images/products/fresh_milk.jpg') }}';">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right: Product Purchase & Info Details -->
        <div class="col-lg-6">
            <div class="ps-lg-3">
                <!-- Seller Location & Rating Header -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <a href="{{ route('farmers.show', $product->seller->sellerProfile->id ?? 1) }}" class="text-muted text-decoration-none small">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $product->seller->sellerProfile->farm_name ?? 'Local Farm' }} &bull; {{ $product->seller->sellerProfile->location ?? 'Gilgit-Baltistan' }}
                    </a>
                    <div class="d-flex align-items-center gap-1 text-warning small">
                        <i class="bi bi-star-fill"></i>
                        <strong class="text-dark">{{ $product->average_rating > 0 ? $product->average_rating : '5.0' }}</strong>
                        <span class="text-muted">({{ $product->review_count }} {{ Str::plural('review', $product->review_count) }})</span>
                    </div>
                </div>

                <h1 class="heading-serif display-6 fw-bold mb-3 text-dark">{{ $product->name }}</h1>

                <!-- Price Box -->
                <div class="d-flex align-items-baseline gap-3 mb-3 p-3 rounded-4 bg-light border">
                    <span id="productPriceDisplay" class="heading-serif display-6 fw-bold text-success">{{ $product->formatted_price }}</span>
                    <span id="productUnitDisplay" class="text-muted small">per {{ $product->unit }}</span>
                    <span id="productStockBadge" class="badge {{ $product->stock <= 10 ? 'badge-status-low_stock' : 'badge-status-ok' }} ms-auto">
                        {{ $product->stock > 0 ? 'In Stock (' . $product->stock . ' units)' : 'Out of Stock' }}
                    </span>
                </div>

                <p class="text-muted mb-4 leading-relaxed">
                    {{ $product->description }}
                </p>

                <!-- Pack-Size Chips (Variants) -->
                @if($product->variants->isNotEmpty())
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark text-uppercase" style="letter-spacing: 0.5px;">Select Pack Size / Quantity:</label>
                        <div class="d-flex flex-wrap gap-2" id="variantChipsContainer">
                            <!-- Default base size -->
                            <button type="button" class="btn btn-sm btn-primary variant-chip" 
                                    data-variant-id="" 
                                    data-price="{{ $product->price }}" 
                                    data-formatted-price="{{ $product->formatted_price }}"
                                    data-unit="{{ $product->unit }}"
                                    data-stock="{{ $product->stock }}"
                                    onclick="selectVariant(this)">
                                {{ $product->unit }} ({{ $product->formatted_price }})
                            </button>

                            @foreach($product->variants as $variant)
                                <button type="button" class="btn btn-sm btn-outline-darkgreen variant-chip" 
                                        data-variant-id="{{ $variant->id }}" 
                                        data-price="{{ $variant->price }}" 
                                        data-formatted-price="{{ $variant->formatted_price }}"
                                        data-unit="{{ $variant->label }}"
                                        data-stock="{{ $variant->stock }}"
                                        onclick="selectVariant(this)">
                                    {{ $variant->label }} ({{ $variant->formatted_price }})
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Purchase Form -->
                <div class="mb-4">
                    <input type="hidden" id="selectedProductId" value="{{ $product->id }}">
                    <input type="hidden" id="selectedVariantId" value="">

                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-sm-4">
                            <label class="form-label small fw-bold text-dark mb-1">Quantity</label>
                            <div class="input-group">
                                <button class="btn btn-outline-custom" type="button" onclick="decrementQty()"><i class="bi bi-dash"></i></button>
                                <input type="number" name="quantity" id="quantityInput" class="form-control text-center fw-bold" value="1" min="1" max="{{ max(1, $product->stock) }}" readonly>
                                <button class="btn btn-outline-custom" type="button" onclick="incrementQty()"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>

                        <div class="col-sm-8 d-flex align-items-end gap-2">
                            <button type="button" class="btn btn-primary flex-grow-1 py-2" onclick="handleProductDetailAddToCart(this, false)">
                                <i class="bi bi-cart-plus me-1"></i> Add to Cart
                            </button>
                            <button type="button" class="btn btn-gold px-4 py-2" onclick="handleProductDetailAddToCart(this, true)">
                                Buy Now
                            </button>
                        </div>
                    </div>

                    <button type="button" onclick="window.openSupportChat('Tell me about {{ addslashes($product->name) }} ({{ $product->formatted_price }})')" class="btn btn-outline-success rounded-pill w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-xs">
                        <i class="bi bi-chat-dots-fill fs-5 text-success"></i> Ask Support About This Product
                    </button>
                </div>

                <!-- Daily Farm Deal Banner (Only if Seller has enabled deals) -->
                @if($product->deals_enabled)
                <div class="card border-0 rounded-4 p-3 mb-4 shadow-sm" style="background: linear-gradient(135deg, #FAF7F0 0%, #F0EAE1 100%); border: 1px solid var(--od-border); border-left: 4px solid var(--od-gold) !important;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 38px; height: 38px; background-color: var(--od-dark-green);">
                                <i class="bi bi-calendar2-heart fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Save up to 15% with a Daily Farm Deal</h6>
                                <small class="text-muted">Choose 3-Day Trial, 1-Week Routine, or 1-Month Family Supply.</small>
                            </div>
                        </div>
                        <a href="{{ route('deals.create', $product->id) }}" class="btn btn-sm btn-outline-darkgreen rounded-pill px-3 fw-bold">
                            <i class="bi bi-calendar-plus me-1"></i> Start Deal
                        </a>
                    </div>
                </div>
                @endif

                <!-- Fresh Batch & Quality Details -->
                <div class="card p-3.5 rounded-4 bg-light border small text-muted d-flex flex-column gap-2 mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-tag text-success me-1"></i> Batch Number:</span>
                        <strong class="text-dark font-monospace">{{ $product->batch_no ?? 'BATCH-' . $product->id . '-2026' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-calendar-check text-success me-1"></i> Milked / Produced On:</span>
                        <strong class="text-dark">{{ $product->produced_on ? $product->produced_on->format('d M Y') : 'Fresh Daily' }}</strong>
                    </div>
                    @if($product->shelf_life_days)
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-hourglass-split text-gold me-1"></i> Shelf Life:</span>
                        <strong class="text-dark">{{ $product->shelf_life_days }} Days from Milking</strong>
                    </div>
                    @endif
                    @if($product->expires_at)
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-patch-check-fill text-primary me-1"></i> Best Before / Expiry:</span>
                        @php $freshness = $product->freshness_status; @endphp
                        <div>
                            <span class="text-dark fw-bold">{{ $product->expires_at->format('d M Y') }}</span>
                            <span class="badge {{ $freshness['badge_class'] }} ms-1 text-2xs">{{ $freshness['label'] }}</span>
                        </div>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between align-items-center border-top pt-2">
                        <span><i class="bi bi-snow text-info me-1"></i> Cold-Chain Dispatch:</span>
                        <span class="text-success fw-bold">Cold Packaged within 24 Hours</span>
                    </div>
                </div>

                <!-- Seller Farm Card -->
                @if($product->seller && $product->seller->sellerProfile)
                    @php $profile = $product->seller->sellerProfile; @endphp
                    <div class="card p-3 rounded-4 border shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-dark" style="width: 48px; height: 48px; background-color: var(--od-gold);">
                                    <i class="bi bi-shop fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="heading-serif mb-0 fw-bold">{{ $profile->farm_name }}</h6>
                                    <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $profile->location }}</small>
                                </div>
                            </div>
                            <a href="{{ route('farmers.show', $profile->id) }}" class="btn btn-sm btn-outline-darkgreen">
                                View Farm Profile
                            </a>
                        </div>
                        <p class="text-muted small mt-2 mb-0">
                            {{ Str::limit($profile->description, 110) }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Customer Reviews Section -->
    <div class="card p-4 p-md-5 shadow-sm border-0 mb-5" id="reviews-section">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="heading-serif mb-1">Customer Reviews</h4>
                <p class="text-muted small mb-0">Feedback from verified buyers of this mountain dairy batch.</p>
            </div>
            <div class="text-end">
                <span class="display-6 heading-serif fw-bold text-dark">{{ $product->average_rating > 0 ? $product->average_rating : '5.0' }}</span>
                <div class="text-warning small">
                    @php $avgRounded = round($product->average_rating > 0 ? $product->average_rating : 5); @endphp
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= $avgRounded ? '-fill' : '' }}"></i>
                    @endfor
                </div>
                <div class="text-xs text-muted">({{ $product->review_count }} {{ Str::plural('review', $product->review_count) }})</div>
            </div>
        </div>

        <!-- Verified Buyer Review Form -->
        @if(isset($canReview) && $canReview && isset($unreviewedOrder))
            <div class="card p-4 rounded-4 bg-light border mb-4">
                <h5 class="heading-serif fw-bold text-dark mb-1">
                    <i class="bi bi-patch-check-fill text-success me-1"></i> Write a Verified Review
                </h5>
                <p class="text-muted small mb-3">You received this product in Order <strong>#{{ $unreviewedOrder->order_no }}</strong>. Share your genuine taste and quality experience.</p>

                <form action="{{ route('products.reviews.store', $product) }}" method="POST">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $unreviewedOrder->id }}">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Your Rating <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3 align-items-center flex-wrap">
                            @for($s = 5; $s >= 1; $s--)
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="radio" name="rating" id="star_{{ $s }}" value="{{ $s }}" {{ $s === 5 ? 'checked' : '' }} required>
                                    <label class="form-check-label text-warning small fw-bold cursor-pointer" for="star_{{ $s }}">
                                        {{ $s }} <i class="bi bi-star-fill"></i>
                                    </label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Comments (Optional)</label>
                        <textarea name="comment" class="form-control bg-white" rows="3" placeholder="Describe the freshness, rich taste, aroma, and packaging of this batch..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-darkgreen rounded-pill px-4">
                        <i class="bi bi-send me-1"></i> Post Verified Review
                    </button>
                </form>
            </div>
        @elseif(!auth()->check())
            <div class="alert alert-light border rounded-4 small text-muted mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <i class="bi bi-info-circle me-1 text-success"></i> Have you purchased and received this product?
                </div>
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-darkgreen rounded-pill px-3">Sign In to Review</a>
            </div>
        @endif

        <div class="row g-4">
            @forelse($product->reviews as $review)
                <div class="col-md-6">
                    <div class="p-3 rounded-4 bg-light border h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle overflow-hidden border flex-shrink-0" style="width: 32px; height: 32px; background-color: var(--od-primary);">
                                    <img src="{{ $review->user ? $review->user->avatar_url : 'https://ui-avatars.com/api/?name=Buyer&background=2F5D3A&color=fff' }}" alt="{{ $review->user->name ?? 'Buyer' }}" class="w-100 h-100 object-fit-cover">
                                </div>
                                <strong class="text-dark">{{ $review->user->name ?? 'Verified Buyer' }}</strong>
                            </div>
                            <div class="text-warning small">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="text-muted small mb-1">{{ $review->comment ?? 'No written comment provided.' }}</p>
                        <small class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-check-circle-fill text-success me-1"></i> Verified Purchase &bull; {{ $review->created_at->diffForHumans() }}</small>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-chat-square-text fs-2 d-block mb-2 text-muted"></i>
                        <p class="small mb-0">No reviews yet for this harvest batch. Be the first to try it and share your feedback!</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->isNotEmpty())
        <div class="mb-4">
            <h4 class="heading-serif mb-3">You May Also Like</h4>
            <div class="row g-4">
                @foreach($relatedProducts as $rel)
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100 p-3 shadow-sm d-flex flex-column justify-content-between">
                            <div>
                                <div class="rounded-3 mb-2 d-flex align-items-center justify-content-center" style="height: 150px; background: #F5F1E8;">
                                    <i class="bi bi-egg-fried fs-2 text-success"></i>
                                </div>
                                <h6 class="heading-serif mb-1">
                                    <a href="{{ route('shop.show', $rel->slug) }}" class="text-dark text-decoration-none hover-gold">{{ $rel->name }}</a>
                                </h6>
                                <small class="text-muted d-block mb-2">{{ $rel->category->name }}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <strong class="text-dark">{{ $rel->formatted_price }}</strong>
                                <a href="{{ route('shop.show', $rel->slug) }}" class="btn btn-sm btn-outline-darkgreen">View</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Vanilla JS for Variant & Stepper Selection -->
<script>
    let currentStock = {{ $product->stock }};

    function selectVariant(button) {
        // Highlight active variant chip
        const chips = document.querySelectorAll('.variant-chip');
        chips.forEach(chip => {
            chip.classList.remove('btn-primary');
            chip.classList.add('btn-outline-darkgreen');
        });

        button.classList.remove('btn-outline-darkgreen');
        button.classList.add('btn-primary');

        // Extract dataset attributes
        const variantId = button.dataset.variantId || '';
        const formattedPrice = button.dataset.formattedPrice;
        const unit = button.dataset.unit;
        const stock = parseInt(button.dataset.stock) || 0;
        currentStock = stock;

        // Update UI displays
        document.getElementById('selectedVariantId').value = variantId;
        document.getElementById('productPriceDisplay').innerText = formattedPrice;
        document.getElementById('productUnitDisplay').innerText = 'per ' + unit;

        const stockBadge = document.getElementById('productStockBadge');
        if (stock <= 0) {
            stockBadge.className = 'badge badge-status-new ms-auto';
            stockBadge.innerText = 'Out of Stock';
        } else if (stock <= 10) {
            stockBadge.className = 'badge badge-status-low_stock ms-auto';
            stockBadge.innerText = 'Low Stock (' + stock + ' units)';
        } else {
            stockBadge.className = 'badge badge-status-ok ms-auto';
            stockBadge.innerText = 'In Stock (' + stock + ' units)';
        }

        // Reset and cap quantity input
        const qtyInput = document.getElementById('quantityInput');
        qtyInput.max = Math.max(1, stock);
        if (parseInt(qtyInput.value) > stock && stock > 0) {
            qtyInput.value = stock;
        }
    }

    function incrementQty() {
        const input = document.getElementById('quantityInput');
        let val = parseInt(input.value) || 1;
        if (val < currentStock) {
            input.value = val + 1;
        }
    }

    function decrementQty() {
        const input = document.getElementById('quantityInput');
        let val = parseInt(input.value) || 1;
        if (val > 1) {
            input.value = val - 1;
        }
    }

    async function handleProductDetailAddToCart(button, redirectToCart = false) {
        const productId = document.getElementById('selectedProductId').value;
        const variantId = document.getElementById('selectedVariantId').value || null;
        const quantity = parseInt(document.getElementById('quantityInput').value) || 1;

        await window.addToCart(productId, variantId, quantity, button);

        if (redirectToCart) {
            window.location.href = '{{ route('cart.index') }}';
        }
    }
</script>
@endsection
