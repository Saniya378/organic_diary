@extends('layouts.panel')

@section('title', 'Edit Product - Farmer Portal')
@section('page_heading', 'Edit Dairy Product: ' . $product->name)

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('seller.products.index') }}" class="text-decoration-none">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit #{{ $product->id }}</li>
        </ol>
    </nav>

    <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
            <div>
                <strong>Re-verification Notice:</strong> Saving changes to this product will set its status to <em>Pending</em> until verified by the marketplace team.
            </div>
        </div>
    </div>

    <form action="{{ route('seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column: Core Details -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="font-serif fw-bold text-dark-green mb-3">Product Information</h5>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Product Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" required>{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="price" class="form-label fw-semibold">Base Price (PKR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rs</span>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" min="1" value="{{ old('price', $product->price) }}" required>
                            </div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="unit" class="form-label fw-semibold">Standard Unit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit', $product->unit) }}" required>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="stock" class="form-label fw-semibold">Available Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" min="0" value="{{ old('stock', $product->stock) }}" required>
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pack-Size Variants -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="font-serif fw-bold text-dark-green mb-0">Pack-Size Variants</h5>
                            <small class="text-muted">Manage different packaging sizes and custom pricing</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-darkgreen rounded-pill px-3" onclick="addVariantRow()">
                            <i class="bi bi-plus-lg me-1"></i> Add Variant
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle border-light" id="variantsTable">
                            <thead class="bg-light">
                                <tr class="text-muted small">
                                    <th>Pack Label</th>
                                    <th style="width: 160px;">Price (Rs)</th>
                                    <th style="width: 140px;">Stock</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="variantsBody">
                                <!-- Existing variants loaded via script -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Farm Specs & Images -->
            <div class="col-lg-4">
                
                <!-- Batch & Certification Specs -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="font-serif fw-bold text-dark-green mb-3">Farm Traceability</h5>

                    <div class="mb-3">
                        <label for="batch_no" class="form-label fw-semibold small">Harvest Batch No</label>
                        <input type="text" class="form-control form-control-sm @error('batch_no') is-invalid @enderror" id="batch_no" name="batch_no" value="{{ old('batch_no', $product->batch_no) }}">
                        @error('batch_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="produced_on" class="form-label fw-semibold small">Date of Milking / Production</label>
                        <input type="date" class="form-control form-control-sm @error('produced_on') is-invalid @enderror" id="produced_on" name="produced_on" value="{{ old('produced_on', optional($product->produced_on)->format('Y-m-d')) }}">
                        @error('produced_on')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="is_organic" name="is_organic" value="1" {{ old('is_organic', $product->is_organic) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark" for="is_organic">
                            100% Certified Organic Feed
                        </label>
                    </div>

                    <div class="form-check form-switch mt-3 pt-3 border-top">
                        <input class="form-check-input" type="checkbox" id="deals_enabled" name="deals_enabled" value="1" {{ old('deals_enabled', $product->deals_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark" for="deals_enabled">
                            <i class="bi bi-calendar2-heart text-gold me-1"></i> Enable Subscription Deals (3 Days, 1 Week, 1 Month)
                        </label>
                        <small class="d-block text-muted">Allow buyers to subscribe for daily doorstep deliveries for this product.</small>
                    </div>
                </div>

                <!-- Product Images & Upload -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="font-serif fw-bold text-dark-green mb-1">Product Images</h5>
                    <small class="text-muted d-block mb-3">Current & new photos (up to 5 total)</small>

                    @if($product->images->isNotEmpty())
                        <div class="row g-2 mb-3">
                            @foreach($product->images as $img)
                                <div class="col-4 position-relative" id="existing-img-{{ $img->id }}">
                                    <div class="border rounded-3 overflow-hidden position-relative bg-light" style="height: 85px;">
                                        <img src="{{ Storage::url($img->path) }}" class="w-100 h-100 object-fit-cover">
                                        @if($loop->first)
                                            <span class="badge bg-dark-green position-absolute top-0 start-0 m-1 px-1.5 py-0.5 text-xs">Primary</span>
                                        @endif
                                        <button type="button" class="btn btn-danger btn-sm p-0 rounded-circle position-absolute top-0 end-0 m-1 d-flex align-items-center justify-content-center shadow" style="width: 24px; height: 24px;" title="Delete Photo" onclick="deleteProductImage({{ $product->id }}, {{ $img->id }})">
                                            <i class="bi bi-x fs-6"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <label class="form-label small fw-semibold">Add More Photos (JPG, PNG, WebP, max 2MB)</label>
                    <input type="file" name="images[]" id="imageInput" class="form-control form-control-sm @error('images.*') is-invalid @enderror" multiple accept="image/jpeg,image/png,image/webp" onchange="previewImages(this)">
                    @error('images') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    @error('images.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                    <div id="imagePreviewGrid" class="row g-2 mt-2"></div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Update Product & Submit
                    </button>
                    <a href="{{ route('seller.products.index') }}" class="btn btn-light rounded-pill py-2 text-muted">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let variantIndex = 0;

    function addVariantRow(label = '', price = '', stock = '') {
        const tbody = document.getElementById('variantsBody');
        const tr = document.createElement('tr');
        tr.id = `variant_row_${variantIndex}`;
        tr.innerHTML = `
            <td>
                <input type="text" name="variants[${variantIndex}][label]" class="form-control form-control-sm" placeholder="e.g. 500 g Tub" value="${label}" required>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light">Rs</span>
                    <input type="number" name="variants[${variantIndex}][price]" class="form-control" placeholder="1200" min="1" value="${price}" required>
                </div>
            </td>
            <td>
                <input type="number" name="variants[${variantIndex}][stock]" class="form-control form-control-sm" placeholder="15" min="0" value="${stock}" required>
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeVariantRow(${variantIndex})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        variantIndex++;
    }

    function removeVariantRow(index) {
        const row = document.getElementById(`variant_row_${index}`);
        if (row) row.remove();
    }

    function previewImages(input) {
        const grid = document.getElementById('imagePreviewGrid');
        grid.innerHTML = '';

        if (input.files && input.files.length > 0) {
            const maxFiles = Math.min(input.files.length, 5);
            for (let i = 0; i < maxFiles; i++) {
                const file = input.files[i];
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-4';
                    col.innerHTML = `
                        <div class="border rounded-3 overflow-hidden position-relative bg-light" style="height: 70px;">
                            <img src="${e.target.result}" class="w-100 h-100 object-fit-cover">
                        </div>
                    `;
                    grid.appendChild(col);
                };
                reader.readAsDataURL(file);
            }
        }
    }

    // Populate existing variants
    document.addEventListener('DOMContentLoaded', () => {
        const existingVariants = {!! json_encode($product->variants) !!};
        if (existingVariants && existingVariants.length > 0) {
            existingVariants.forEach(v => {
                addVariantRow(v.label, v.price, v.stock);
            });
        }
    });

    function deleteProductImage(productId, imageId) {
        if (confirm('Are you sure you want to remove this product image?')) {
            const form = document.getElementById('deleteProductImageForm');
            form.action = `/seller/products/${productId}/images/${imageId}`;
            form.submit();
        }
    }
</script>

<form id="deleteProductImageForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endpush
@endsection
