@extends('layouts.public')

@section('title', 'Address Book')

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-success text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('profile.edit') }}" class="text-success text-decoration-none">My Account</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Address Book</li>
                </ol>
            </nav>
            <h2 class="heading-serif mb-0">Delivery Address Book</h2>
            <p class="text-muted small mb-0">Manage your shipping destinations across Gilgit-Baltistan.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAddressModal">
                <i class="bi bi-plus-circle me-1"></i> Add New Address
            </button>
        </div>
    </div>

    <!-- Address Cards Grid -->
    <div class="row g-4">
        @forelse($addresses as $address)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-4 shadow-sm position-relative d-flex flex-column justify-content-between {{ $address->is_default ? 'border-success' : '' }}" style="{{ $address->is_default ? 'border-width: 2px;' : '' }}">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="heading-serif mb-0 fw-bold">{{ $address->full_name }}</h5>
                            @if($address->is_default)
                                <span class="badge badge-status-ok"><i class="bi bi-check2-circle me-1"></i> Default</span>
                            @endif
                        </div>

                        <div class="small text-muted mb-3 d-flex flex-column gap-1">
                            <div><i class="bi bi-telephone text-success me-2"></i> {{ $address->phone }}</div>
                            <div><i class="bi bi-geo-alt text-success me-2"></i> <strong>{{ $address->city }}</strong> &bull; {{ $address->area }}</div>
                            <div class="text-dark mt-1 p-2 rounded-3 bg-light border">
                                {{ $address->address_line }}
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-darkgreen px-3" data-bs-toggle="modal" data-bs-target="#editAddressModal{{ $address->id }}">
                                <i class="bi bi-pencil-square me-1"></i> Edit
                            </button>

                            <form action="{{ route('addresses.destroy', $address->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Delete Address">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>

                        @if(!$address->is_default)
                            <form action="{{ route('addresses.set-default', $address->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm text-success text-decoration-none fw-semibold p-0">
                                    Make Default
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Edit Address Modal -->
            <div class="modal fade" id="editAddressModal{{ $address->id }}" tabindex="-1" aria-labelledby="editAddressModalLabel{{ $address->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow">
                        <form action="{{ route('addresses.update', $address->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header border-bottom px-4 pt-4 pb-3">
                                <h5 class="heading-serif modal-title" id="editAddressModalLabel{{ $address->id }}">Edit Delivery Address</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">Recipient Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" value="{{ old('full_name', $address->full_name) }}" class="form-control" required>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" value="{{ old('phone', $address->phone) }}" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">City / District <span class="text-danger">*</span></label>
                                        <select name="city" class="form-select" required>
                                            @foreach($allowedCities as $city)
                                                <option value="{{ $city }}" {{ old('city', $address->city) == $city ? 'selected' : '' }}>{{ $city }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">Area / Neighborhood / Valley <span class="text-danger">*</span></label>
                                    <input type="text" name="area" value="{{ old('area', $address->area) }}" class="form-control" placeholder="e.g. Jutial, Aliabad, Hameed Garh" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">Complete Street Address / House No <span class="text-danger">*</span></label>
                                    <textarea name="address_line" rows="2" class="form-control" required>{{ old('address_line', $address->address_line) }}</textarea>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" name="is_default" value="1" id="edit_is_default_{{ $address->id }}" class="form-check-input" {{ old('is_default', $address->is_default) ? 'checked' : '' }}>
                                    <label class="form-check-label small text-muted" for="edit_is_default_{{ $address->id }}">Set as my default shipping address</label>
                                </div>
                            </div>
                            <div class="modal-footer border-0 px-4 pb-4">
                                <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary px-4">Update Address</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center shadow-sm">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center text-muted mb-3" style="width: 72px; height: 72px; background-color: #FAF7F0;">
                        <i class="bi bi-geo-alt fs-1 text-warning"></i>
                    </div>
                    <h4 class="heading-serif mb-2">No Saved Addresses Found</h4>
                    <p class="text-muted small mx-auto mb-4" style="max-width: 420px;">
                        You haven't added any shipping destinations yet. Add your address in Gilgit, Skardu, Hunza or other GB valleys for faster checkout!
                    </p>
                    <div>
                        <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#createAddressModal">
                            <i class="bi bi-plus-circle me-1"></i> Add Your First Address
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Create Address Modal -->
<div class="modal fade" id="createAddressModal" tabindex="-1" aria-labelledby="createAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('addresses.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <h5 class="heading-serif modal-title" id="createAddressModalLabel">Add New Delivery Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Recipient Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" value="{{ old('full_name', Auth::user()->name) }}" class="form-control @error('full_name') is-invalid @enderror" placeholder="e.g. Ali Khan" required>
                        @error('full_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone', Auth::user()->phone) }}" class="form-control @error('phone') is-invalid @enderror" placeholder="03451234567" required>
                            @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">City / District <span class="text-danger">*</span></label>
                            <select name="city" class="form-select @error('city') is-invalid @enderror" required>
                                <option value="">Select City</option>
                                @foreach($allowedCities as $city)
                                    <option value="{{ $city }}" {{ old('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                                @endforeach
                            </select>
                            @error('city') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Area / Neighborhood / Valley <span class="text-danger">*</span></label>
                        <input type="text" name="area" value="{{ old('area') }}" class="form-control @error('area') is-invalid @enderror" placeholder="e.g. Jutial, Aliabad, Hameed Garh" required>
                        @error('area') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Complete Street Address / House Details <span class="text-danger">*</span></label>
                        <textarea name="address_line" rows="2" class="form-control @error('address_line') is-invalid @enderror" placeholder="House #, Street name, Landmark..." required>{{ old('address_line') }}</textarea>
                        @error('address_line') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="is_default" value="1" id="create_is_default" class="form-check-input" {{ old('is_default') || $addresses->isEmpty() ? 'checked' : '' }}>
                        <label class="form-check-label small text-muted" for="create_is_default">Set as default delivery address</label>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
