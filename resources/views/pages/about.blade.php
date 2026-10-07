@extends('layouts.public')

@section('title', 'About Organic Dairy Gilgit-Baltistan')

@section('content')
<div class="container py-5">
    <div class="row g-5 align-items-center mb-5">
        <div class="col-lg-6">
            <span class="badge badge-status-ok mb-2">High Altitude Alpine Dairy</span>
            <h2 class="display-6 heading-serif mb-3">Pure Himalayan Heritage in Every Drop</h2>
            <p class="text-muted leading-relaxed mb-3">
                Organic Dairy is a community-driven marketplace built specifically for the unique terrain of Gilgit-Baltistan, Pakistan. We connect smallholder mountain herders from Hunza, Skardu, Ghizer, Astore, Nagar, and Diamer directly with local and regional households.
            </p>
            <p class="text-muted leading-relaxed">
                Our livestock graze naturally on wild mountain thyme, mint, and alpine pastures fed by glacial streams. By keeping supply chains short and cold-managed, we preserve natural nutrients, essential enzymes, and rich aroma.
            </p>
        </div>
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm border-0 bg-white">
                <h4 class="heading-serif mb-3 text-dark">Why Mountain Organic?</h4>
                <div class="d-flex gap-3 mb-3">
                    <i class="bi bi-shield-check fs-2 text-success"></i>
                    <div>
                        <strong>100% Unadulterated</strong>
                        <p class="small text-muted mb-0">Direct from farm milking batches with full traceability.</p>
                    </div>
                </div>
                <div class="d-flex gap-3 mb-3">
                    <i class="bi bi-snow fs-2 text-info"></i>
                    <div>
                        <strong>Glacial Pasture Grazing</strong>
                        <p class="small text-muted mb-0">Animals graze freely above 8,000 feet on pristine wildflower valleys.</p>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <i class="bi bi-heart fs-2 text-warning"></i>
                    <div>
                        <strong>Empowering Mountain Herders</strong>
                        <p class="small text-muted mb-0">Fair prices returned directly to native farming families.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
