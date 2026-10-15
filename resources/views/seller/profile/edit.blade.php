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
                    @elseif($profile->status === 'rejected')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i> Application Rejected
                        </span>
                    @endif
                </div>

                @if($profile->status === 'rejected' && $profile->rejection_reason)
                    <div class="alert alert-danger rounded-4 p-3 mb-4 border-0 d-flex gap-3 align-items-start" style="background-color: #FDF0EE; color: #C53030;">
                        <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0 text-danger"></i>
                        <div>
                            <strong class="d-block mb-1">Admin Verification Feedback:</strong>
                            <p class="mb-2 small">{{ $profile->rejection_reason }}</p>
                            <small class="text-muted">Please update your original CNIC documents or farm information below and click Save. Your application will be re-submitted for admin approval.</small>
                        </div>
                    </div>
                @endif

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

                        <!-- Delivery & Serviceable Coverage Settings -->
                        <div class="col-12 mt-4">
                            <div class="p-3 bg-light rounded-4 border">
                                <h6 class="fw-bold text-dark-green mb-1"><i class="bi bi-truck me-2 text-gold"></i> Delivery & Coverage Settings</h6>
                                <p class="small text-muted mb-3">Define which cities/areas you can deliver your fresh dairy to, and set your delivery rates.</p>

                                <div class="row g-3">
                                    <!-- Serviceable Cities -->
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-dark mb-2">Serviceable Cities / Regions</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            @php
                                                $selectedCities = old('serviceable_cities', $profile->serviceable_cities ?? []);
                                                if (!is_array($selectedCities)) {
                                                    $selectedCities = [];
                                                }
                                            @endphp
                                            @foreach($allowedCities as $city)
                                                <div class="form-check form-check-inline bg-white px-3 py-2 rounded-3 border shadow-2xs m-0">
                                                    <input class="form-check-input" type="checkbox" name="serviceable_cities[]" value="{{ $city }}" id="city_{{ Str::slug($city) }}" {{ in_array($city, $selectedCities) ? 'checked' : '' }}>
                                                    <label class="form-check-label small fw-semibold text-dark" for="city_{{ Str::slug($city) }}">
                                                        {{ $city }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="form-text small text-muted mt-1">Leave all unchecked if you can deliver to all supported cities.</div>
                                    </div>

                                    <!-- Specific Serviceable Areas -->
                                    <div class="col-12">
                                        <label for="serviceable_areas" class="form-label small fw-bold text-dark mb-1">Specific Neighborhoods / Local Areas (Optional)</label>
                                        <input type="text" class="form-control form-control-sm @error('serviceable_areas') is-invalid @enderror" id="serviceable_areas" name="serviceable_areas" placeholder="e.g. Jutial, Amphary, Baseen, Danyore, Zulfiqarabad" value="{{ old('serviceable_areas', $profile->serviceable_areas) }}">
                                        <div class="form-text small text-muted">Provide comma-separated specific areas or sectors you deliver to.</div>
                                        @error('serviceable_areas')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Custom Delivery Fee -->
                                    <div class="col-md-4">
                                        <label for="delivery_fee" class="form-label small fw-bold text-dark mb-1">Your Delivery Fee (Rs)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-end-0">Rs</span>
                                            <input type="number" min="0" max="2000" class="form-control @error('delivery_fee') is-invalid @enderror" id="delivery_fee" name="delivery_fee" placeholder="Default 150" value="{{ old('delivery_fee', $profile->delivery_fee) }}">
                                        </div>
                                        <div class="form-text small text-muted">Leave empty for platform default (Rs 150).</div>
                                        @error('delivery_fee')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Free Delivery Threshold -->
                                    <div class="col-md-4">
                                        <label for="free_delivery_threshold" class="form-label small fw-bold text-dark mb-1">Free Delivery Above (Rs)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-end-0">Rs</span>
                                            <input type="number" min="0" max="50000" class="form-control @error('free_delivery_threshold') is-invalid @enderror" id="free_delivery_threshold" name="free_delivery_threshold" placeholder="e.g. 1500" value="{{ old('free_delivery_threshold', $profile->free_delivery_threshold) }}">
                                        </div>
                                        <div class="form-text small text-muted">Free shipping for orders over this amount.</div>
                                        @error('free_delivery_threshold')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Minimum Order Amount -->
                                    <div class="col-md-4">
                                        <label for="min_order_amount" class="form-label small fw-bold text-dark mb-1">Min. Order Amount (Rs)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-end-0">Rs</span>
                                            <input type="number" min="0" max="50000" class="form-control @error('min_order_amount') is-invalid @enderror" id="min_order_amount" name="min_order_amount" placeholder="e.g. 500" value="{{ old('min_order_amount', $profile->min_order_amount) }}">
                                        </div>
                                        <div class="form-text small text-muted">Minimum checkout subtotal for your items.</div>
                                        @error('min_order_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Government CNIC Identity Verification -->
                        <div class="col-12">
                            <div class="p-3.5 bg-light rounded-4 border">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold text-dark mb-0">
                                        <i class="bi bi-person-vcard-fill text-success me-1"></i> Government CNIC Identity Verification
                                    </h6>
                                    @if($profile->hasCnic())
                                        <span class="badge bg-success-subtle text-success border border-success-subtle text-xs px-2.5 py-1">
                                            <i class="bi bi-patch-check-fill me-1"></i> CNIC Documents on File
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle text-xs px-2.5 py-1">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> CNIC Missing
                                        </span>
                                    @endif
                                </div>
                                <p class="small text-muted mb-3">
                                    Admin inspects your real CNIC to verify authenticity. You can update your CNIC number and upload clear front and back photos below if needed.
                                </p>

                                <div class="row g-3">
                                    <!-- CNIC Number -->
                                    <div class="col-12">
                                        <label for="cnic_no" class="form-label text-xs fw-bold text-dark mb-1">CNIC Number (شناختی کارڈ نمبر)</label>
                                        <div class="input-group input-group-sm" style="max-width: 320px;">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-card-heading text-muted"></i></span>
                                            <input type="text" maxlength="15" class="form-control form-control-sm font-monospace bg-white border-start-0 @error('cnic_no') is-invalid @enderror" id="cnic_no" name="cnic_no" placeholder="71101-1234567-1" value="{{ old('cnic_no', $profile->cnic_no) }}">
                                        </div>
                                        <div class="form-text text-xs text-muted">13 digits format (e.g. 71101-1234567-1).</div>
                                        @error('cnic_no') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Front Card -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-white rounded-3 border h-100">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="fw-bold text-dark small">
                                                    <i class="bi bi-image text-success me-1"></i> CNIC Front Side (سامنے)
                                                </span>
                                                @if($profile->cnic_front)
                                                    <span class="badge bg-success-subtle text-success border text-xs">Current Card</span>
                                                @endif
                                            </div>

                                            @if($profile->cnic_front)
                                                @if($profile->is_cnic_front_pdf)
                                                    <div class="p-3 bg-light rounded text-center border mb-2">
                                                        <i class="bi bi-file-earmark-pdf-fill fs-3 text-danger d-block mb-1"></i>
                                                        <span class="small fw-semibold text-dark d-block">PDF Document</span>
                                                        <a href="{{ Storage::disk('public')->url($profile->cnic_front) }}" target="_blank" class="btn btn-xs btn-outline-danger mt-1">View Current PDF</a>
                                                    </div>
                                                @else
                                                    <div class="p-1 bg-light rounded text-center border mb-2" style="height: 120px;">
                                                        <img src="{{ Storage::disk('public')->url($profile->cnic_front) }}" alt="CNIC Front" class="img-fluid rounded w-100 h-100" style="object-fit: contain;">
                                                    </div>
                                                    <div class="text-center mb-2">
                                                        <a href="{{ Storage::disk('public')->url($profile->cnic_front) }}" target="_blank" class="small text-decoration-none text-success">
                                                            <i class="bi bi-arrows-fullscreen me-1"></i> View Full Image
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif

                                            <label for="cnic_front" class="form-label text-xs fw-semibold text-muted mb-1">
                                                {{ $profile->cnic_front ? 'Upload new front photo to replace' : 'Upload front photo' }}
                                            </label>
                                            <input type="file" class="form-control form-control-sm @error('cnic_front') is-invalid @enderror" id="cnic_front" name="cnic_front" accept="image/jpeg,image/png,image/jpg,image/webp,application/pdf">
                                            <small class="text-muted" style="font-size: 0.7rem;">JPG, PNG, WebP or PDF &bull; Max 5MB</small>
                                            @error('cnic_front') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <!-- Back Card -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-white rounded-3 border h-100">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="fw-bold text-dark small">
                                                    <i class="bi bi-image text-secondary me-1"></i> CNIC Back Side (پیچھے)
                                                </span>
                                                @if($profile->cnic_back)
                                                    <span class="badge bg-success-subtle text-success border text-xs">Current Card</span>
                                                @endif
                                            </div>

                                            @if($profile->cnic_back)
                                                @if($profile->is_cnic_back_pdf)
                                                    <div class="p-3 bg-light rounded text-center border mb-2">
                                                        <i class="bi bi-file-earmark-pdf-fill fs-3 text-danger d-block mb-1"></i>
                                                        <span class="small fw-semibold text-dark d-block">PDF Document</span>
                                                        <a href="{{ Storage::disk('public')->url($profile->cnic_back) }}" target="_blank" class="btn btn-xs btn-outline-danger mt-1">View Current PDF</a>
                                                    </div>
                                                @else
                                                    <div class="p-1 bg-light rounded text-center border mb-2" style="height: 120px;">
                                                        <img src="{{ Storage::disk('public')->url($profile->cnic_back) }}" alt="CNIC Back" class="img-fluid rounded w-100 h-100" style="object-fit: contain;">
                                                    </div>
                                                    <div class="text-center mb-2">
                                                        <a href="{{ Storage::disk('public')->url($profile->cnic_back) }}" target="_blank" class="small text-decoration-none text-success">
                                                            <i class="bi bi-arrows-fullscreen me-1"></i> View Full Image
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif

                                            <label for="cnic_back" class="form-label text-xs fw-semibold text-muted mb-1">
                                                {{ $profile->cnic_back ? 'Upload new back photo to replace' : 'Upload back photo' }}
                                            </label>
                                            <input type="file" class="form-control form-control-sm @error('cnic_back') is-invalid @enderror" id="cnic_back" name="cnic_back" accept="image/jpeg,image/png,image/jpg,image/webp,application/pdf">
                                            <small class="text-muted" style="font-size: 0.7rem;">JPG, PNG, WebP or PDF &bull; Max 5MB</small>
                                            @error('cnic_back') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Direct Customer Payment Receiving Accounts -->
                        <div class="col-12">
                            <div class="p-3.5 bg-light rounded-4 border">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold text-dark mb-0">
                                        <i class="bi bi-wallet2 text-success me-1"></i> Customer Direct Payment Receiving Accounts
                                    </h6>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle text-xs px-2.5 py-1">Direct to Seller</span>
                                </div>
                                <p class="small text-muted mb-3">
                                    Customers who place online orders will send money directly to these accounts. Website administration will only charge product-based commission.
                                </p>

                                <div class="row g-3">
                                    <!-- EasyPaisa -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-white rounded-3 border h-100">
                                            <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1">
                                                <i class="bi bi-phone text-success"></i> EasyPaisa Account
                                            </div>
                                            <div class="mb-2">
                                                <label for="easypaisa_title" class="form-label text-xs fw-semibold text-muted mb-1">Account Title</label>
                                                <input type="text" class="form-control form-control-sm" id="easypaisa_title" name="easypaisa_title" placeholder="e.g. Muhammad Ali" value="{{ old('easypaisa_title', $profile->easypaisa_title) }}">
                                            </div>
                                            <div>
                                                <label for="easypaisa_number" class="form-label text-xs fw-semibold text-muted mb-1">Mobile / Account Number</label>
                                                <input type="text" class="form-control form-control-sm font-monospace" id="easypaisa_number" name="easypaisa_number" placeholder="0345XXXXXXX" value="{{ old('easypaisa_number', $profile->easypaisa_number) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- JazzCash -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-white rounded-3 border h-100">
                                            <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1">
                                                <i class="bi bi-phone-fill text-danger"></i> JazzCash Account
                                            </div>
                                            <div class="mb-2">
                                                <label for="jazzcash_title" class="form-label text-xs fw-semibold text-muted mb-1">Account Title</label>
                                                <input type="text" class="form-control form-control-sm" id="jazzcash_title" name="jazzcash_title" placeholder="e.g. Muhammad Ali" value="{{ old('jazzcash_title', $profile->jazzcash_title) }}">
                                            </div>
                                            <div>
                                                <label for="jazzcash_number" class="form-label text-xs fw-semibold text-muted mb-1">Mobile / Account Number</label>
                                                <input type="text" class="form-control form-control-sm font-monospace" id="jazzcash_number" name="jazzcash_number" placeholder="0300XXXXXXX" value="{{ old('jazzcash_number', $profile->jazzcash_number) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bank Account -->
                                    <div class="col-12">
                                        <div class="p-3 bg-white rounded-3 border">
                                            <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1">
                                                <i class="bi bi-bank text-primary"></i> Bank Account Transfer (Optional)
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <label for="bank_name" class="form-label text-xs fw-semibold text-muted mb-1">Bank Name</label>
                                                    <input type="text" class="form-control form-control-sm" id="bank_name" name="bank_name" placeholder="e.g. Meezan Bank, HBL" value="{{ old('bank_name', $profile->bank_name) }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="bank_account_title" class="form-label text-xs fw-semibold text-muted mb-1">Account Title</label>
                                                    <input type="text" class="form-control form-control-sm" id="bank_account_title" name="bank_account_title" placeholder="Title as on bank statement" value="{{ old('bank_account_title', $profile->bank_account_title) }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="bank_account_number" class="form-label text-xs fw-semibold text-muted mb-1">Account Number</label>
                                                    <input type="text" class="form-control form-control-sm font-monospace" id="bank_account_number" name="bank_account_number" placeholder="0123456789" value="{{ old('bank_account_number', $profile->bank_account_number) }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="bank_iban" class="form-label text-xs fw-semibold text-muted mb-1">IBAN (24 Characters)</label>
                                                    <input type="text" class="form-control form-control-sm font-monospace" id="bank_iban" name="bank_iban" placeholder="PK36MEZN..." value="{{ old('bank_iban', $profile->bank_iban) }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="bank_branch" class="form-label text-xs fw-semibold text-muted mb-1">Branch Name / City</label>
                                                    <input type="text" class="form-control form-control-sm" id="bank_branch" name="bank_branch" placeholder="e.g. Gilgit Branch" value="{{ old('bank_branch', $profile->bank_branch) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Custom Instructions for Buyer -->
                                    <div class="col-12">
                                        <label for="payment_instructions" class="form-label text-xs fw-semibold text-dark mb-1">Special Payment Instructions for Buyer (Optional)</label>
                                        <textarea class="form-control form-control-sm" id="payment_instructions" name="payment_instructions" rows="2" placeholder="e.g. Please send the exact amount and mention your Order No in transfer notes.">{{ old('payment_instructions', $profile->payment_instructions) }}</textarea>
                                    </div>
                                </div>
                            </div>
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

    document.addEventListener('DOMContentLoaded', function() {
        const cnicInput = document.getElementById('cnic_no');
        if (cnicInput) {
            cnicInput.addEventListener('input', function() {
                let digits = this.value.replace(/\D/g, '');
                if (digits.length > 13) digits = digits.substring(0, 13);
                let formatted = '';
                if (digits.length > 5) {
                    formatted += digits.substring(0, 5) + '-';
                    if (digits.length > 12) {
                        formatted += digits.substring(5, 12) + '-' + digits.substring(12, 13);
                    } else {
                        formatted += digits.substring(5);
                    }
                } else {
                    formatted = digits;
                }
                this.value = formatted;
            });
        }
    });
</script>
@endpush
@endsection
