@extends('layouts.public')

@section('title', '404 - Page Not Found | Organic Dairy')

@section('content')
<div class="container py-5 my-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-5 bg-white position-relative overflow-hidden">
                <div class="mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-darkgreen bg-success-subtle p-4 mb-2" style="width: 100px; height: 100px;">
                        <i class="bi bi-compass fs-1 text-success"></i>
                    </div>
                    <h1 class="display-3 font-serif fw-bold text-darkgreen mb-1">404</h1>
                    <h3 class="font-serif fw-bold text-dark mb-2">Pasture Not Found</h3>
                    <p class="text-muted leading-relaxed">
                        The mountain dairy batch or page you are looking for might have been moved, seasonal stock has ended, or the URL address was mistyped.
                    </p>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="{{ url('/') }}" class="btn btn-darkgreen btn-lg rounded-pill px-4 fw-bold">
                        <i class="bi bi-house-door me-1"></i> Return Home
                    </a>
                    <a href="{{ route('shop.index') }}" class="btn btn-outline-darkgreen btn-lg rounded-pill px-4 fw-bold">
                        <i class="bi bi-bag-check me-1"></i> Browse Fresh Shop
                    </a>
                </div>

                <div class="mt-4 pt-4 border-top">
                    <small class="text-muted">
                        Looking for a parcel? <a href="{{ route('track-order.form') }}" class="text-darkgreen fw-semibold text-decoration-none hover-gold">Track an Order &rarr;</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
