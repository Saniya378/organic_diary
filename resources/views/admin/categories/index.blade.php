@extends('layouts.panel')

@section('title', 'Categories Management - Admin Center')
@section('page_heading', 'Product Categories')

@section('menu_items')
    @include('admin.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Header Actions -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="font-serif fw-bold text-dark mb-1">Catalog Categories</h5>
                <p class="text-muted small mb-0">Organize and control the dairy product taxonomy for the marketplace.</p>
            </div>
            <button type="button" class="btn btn-darkgreen rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="bi bi-plus-circle me-1"></i> Add New Category
            </button>
        </div>
    </div>

    <!-- Categories List Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        @if($categories->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-tags fs-1 text-gold d-block mb-3"></i>
                <h5 class="font-serif fw-bold text-dark">No Categories Defined</h5>
                <p class="small mb-0">Click "Add New Category" to create your first category.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Category Name</th>
                            <th>Slug (URL Key)</th>
                            <th>Products Count</th>
                            <th>Default Commission</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; min-width: 40px;">
                                            <i class="bi bi-tag-fill"></i>
                                        </div>
                                        <div class="fw-bold text-dark">{{ $category->name }}</div>
                                    </div>
                                </td>
                                <td>
                                    <code class="text-muted bg-light px-2 py-1 rounded border">{{ $category->slug }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">
                                        {{ $category->products_count }} Products
                                    </span>
                                </td>
                                <td>
                                    @if($category->commission_rate !== null)
                                        <span class="badge bg-gold text-dark rounded-pill px-2.5 py-1 text-xs fw-bold">
                                            {{ $category->commission_rate }}%
                                        </span>
                                    @else
                                        <span class="text-muted small">Global Default</span>
                                    @endif
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-xs">
                                            <i class="bi bi-check-circle-fill me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 text-xs">
                                            <i class="bi bi-dash-circle me-1"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-sm text-dark">{{ $category->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-darkgreen rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editModal-{{ $category->id }}">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>

                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" {{ $category->products_count > 0 ? 'disabled title="Cannot delete category with products"' : '' }}>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Edit Category Modal -->
                                    <div class="modal fade text-start" id="editModal-{{ $category->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title font-serif fw-bold text-dark">Edit Category: {{ $category->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body py-3">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold text-sm">Category Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold text-sm">Custom Slug (Optional)</label>
                                                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}" placeholder="e.g. fresh-cow-milk">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold text-sm">Default Category Commission Rate (%)</label>
                                                            <div class="input-group">
                                                                <input type="number" step="0.1" name="commission_rate" class="form-control" value="{{ old('commission_rate', $category->commission_rate) }}" placeholder="e.g. 5.0 (leave blank for platform default)" min="0" max="100">
                                                                <span class="input-group-text">%</span>
                                                            </div>
                                                            <div class="form-text small text-muted">Applied to all products in this category unless product has its own custom rate.</div>
                                                        </div>
                                                        <div class="form-check form-switch mb-0">
                                                            <input class="form-check-input" type="checkbox" role="switch" id="activeEditSwitch-{{ $category->id }}" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label text-sm fw-semibold" for="activeEditSwitch-{{ $category->id }}">Active in Marketplace</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-darkgreen rounded-pill px-4">Update Category</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Create Category Modal -->
    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title font-serif fw-bold text-dark">Create New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-sm">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Artisanal Cheese, Clarified Ghee" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-sm">Slug (Optional - auto generated if empty)</label>
                            <input type="text" name="slug" class="form-control" placeholder="e.g. artisanal-cheese">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-sm">Default Category Commission Rate (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.1" name="commission_rate" class="form-control" placeholder="e.g. 5.0 (leave blank for platform default)" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text small text-muted">Applied to products in this category unless product has its own custom rate.</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="activeCreateSwitch" name="is_active" value="1" checked>
                            <label class="form-check-label text-sm fw-semibold" for="activeCreateSwitch">Active in Marketplace</label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darkgreen rounded-pill px-4">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
