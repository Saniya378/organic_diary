@extends('layouts.public')

@section('title', 'Certified Organic Dairy Farmers | Gilgit-Baltistan')
@section('meta_description', 'Meet certified high-altitude organic dairy farmers and shepherds from Hunza, Skardu, Ghizer, Astore, and Nagar.')

@section('content')
<div class="container py-4 py-lg-5">
    <!-- Breadcrumb & Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-success text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Farmers</li>
            </ol>
        </nav>
        <h2 class="heading-serif mb-1">Meet Our Mountain Dairy Farmers</h2>
        <p class="text-muted small mb-0">Direct from traditional family-owned farms and alpine herder cooperatives in Gilgit-Baltistan.</p>
    </div>

    <!-- Farmers Grid -->
    <div class="row g-4 mb-5">
        @forelse($farmers as $farmer)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 d-flex flex-column justify-content-between overflow-hidden rounded-4">
                    <div>
                        <div class="position-relative overflow-hidden" style="height: 140px; background: linear-gradient(135deg, var(--od-dark-green) 0%, #17321e 100%);">
                            @if($farmer->photo_path && (file_exists(public_path('storage/' . $farmer->photo_path)) || file_exists(public_path('images/' . $farmer->photo_path))))
                                @php
                                    $imgSrc = file_exists(public_path('storage/' . $farmer->photo_path)) ? asset('storage/' . $farmer->photo_path) : asset('images/' . $farmer->photo_path);
                                @endphp
                                <img src="{{ $imgSrc }}" alt="{{ $farmer->farm_name }}" class="w-100 h-100 object-fit-cover">
                                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.5) 100%);"></div>
                            @endif
                            <span class="position-absolute top-0 end-0 m-2 badge badge-status-ok shadow-sm">
                                <i class="bi bi-patch-check-fill me-1"></i> Verified Farm
                            </span>
                        </div>

                        <div class="p-4">
                            <h5 class="heading-serif mb-1 fw-bold">
                                <a href="{{ route('farmers.show', $farmer->id) }}" class="text-dark text-decoration-none hover-gold">
                                    {{ $farmer->farm_name }}
                                </a>
                            </h5>
                            <div class="small text-muted mb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $farmer->location }}</div>

                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge badge-status-pending"><i class="bi bi-box-seam me-1"></i> {{ $farmer->approved_products_count }} {{ Str::plural('Product', $farmer->approved_products_count) }}</span>
                            </div>

                            <p class="text-muted small mb-0 leading-relaxed">
                                {{ Str::limit($farmer->description, 120) }}
                            </p>
                        </div>
                    </div>

                    <div class="px-4 pb-4 pt-0 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-warning small"><i class="bi bi-star-fill"></i></span>
                            <strong class="text-dark small ms-1">{{ $farmer->average_rating }}</strong>
                            <small class="text-muted">Rating</small>
                        </div>
                        <a href="{{ route('farmers.show', $farmer->id) }}" class="btn btn-sm btn-primary px-3">
                            Visit Farm <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center shadow-sm">
                    <h5 class="heading-serif mb-2">No Farmers Found</h5>
                    <p class="text-muted small">Farmer profiles are being updated. Check back soon.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center">
        {{ $farmers->links() }}
    </div>
</div>
@endsection
