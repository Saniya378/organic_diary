@extends('layouts.public')

@section('title', $farmer->farm_name . ' - Verified Farm Profile')
@section('meta_description', Str::limit($farmer->description, 150))

@section('content')
<div class="container py-4 py-lg-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-success text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('farmers.index') }}" class="text-success text-decoration-none">Farmers</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $farmer->farm_name }}</li>
        </ol>
    </nav>

    <!-- Farm Banner Header -->
    @php
        $farmBg = ($farmer->photo_path && (file_exists(public_path('storage/' . $farmer->photo_path)) || file_exists(public_path('images/' . $farmer->photo_path))))
            ? (file_exists(public_path('storage/' . $farmer->photo_path)) ? asset('storage/' . $farmer->photo_path) : asset('images/' . $farmer->photo_path))
            : null;
    @endphp
    <div class="card p-4 p-md-5 shadow-sm border-0 mb-5 rounded-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, rgba(31,58,39,0.92) 0%, rgba(23,50,30,0.95) 100%){{ $farmBg ? ', url(\'' . $farmBg . '\') center/cover no-repeat' : '' }}; color: white;">
        <div class="row align-items-center g-4 position-relative">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle overflow-hidden border border-2 border-white shadow flex-shrink-0" style="width: 64px; height: 64px; background-color: var(--od-gold);">
                        <img src="{{ $farmer->user ? $farmer->user->avatar_url : ($farmBg ?: asset('images/logo.png')) }}" alt="{{ $farmer->farm_name }}" class="w-100 h-100 object-fit-cover">
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h2 class="heading-serif text-white mb-0">{{ $farmer->farm_name }}</h2>
                            <span class="badge bg-warning text-dark"><i class="bi bi-patch-check-fill me-1"></i> Verified</span>
                        </div>
                        <small class="text-white-50"><i class="bi bi-geo-alt-fill text-warning me-1"></i> {{ $farmer->location }} &bull; Member since {{ $farmer->created_at->format('M Y') }}</small>
                    </div>
                </div>

                <p class="text-white-50 lead fs-6 mb-3" style="max-width: 650px;">
                    {{ $farmer->description }}
                </p>

                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 rounded-pill px-3 py-2"><i class="bi bi-shield-check text-warning me-1"></i> 100% Organic Feeding</span>
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 rounded-pill px-3 py-2"><i class="bi bi-snow text-warning me-1"></i> Glacial Stream Sourced</span>
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 rounded-pill px-3 py-2"><i class="bi bi-hand-thumbs-up text-warning me-1"></i> Hand Milked</span>
                </div>
            </div>

            <div class="col-md-4 text-md-end">
                <div class="bg-white bg-opacity-10 p-3 rounded-4 border border-white border-opacity-25 text-start d-inline-block w-100" style="max-width: 280px;">
                    <div class="d-flex justify-content-between gap-4 mb-1">
                        <span class="text-white-50 small">Active Products:</span>
                        <strong class="text-white">{{ $farmer->approved_products_count }}</strong>
                    </div>
                    <div class="d-flex justify-content-between gap-4 mb-1">
                        <span class="text-white-50 small">Customer Rating:</span>
                        <strong class="text-warning"><i class="bi bi-star-fill"></i> {{ $farmer->average_rating }} / 5.0</strong>
                    </div>
                    <div class="d-flex justify-content-between gap-4 mb-3">
                        <span class="text-white-50 small">Managed By:</span>
                        <strong class="text-white">{{ $farmer->user->name }}</strong>
                    </div>

                    @php
                        $farmerWaPhone = $farmer->user->phone ?? \App\Models\Setting::get('whatsapp_number', \App\Models\Setting::get('contact_phone', '03555123456'));
                        $cleanFarmerPhone = preg_replace('/[^0-9]/', '', $farmerWaPhone);
                        if (str_starts_with($cleanFarmerPhone, '0')) {
                            $cleanFarmerPhone = '92' . substr($cleanFarmerPhone, 1);
                        } elseif (!str_starts_with($cleanFarmerPhone, '92') && strlen($cleanFarmerPhone) === 10) {
                            $cleanFarmerPhone = '92' . $cleanFarmerPhone;
                        }
                        $farmerMsg = rawurlencode("Hello {$farmer->farm_name}! I found your farm profile on Organic Dairy GB and would like to ask about your dairy harvest.");
                    @endphp
                    <a href="https://wa.me/{{ $cleanFarmerPhone }}?text={{ $farmerMsg }}" target="_blank" rel="noopener noreferrer" class="btn btn-light rounded-pill w-100 btn-sm py-2 fw-bold text-success d-flex align-items-center justify-content-center gap-1.5 shadow-sm">
                        <i class="bi bi-whatsapp fs-5 text-success"></i> Chat with Farmer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Farm Products Section -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="heading-serif mb-1">Dairy Harvest from {{ $farmer->farm_name }}</h3>
                <p class="text-muted small mb-0">Directly from {{ $farmer->location }} pastures.</p>
            </div>
        </div>

        <div class="row g-4">
            @forelse($products as $product)
                <div class="col-sm-6 col-lg-4">
                    <div class="card h-100 p-3 shadow-sm d-flex flex-column justify-content-between">
                        <div>
                            <div class="rounded-3 mb-3 d-flex align-items-center justify-content-center" style="height: 180px; background: linear-gradient(135deg, #F5F1E8 0%, #E8E2D2 100%);">
                                @if($product->primary_image)
                                    <img src="{{ asset('storage/' . $product->primary_image) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover rounded-3">
                                @else
                                    <div class="text-center text-muted">
                                        <div class="rounded-circle d-inline-flex p-3 bg-white shadow-sm mb-1 text-success">
                                            <i class="bi bi-egg-fried fs-2"></i>
                                        </div>
                                        <div class="small fw-semibold text-dark">{{ $product->category->name }}</div>
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge badge-status-ok small">{{ $product->category->name }}</span>
                                <div class="small text-warning d-flex align-items-center gap-1">
                                    <i class="bi bi-star-fill"></i> <strong class="text-dark">{{ $product->average_rating > 0 ? $product->average_rating : '5.0' }}</strong> <span class="text-muted" style="font-size: 0.72rem;">({{ $product->review_count }})</span>
                                </div>
                            </div>

                            <h5 class="heading-serif mb-1">
                                <a href="{{ route('shop.show', $product->slug) }}" class="text-dark text-decoration-none hover-gold">
                                    {{ $product->name }}
                                </a>
                            </h5>
                            <p class="text-muted small mb-3">
                                {{ Str::limit($product->description, 70) }}
                            </p>
                        </div>

                        <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                            <div>
                                <span class="d-block heading-serif fs-5 fw-bold text-dark">{{ $product->formatted_price }}</span>
                                <small class="text-muted">per {{ $product->unit }}</small>
                            </div>
                            <a href="{{ route('shop.show', $product->slug) }}" class="btn btn-sm btn-primary px-3">
                                View Product
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card p-5 text-center shadow-sm">
                        <h5 class="heading-serif">No products currently available from this farm.</h5>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Farm Reviews -->
    @if($reviews->isNotEmpty())
        <div class="card p-4 p-md-5 shadow-sm border-0">
            <h4 class="heading-serif mb-3">Customer Feedback for this Farm</h4>
            <div class="row g-3">
                @foreach($reviews as $rev)
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong>{{ $rev->user->name ?? 'Verified Customer' }}</strong>
                                <span class="text-warning small">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $rev->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </span>
                            </div>
                            <small class="text-muted d-block mb-2">Reviewed <strong>{{ $rev->product->name }}</strong></small>
                            <p class="small text-muted mb-0">{{ $rev->comment }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
