@extends('layouts.panel')

@section('title', 'My Products - Farmer Portal')
@section('page_heading', 'My Dairy Products')

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    @if(!$isApprovedSeller)
        <div class="alert alert-warning rounded-4 p-4 border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-shield-exclamation fs-2 text-warning"></i>
                <div>
                    <h5 class="fw-bold mb-1">Seller Verification Pending</h5>
                    <p class="small mb-0">Your farm account is currently pending administrator verification. Adding new products or editing existing catalog items is temporarily disabled until your farm profile is approved.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Header Actions and Filters -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <!-- Filter Pills -->
            <ul class="nav nav-pills gap-1">
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ !$currentStatus ? 'active bg-primary' : 'text-dark' }}" href="{{ route('seller.products.index') }}">
                        All ({{ $counts['all'] ?? Auth::user()->products()->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'approved' ? 'active bg-success' : 'text-dark' }}" href="{{ route('seller.products.index', ['status' => 'approved']) }}">
                        Live / Approved ({{ $counts['approved'] ?? Auth::user()->products()->where('status', 'approved')->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'pending' ? 'active bg-warning text-dark' : 'text-dark' }}" href="{{ route('seller.products.index', ['status' => 'pending']) }}">
                        Pending Review ({{ $counts['pending'] ?? Auth::user()->products()->where('status', 'pending')->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'inactive' ? 'active bg-secondary' : 'text-dark' }}" href="{{ route('seller.products.index', ['status' => 'inactive']) }}">
                        Inactive ({{ $counts['inactive'] ?? Auth::user()->products()->where('status', 'inactive')->count() }})
                    </a>
                </li>
            </ul>

            <!-- Add Product Button -->
            @if($isApprovedSeller)
                <a href="{{ route('seller.products.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle fs-5"></i> Add New Product
                </a>
            @endif
        </div>
    </div>

    <!-- Products Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        @if($products->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-box-seam fs-1 text-gold d-block mb-3"></i>
                <h5 class="font-serif fw-bold text-dark">No Products Found</h5>
                <p class="small mb-3">You haven't listed any products matching this status filter yet.</p>
                @if($isApprovedSeller)
                    <a href="{{ route('seller.products.create') }}" class="btn btn-primary rounded-pill px-4 btn-sm">
                        Create Your First Product
                    </a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Product</th>
                            <th>Category</th>
                            <th>Base Price</th>
                            <th>Stock</th>
                            <th>Variants</th>
                            <th>Status</th>
                            <th>Deals (Subscriptions)</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            @php
                                $statusBadges = [
                                    'approved' => 'bg-success-subtle text-success border border-success-subtle',
                                    'pending' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    'inactive' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                ];
                                $badgeClass = $statusBadges[$product->status] ?? 'bg-light text-dark';
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 overflow-hidden bg-light border d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; min-width: 50px;">
                                            @if($product->images->isNotEmpty())
                                                <img src="{{ Storage::url($product->images->first()->path) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <i class="bi bi-image text-muted fs-4"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('shop.show', $product->slug) }}" target="_blank" class="fw-bold text-dark text-decoration-none hover-gold d-block">
                                                {{ $product->name }}
                                            </a>
                                            <div class="small text-muted">
                                                {{ $product->unit }} &bull; 
                                                @if($product->is_organic)
                                                    <span class="text-success"><i class="bi bi-patch-check-fill text-gold"></i> Organic</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                                </td>
                                <td class="fw-bold text-dark-green font-serif">
                                    Rs {{ number_format($product->price) }}
                                </td>
                                <td>
                                    @if($product->stock <= 10)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold">
                                            {{ $product->stock }} (Low)
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            {{ $product->stock }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->variants->isNotEmpty())
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            {{ $product->variants->count() }} Variants
                                        </span>
                                    @else
                                        <span class="text-muted small">Standard</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }} px-2 py-1 rounded-pill small">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($isApprovedSeller)
                                        <form action="{{ route('seller.products.toggle-deals', $product->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            @if($product->deals_enabled)
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 text-xs fw-bold" title="Deals are active. Click to disable.">
                                                    <i class="bi bi-check-circle-fill text-success me-1"></i> Deals ON
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1 text-xs" title="No deals. Click to enable.">
                                                    <i class="bi bi-x-circle text-muted me-1"></i> Deals OFF
                                                </button>
                                            @endif
                                        </form>
                                    @else
                                        <span class="text-muted small">--</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        @if($isApprovedSeller)
                                            <a href="{{ route('seller.products.edit', $product->id) }}" class="btn btn-sm btn-outline-darkgreen rounded-pill px-3" title="Edit Product">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </a>
                                            <form action="{{ route('seller.products.toggle-active', $product->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-light border rounded-pill px-2 text-muted" title="{{ $product->status === 'inactive' ? 'Activate' : 'Deactivate' }}">
                                                    <i class="bi {{ $product->status === 'inactive' ? 'bi-toggle-off' : 'bi-toggle-on text-success' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('seller.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Locked</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-3 border-top d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
