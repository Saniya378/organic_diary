@extends('layouts.panel')

@section('title', 'Farm Profile Settings - Farmer Portal')
@section('page_heading', 'Farm Profile & Verification Settings')

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Farm Profile</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left: Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="font-serif fw-bold text-dark-green mb-0">Himalayan Farm Details</h5>
                    @if($profile->status === 'approved')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                            <i class="bi bi-patch-check-fill text-gold me-1"></i> Verified Farm
                        </span>
                    @elseif($profile->status === 'pending')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1 rounded-pill">
                            <i class="bi bi-clock-history me-1"></i> Verification Pending
                        </span>
                    @endif
                </div>

                <form action="{{ route('seller.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Farmer Avatar Upload -->
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border">
                                <div class="rounded-circle overflow-hidden border border-2 border-white shadow-sm flex-shrink-0" style="width: 70px; height: 70px;">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-100 h-100 object-fit-cover" id="farmerAvatarPreview">
                                </div>
                                <div class="flex-grow-1">
                                    <label for="avatar" class="form-label small fw-bold text-dark mb-1">
                                        Farmer Profile Picture <span class="text-muted fw-normal">(JPG, PNG, WebP &bull; Max 2MB)</span>
                                    </label>
                                    <input type="file" class="form-control form-control-sm @error('avatar') is-invalid @enderror" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp" onchange="previewFarmerAvatar(this)">
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($user->avatar)
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="removeAvatarCheck">
                                            <label class="form-check-label text-danger small" for="removeAvatarCheck">
                                                <i class="bi bi-trash3 me-1"></i> Remove profile picture
                                            </label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Farmer Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="farm_name" class="form-label fw-semibold">Farm / Brand Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('farm_name') is-invalid @enderror" id="farm_name" name="farm_name" value="{{ old('farm_name', $profile->farm_name ?? $user->name) }}" required>
                            @error('farm_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="location" class="form-label fw-semibold">Farm Location / Valley <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" placeholder="e.g. Karimabad, Hunza" value="{{ old('location', $profile->location) }}" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">Farm Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="0355XXXXXXX" value="{{ old('phone', $user->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Farm Description & Heritage <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Tell customers about your grazing pastures, cattle breeds, organic methods, and generations of mountain farming tradition..." required>{{ old('description', $profile->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Farm Cover Photo -->
                        <div class="col-12">
                            <label for="photo" class="form-label fw-semibold">Farm Landscape / Cover Photo (JPG, PNG, WebP, max 2MB)</label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" onchange="previewFarmCover(this)">
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($profile && $profile->photo_path)
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="removePhotoCheck">
                                    <label class="form-check-label text-danger small" for="removePhotoCheck">
                                        <i class="bi bi-trash3 me-1"></i> Remove farm cover photo
                                    </label>
                                </div>
                            @endif
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                <i class="bi bi-save me-1"></i> Save Profile Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Current Photo & Public Preview Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center mb-4">
                <div class="position-relative mb-4">
                    <!-- Farm Cover Banner -->
                    <div class="rounded-4 overflow-hidden bg-light border mx-auto" style="width: 100%; height: 140px;">
                        @if($profile && $profile->photo_path)
                            <img src="{{ Storage::url($profile->photo_path) }}" alt="{{ $profile->farm_name }}" class="w-100 h-100 object-fit-cover" id="sidebarFarmCover">
                        @else
                            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted" id="sidebarFarmCoverPlaceholder">
                                <i class="bi bi-image fs-1 text-gold mb-1"></i>
                                <span class="small">No cover photo</span>
                            </div>
                            <img src="" class="w-100 h-100 object-fit-cover d-none" id="sidebarFarmCover">
                        @endif
                    </div>
                    <!-- Farmer Avatar overlapping -->
                    <div class="position-absolute start-50 translate-middle-x" style="bottom: -35px;">
                        <div class="rounded-circle overflow-hidden border border-3 border-white shadow" style="width: 70px; height: 70px; background-color: var(--od-primary);">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-100 h-100 object-fit-cover" id="sidebarFarmerAvatar">
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <h5 class="font-serif fw-bold text-dark-green mb-0">{{ $profile->farm_name ?? $user->name }}</h5>
                    <div class="small text-muted mb-2"><i class="bi bi-person me-1"></i> {{ $user->name }}</div>
                    <div class="small text-secondary mb-3"><i class="bi bi-geo-alt me-1"></i> {{ $profile->location ?? 'Gilgit-Baltistan' }}</div>

                    <a href="{{ route('farmers.show', $user->id) }}" target="_blank" class="btn btn-outline-darkgreen rounded-pill btn-sm px-3 w-100 fw-semibold">
                        <i class="bi bi-box-arrow-up-right me-1"></i> View Public Farm Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewFarmerAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('farmerAvatarPreview');
                const sidebarImg = document.getElementById('sidebarFarmerAvatar');
                if (img) img.src = e.target.result;
                if (sidebarImg) sidebarImg.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewFarmCover(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const sidebarCover = document.getElementById('sidebarFarmCover');
                const placeholder = document.getElementById('sidebarFarmCoverPlaceholder');
                if (sidebarCover) {
                    sidebarCover.src = e.target.result;
                    sidebarCover.classList.remove('d-none');
                }
                if (placeholder) {
                    placeholder.classList.add('d-none');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
