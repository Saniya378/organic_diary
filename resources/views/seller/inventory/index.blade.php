@extends('layouts.panel')

@section('title', 'Live Inventory & Low Stock Alerts - Farmer Portal')
@section('page_heading', 'Live Farm Inventory & Low Stock Controls')

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    @php
        $lowStockCount = 0;
        $outOfStockCount = 0;
        foreach($products as $p) {
            if ($p->stock <= 0) {
                $outOfStockCount++;
            } elseif ($p->stock <= $lowStockThreshold) {
                $lowStockCount++;
            }
            foreach($p->variants as $v) {
                if ($v->stock <= 0) {
                    $outOfStockCount++;
                } elseif ($v->stock <= $lowStockThreshold) {
                    $lowStockCount++;
                }
            }
        }
    @endphp

    <!-- Top Summary Banner -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="font-serif fw-bold text-dark-green mb-1">
                    <i class="bi bi-stack me-1.5 text-gold"></i> Live Stock &amp; Alert Manager
                </h5>
                <p class="text-muted small mb-0">
                    Real-time stock controls. Automatic alerts are dispatched when stock drops to or below <strong>{{ $lowStockThreshold }} units</strong>.
                </p>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                @if($outOfStockCount > 0)
                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill">
                        <i class="bi bi-x-octagon-fill me-1"></i> {{ $outOfStockCount }} Sold Out
                    </span>
                @endif
                @if($lowStockCount > 0)
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $lowStockCount }} Low Stock (&le; {{ $lowStockThreshold }})
                    </span>
                @endif
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                    Alert Threshold: &le; {{ $lowStockThreshold }} Units
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Filter Tabs & Search -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="btn-group rounded-pill overflow-hidden shadow-xs border bg-white p-1" role="group">
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold" id="filterAllBtn" onclick="filterInventory('all')">
                All Products ({{ $products->count() }})
            </button>
            <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold text-warning-emphasis position-relative" id="filterLowBtn" onclick="filterInventory('low')">
                ⚠️ Low Stock Only
                @if($lowStockCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill ms-1">{{ $lowStockCount }}</span>
                @endif
            </button>
            <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold text-danger position-relative" id="filterOutBtn" onclick="filterInventory('out')">
                🚨 Sold Out (0)
                @if($outOfStockCount > 0)
                    <span class="badge bg-danger text-white rounded-pill ms-1">{{ $outOfStockCount }}</span>
                @endif
            </button>
        </div>

        <div style="min-width: 240px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" id="inventorySearchInput" class="form-control border-start-0" placeholder="Filter items by name or batch..." onkeyup="searchInventoryTable()">
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        @if($products->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-stack fs-1 text-gold d-block mb-3"></i>
                <h5 class="font-serif fw-bold text-dark">No Products in Inventory</h5>
                <p class="small mb-3">Add products to your catalog to track and manage live batches.</p>
                <a href="{{ route('seller.products.create') }}" class="btn btn-primary rounded-pill px-4 btn-sm">
                    Add First Product
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="inventoryTable">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Item / Variant</th>
                            <th>Category</th>
                            <th>Batch No</th>
                            <th>Unit</th>
                            <th>Price</th>
                            <th style="min-width: 260px;">Live Stock &amp; Quick Adjust</th>
                            <th class="text-end pe-4">Stock Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            @php
                                $stockType = $product->stock <= 0 ? 'out' : ($product->stock <= $lowStockThreshold ? 'low' : 'ok');
                            @endphp

                            <!-- Main Product Row -->
                            <tr id="row_product_{{ $product->id }}" class="inventory-row {{ $stockType === 'out' ? 'table-danger' : ($stockType === 'low' ? 'table-warning bg-opacity-25' : '') }}" data-stock-type="{{ $stockType }}" data-item-name="{{ strtolower($product->name . ' ' . ($product->batch_no ?? '')) }}">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="fw-bold text-dark">{{ $product->name }}</div>
                                        @if($product->variants->isNotEmpty())
                                            <span class="badge bg-light text-muted border small">Base</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">Slug: {{ $product->slug }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                                </td>
                                <td class="small font-monospace text-secondary">
                                    {{ $product->batch_no ?? '—' }}
                                </td>
                                <td class="small text-muted">
                                    {{ $product->unit }}
                                </td>
                                <td class="fw-bold text-dark-green font-serif">
                                    Rs {{ number_format($product->price) }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <div class="input-group input-group-sm" style="width: 130px;">
                                            <input type="number" class="form-control text-center fw-bold font-monospace {{ $stockType !== 'ok' ? 'border-danger text-danger' : '' }}" id="input_product_{{ $product->id }}" value="{{ $product->stock }}" min="0">
                                            <button class="btn btn-primary btn-sm px-2" type="button" title="Save Stock" onclick="saveProductStock({{ $product->id }})">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        </div>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary px-1.5 py-0 text-xs" title="Add 5 units" onclick="quickAddProductStock({{ $product->id }}, 5)">+5</button>
                                            <button type="button" class="btn btn-outline-secondary px-1.5 py-0 text-xs" title="Add 10 units" onclick="quickAddProductStock({{ $product->id }}, 10)">+10</button>
                                            <button type="button" class="btn btn-outline-secondary px-1.5 py-0 text-xs" title="Add 25 units" onclick="quickAddProductStock({{ $product->id }}, 25)">+25</button>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end pe-4" id="badge_product_{{ $product->id }}">
                                    @if($stockType === 'out')
                                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-1">🚨 Out of Stock</span>
                                    @elseif($stockType === 'low')
                                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1">⚠️ Low Stock ({{ $product->stock }})</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">In Stock ({{ $product->stock }})</span>
                                    @endif
                                </td>
                            </tr>

                            <!-- Sub-variants Rows if any -->
                            @foreach($product->variants as $variant)
                                @php
                                    $variantStockType = $variant->stock <= 0 ? 'out' : ($variant->stock <= $lowStockThreshold ? 'low' : 'ok');
                                @endphp
                                <tr id="row_variant_{{ $variant->id }}" class="inventory-row bg-light bg-opacity-50 {{ $variantStockType === 'out' ? 'table-danger' : ($variantStockType === 'low' ? 'table-warning bg-opacity-25' : '') }}" data-stock-type="{{ $variantStockType }}" data-item-name="{{ strtolower($product->name . ' ' . $variant->label . ' ' . ($product->batch_no ?? '')) }}">
                                    <td class="ps-5">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-arrow-return-right text-muted"></i>
                                            <span class="fw-semibold text-dark">{{ $variant->label }}</span>
                                            <span class="badge bg-primary-subtle text-primary small">Pack</span>
                                        </div>
                                    </td>
                                    <td><span class="text-muted small">—</span></td>
                                    <td class="small font-monospace text-secondary">{{ $product->batch_no ?? '—' }}</td>
                                    <td class="small text-muted">{{ $variant->label }}</td>
                                    <td class="fw-bold text-dark font-serif">Rs {{ number_format($variant->price) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            <div class="input-group input-group-sm" style="width: 130px;">
                                                <input type="number" class="form-control text-center fw-bold font-monospace {{ $variantStockType !== 'ok' ? 'border-danger text-danger' : '' }}" id="input_variant_{{ $variant->id }}" value="{{ $variant->stock }}" min="0">
                                                <button class="btn btn-primary btn-sm px-2" type="button" title="Save Stock" onclick="saveVariantStock({{ $variant->id }})">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                            </div>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-secondary px-1.5 py-0 text-xs" title="Add 5 units" onclick="quickAddVariantStock({{ $variant->id }}, 5)">+5</button>
                                                <button type="button" class="btn btn-outline-secondary px-1.5 py-0 text-xs" title="Add 10 units" onclick="quickAddVariantStock({{ $variant->id }}, 10)">+10</button>
                                                <button type="button" class="btn btn-outline-secondary px-1.5 py-0 text-xs" title="Add 25 units" onclick="quickAddVariantStock({{ $variant->id }}, 25)">+25</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4" id="badge_variant_{{ $variant->id }}">
                                        @if($variantStockType === 'out')
                                            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1">🚨 Out of Stock</span>
                                        @elseif($variantStockType === 'low')
                                            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1">⚠️ Low Stock ({{ $variant->stock }})</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">In Stock ({{ $variant->stock }})</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
    async function saveProductStock(productId) {
        const input = document.getElementById(`input_product_${productId}`);
        const newStock = parseInt(input.value);

        if (isNaN(newStock) || newStock < 0) {
            alert('Please enter a valid non-negative stock quantity.');
            return;
        }

        try {
            const response = await fetch(`/seller/inventory/${productId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ stock: newStock })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                const row = document.getElementById(`row_product_${productId}`);
                const badgeTd = document.getElementById(`badge_product_${productId}`);

                if (newStock <= 0) {
                    row.className = 'inventory-row table-danger';
                    row.dataset.stockType = 'out';
                    input.classList.add('border-danger', 'text-danger');
                    badgeTd.innerHTML = '<span class="badge bg-danger text-white rounded-pill px-2.5 py-1">🚨 Out of Stock</span>';
                } else if (data.is_low_stock) {
                    row.className = 'inventory-row table-warning bg-opacity-25';
                    row.dataset.stockType = 'low';
                    input.classList.add('border-danger', 'text-danger');
                    badgeTd.innerHTML = `<span class="badge bg-warning text-dark rounded-pill px-2.5 py-1">⚠️ Low Stock (${newStock})</span>`;
                } else {
                    row.className = 'inventory-row';
                    row.dataset.stockType = 'ok';
                    input.classList.remove('border-danger', 'text-danger');
                    badgeTd.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">In Stock (${newStock})</span>`;
                }

                if (window.showToast) {
                    window.showToast(data.message, true);
                }
            } else {
                alert(data.message || 'Error updating stock.');
            }
        } catch (e) {
            console.error('Error saving stock:', e);
            alert('An unexpected network error occurred.');
        }
    }

    async function saveVariantStock(variantId) {
        const input = document.getElementById(`input_variant_${variantId}`);
        const newStock = parseInt(input.value);

        if (isNaN(newStock) || newStock < 0) {
            alert('Please enter a valid non-negative stock quantity.');
            return;
        }

        try {
            const response = await fetch(`/seller/inventory/variant/${variantId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ stock: newStock })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                const row = document.getElementById(`row_variant_${variantId}`);
                const badgeTd = document.getElementById(`badge_variant_${variantId}`);

                if (newStock <= 0) {
                    row.className = 'inventory-row bg-light bg-opacity-50 table-danger';
                    row.dataset.stockType = 'out';
                    input.classList.add('border-danger', 'text-danger');
                    badgeTd.innerHTML = '<span class="badge bg-danger text-white rounded-pill px-2.5 py-1">🚨 Out of Stock</span>';
                } else if (data.is_low_stock) {
                    row.className = 'inventory-row bg-light bg-opacity-50 table-warning bg-opacity-25';
                    row.dataset.stockType = 'low';
                    input.classList.add('border-danger', 'text-danger');
                    badgeTd.innerHTML = `<span class="badge bg-warning text-dark rounded-pill px-2.5 py-1">⚠️ Low Stock (${newStock})</span>`;
                } else {
                    row.className = 'inventory-row bg-light bg-opacity-50';
                    row.dataset.stockType = 'ok';
                    input.classList.remove('border-danger', 'text-danger');
                    badgeTd.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">In Stock (${newStock})</span>`;
                }

                if (window.showToast) {
                    window.showToast(data.message, true);
                }
            } else {
                alert(data.message || 'Error updating variant stock.');
            }
        } catch (e) {
            console.error('Error saving variant stock:', e);
            alert('An unexpected network error occurred.');
        }
    }

    function quickAddProductStock(productId, increment) {
        const input = document.getElementById(`input_product_${productId}`);
        const current = parseInt(input.value) || 0;
        input.value = current + increment;
        saveProductStock(productId);
    }

    function quickAddVariantStock(variantId, increment) {
        const input = document.getElementById(`input_variant_${variantId}`);
        const current = parseInt(input.value) || 0;
        input.value = current + increment;
        saveVariantStock(variantId);
    }

    function filterInventory(type) {
        document.querySelectorAll('.inventory-row').forEach(row => {
            const rowType = row.dataset.stockType;
            if (type === 'all') {
                row.style.display = '';
            } else if (type === 'low') {
                row.style.display = (rowType === 'low' || rowType === 'out') ? '' : 'none';
            } else if (type === 'out') {
                row.style.display = (rowType === 'out') ? '' : 'none';
            }
        });

        document.getElementById('filterAllBtn').className = type === 'all' ? 'btn btn-sm btn-primary rounded-pill px-3 fw-semibold' : 'btn btn-sm btn-light rounded-pill px-3 fw-semibold';
        document.getElementById('filterLowBtn').className = type === 'low' ? 'btn btn-sm btn-warning rounded-pill px-3 fw-semibold text-dark' : 'btn btn-sm btn-light rounded-pill px-3 fw-semibold text-warning-emphasis';
        document.getElementById('filterOutBtn').className = type === 'out' ? 'btn btn-sm btn-danger rounded-pill px-3 fw-semibold text-white' : 'btn btn-sm btn-light rounded-pill px-3 fw-semibold text-danger';
    }

    function searchInventoryTable() {
        const query = document.getElementById('inventorySearchInput').value.toLowerCase().trim();
        document.querySelectorAll('.inventory-row').forEach(row => {
            const name = row.dataset.itemName || '';
            if (name.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
