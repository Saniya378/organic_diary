@extends('layouts.public')

@section('title', 'My Profile & Security')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-success text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                </ol>
            </nav>
            <h2 class="heading-serif mb-0">Account Settings</h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('addresses.index') }}" class="btn btn-outline-darkgreen">
                <i class="bi bi-geo-alt me-1"></i> Address Book
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-primary">
                <i class="bi bi-box-seam me-1"></i> My Orders
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Summary Card -->
        <div class="col-lg-4">
            <div class="card p-4 shadow-sm text-center rounded-4 border-0 bg-white">
                <div class="mx-auto rounded-circle overflow-hidden mb-3 border border-3 border-light shadow-sm position-relative" style="width: 100px; height: 100px; background-color: var(--od-primary);">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-100 h-100 object-fit-cover" id="sidebarAvatarPreview">
                </div>
                <h4 class="heading-serif mb-1 fw-bold text-dark-green">{{ $user->name }}</h4>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                <div class="mb-3">
                    <span class="badge {{ $user->isAdmin() ? 'bg-danger text-white' : ($user->isSeller() ? 'bg-warning text-dark' : 'bg-success text-white') }} text-capitalize px-3 py-1.5 rounded-pill">
                        {{ $user->role }} Account
                    </span>
                </div>
                <hr class="my-3">
                <div class="text-start small text-muted d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between">
                        <span>Contact:</span>
                        <strong class="text-dark">{{ $user->phone }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Member Since:</span>
                        <strong class="text-dark">{{ $user->created_at->format('M Y') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Saved Addresses:</span>
                        <strong class="text-dark">{{ $user->addresses()->count() }}</strong>
                    </div>
                </div>

                @if($user->isSeller())
                    <hr class="my-3">
                    <a href="{{ route('seller.dashboard') }}" class="btn btn-outline-darkgreen rounded-pill w-100 btn-sm fw-semibold">
                        <i class="bi bi-shop me-1 text-warning"></i> Farmer Dashboard
                    </a>
                @elseif($user->isAdmin())
                    <hr class="my-3">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-darkgreen rounded-pill w-100 btn-sm fw-semibold">
                        <i class="bi bi-speedometer2 me-1 text-danger"></i> Admin Center
                    </a>
                @endif
            </div>
        </div>

        <!-- Forms Column -->
        <div class="col-lg-8">
            <!-- Profile Info Form -->
            <div class="card p-4 p-md-5 shadow-sm rounded-4 border-0 bg-white mb-4">
                <h4 class="heading-serif fw-bold text-dark-green mb-1">Personal Details</h4>
                <p class="text-muted small mb-4">Update your profile photo, name, and contact details used for notifications and doorstep delivery updates.</p>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Profile Avatar Upload Section -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border mb-4">
                        <div class="rounded-circle overflow-hidden border border-2 border-white shadow-sm flex-shrink-0" style="width: 70px; height: 70px;">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-100 h-100 object-fit-cover" id="formAvatarPreview">
                        </div>
                        <div class="flex-grow-1">
                            <label for="avatarInput" class="form-label small fw-bold text-dark mb-1">
                                Profile Photo <span class="text-muted fw-normal">(JPG, PNG, WebP &bull; Max 2MB)</span>
                            </label>
                            <input type="file" name="avatar" id="avatarInput" class="form-control form-control-sm @error('avatar') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" onchange="previewUserAvatar(this)">
                            @error('avatar') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @if($user->avatar)
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="removeAvatarCheck">
                                    <label class="form-check-label text-danger small" for="removeAvatarCheck">
                                        <i class="bi bi-trash3 me-1"></i> Remove profile photo
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label small fw-bold text-dark">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label small fw-bold text-dark">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror" required>
                            @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label small fw-bold text-dark">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                            <i class="bi bi-check2 me-1"></i> Save Profile Details
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change Form -->
            <div class="card p-4 p-md-5 shadow-sm">
                <h4 class="heading-serif mb-1">Security & Password</h4>
                <p class="text-muted small mb-4">Ensure your account is using a secure password (min 8 characters with at least one letter and number).</p>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <!-- Hidden preserve fields -->
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <input type="hidden" name="phone" value="{{ $user->phone }}">

                    <div class="mb-3">
                        <label for="current_password" class="form-label small fw-bold text-dark">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="current_password" class="form-control border-end-0 @error('current_password') is-invalid @enderror" placeholder="••••••••" required>
                            <button type="button" class="btn btn-light border border-start-0 text-muted toggle-password" data-target="current_password" title="Show/Hide Password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('current_password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="password" class="form-label small fw-bold text-dark">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control border-end-0 @error('password') is-invalid @enderror" placeholder="••••••••" required>
                                <button type="button" class="btn btn-light border border-start-0 text-muted toggle-password" data-target="password" title="Show/Hide Password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label small fw-bold text-dark">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control border-end-0" placeholder="••••••••" required>
                                <button type="button" class="btn btn-light border border-start-0 text-muted toggle-password" data-target="password_confirmation" title="Show/Hide Password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-outline-darkgreen rounded-pill px-4 fw-bold">
                            <i class="bi bi-shield-lock me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewUserAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const sidebarImg = document.getElementById('sidebarAvatarPreview');
                const formImg = document.getElementById('formAvatarPreview');
                if (sidebarImg) sidebarImg.src = e.target.result;
                if (formImg) formImg.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
    });
</script>
@endpush
@endsection
