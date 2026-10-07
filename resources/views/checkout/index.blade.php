@extends('layouts.public')

@section('title', 'Checkout - Gilgit-Baltistan Fresh Organic Dairy')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cart.index') }}" class="text-decoration-none">Basket</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
        </ol>
    </nav>

    <!-- 3-Step Checkout Progress Tags -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-3 p-md-4 bg-white">
            <div class="row text-center g-2 align-items-center">
                <div class="col-4">
                    <a href="{{ route('cart.index') }}" class="text-decoration-none d-flex align-items-center justify-content-center gap-2 text-success fw-semibold">
                        <span class="badge rounded-circle bg-success text-white p-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                            <i class="bi bi-check-lg"></i>
                        </span>
                        <span class="d-none d-sm-inline">1. Basket</span>
                    </a>
                </div>
                <div class="col-4">
                    <div class="d-flex align-items-center justify-content-center gap-2 text-primary fw-bold">
                        <span class="badge rounded-circle bg-primary text-white p-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                            2
                        </span>
                        <span>2. Delivery</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="d-flex align-items-center justify-content-center gap-2 text-muted">
                        <span class="badge rounded-circle bg-light text-muted border p-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                            3
                        </span>
                        <span class="d-none d-sm-inline">3. Payment</span>
                    </div>
                </div>
            </div>
            <div class="progress mt-3" style="height: 4px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-3 mb-4 shadow-sm">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Please correct the following:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="row g-4">
            <!-- Left Column: Delivery Form & Payment Cards -->
            <div class="col-lg-7 col-xl-8">
                
                <!-- Saved Address Picker (If any) -->
                @if($savedAddresses->isNotEmpty())
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white py-3 border-bottom border-light">
                            <h5 class="mb-0 fw-bold font-serif text-dark-green">
                                <i class="bi bi-geo-alt-fill text-gold me-2"></i> Saved Delivery Addresses
                            </h5>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <div class="row g-3">
                                @foreach($savedAddresses as $addr)
                                    <div class="col-md-6">
                                        <div class="form-check address-card p-3 border rounded-3 h-100 position-relative cursor-pointer {{ $loop->first ? 'border-primary bg-light' : '' }}" 
                                             onclick="fillAddress('{{ addslashes($addr->full_name) }}', '{{ addslashes($addr->phone) }}', '{{ addslashes($addr->city) }}', '{{ addslashes($addr->area) }}', '{{ addslashes($addr->address_line) }}', this)">
                                            <input class="form-check-input mt-1" type="radio" name="saved_address_id" id="addr_{{ $addr->id }}" value="{{ $addr->id }}" {{ $loop->first ? 'checked' : '' }}>
                                            <label class="form-check-label w-100 ms-1 cursor-pointer" for="addr_{{ $addr->id }}">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <strong class="text-dark">{{ $addr->full_name }}</strong>
                                                    @if($addr->is_default)
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Default</span>
                                                    @endif
                                                </div>
                                                <div class="small text-muted mb-1"><i class="bi bi-telephone me-1"></i> {{ $addr->phone }}</div>
                                                <div class="small text-secondary">{{ $addr->address_line }}, {{ $addr->area }}, <strong>{{ $addr->city }}</strong></div>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Step 2: Delivery Details Form -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom border-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold font-serif text-dark-green">
                            <i class="bi bi-truck text-gold me-2"></i> Delivery Information
                        </h5>
                        <span class="badge bg-light text-muted border">Gilgit-Baltistan Only</span>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="shipping_full_name" class="form-label fw-semibold">Recipient Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('shipping_full_name') is-invalid @enderror" id="shipping_full_name" name="shipping_full_name" value="{{ old('shipping_full_name', $defaultAddress->full_name ?? Auth::user()->name) }}" required>
                                @error('shipping_full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="shipping_phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">+92</span>
                                    <input type="tel" class="form-control @error('shipping_phone') is-invalid @enderror" id="shipping_phone" name="shipping_phone" placeholder="03XXXXXXXXX" value="{{ old('shipping_phone', $defaultAddress->phone ?? Auth::user()->phone) }}" required>
                                </div>
                                @error('shipping_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="shipping_city" class="form-label fw-semibold">City / Region (GB) <span class="text-danger">*</span></label>
                                <select class="form-select @error('shipping_city') is-invalid @enderror" id="shipping_city" name="shipping_city" required>
                                    <option value="" disabled>-- Select City in Gilgit-Baltistan --</option>
                                    @foreach($allowedCities as $city)
                                        <option value="{{ $city }}" {{ old('shipping_city', $defaultAddress->city ?? 'Gilgit') === $city ? 'selected' : '' }}>
                                            {{ $city }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('shipping_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="shipping_area" class="form-label fw-semibold">Area / Mohallah / Landmark <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('shipping_area') is-invalid @enderror" id="shipping_area" name="shipping_area" placeholder="e.g. Jutial, River View Road, Karimabad" value="{{ old('shipping_area', $defaultAddress->area ?? '') }}" required>
                                @error('shipping_area')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="shipping_address_line" class="form-label fw-semibold">Full Street Address <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('shipping_address_line') is-invalid @enderror" id="shipping_address_line" name="shipping_address_line" rows="2" placeholder="House/Apartment #, Street, Near Landmark" required>{{ old('shipping_address_line', $defaultAddress->address_line ?? '') }}</textarea>
                                @error('shipping_address_line')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="save_address" name="save_address" value="1" {{ old('save_address') ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted" for="save_address">
                                        Save or update this address in my address book
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Payment Method Cards -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom border-light">
                        <h5 class="mb-0 fw-bold font-serif text-dark-green">
                            <i class="bi bi-credit-card-2-front-fill text-gold me-2"></i> Payment Method
                        </h5>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3 mb-3">
                            <!-- Card 1: Direct Real-Time Online Transfer -->
                            <div class="col-12">
                                <label class="payment-card border rounded-3 p-3 d-flex align-items-center gap-3 cursor-pointer w-100 transition-all active-payment" for="method_bank" id="label_bank_transfer">
                                    <input class="form-check-input mt-0" type="radio" name="payment_method" id="method_bank" value="bank_transfer" {{ old('payment_method', 'bank_transfer') === 'bank_transfer' ? 'checked' : '' }} onchange="selectPaymentMethod('bank_transfer')">
                                    <div class="icon-wrap bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="bi bi-bank2 fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                            <span>Direct Online Account Transfer (Bank / Easypaisa / JazzCash)</span>
                                            <span class="badge bg-success text-white text-xs px-2 py-0.5">Recommended</span>
                                        </div>
                                        <div class="small text-muted">Instant transfer to official bank or mobile wallet. System provides official account numbers and lets you submit your Transaction ID for fast admin approval.</div>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">Real-Time</span>
                                </label>

                                <div id="bankDetailsNote" class="mt-2 p-3 bg-light rounded-3 border {{ old('payment_method', 'bank_transfer') === 'bank_transfer' ? '' : 'd-none' }}">
                                    <div class="d-flex align-items-center gap-2 small text-darkgreen fw-semibold mb-1">
                                        <i class="bi bi-info-circle-fill text-primary"></i> Next Step After Clicking Place Order:
                                    </div>
                                    <p class="small text-muted mb-0">
                                        You will be shown our official Meezan Bank, Easypaisa, and JazzCash account details with copy buttons. Simply transfer the amount, enter your <strong>Transaction ID (TRX ID)</strong>, and our admin team will review and approve your order.
                                    </p>
                                </div>
                            </div>

                            <!-- Card 2: Cash on Delivery -->
                            <div class="col-12">
                                <label class="payment-card border rounded-3 p-3 d-flex align-items-center gap-3 cursor-pointer w-100 transition-all" for="method_cod" id="label_cod">
                                    <input class="form-check-input mt-0" type="radio" name="payment_method" id="method_cod" value="cod" {{ old('payment_method') === 'cod' ? 'checked' : '' }} onchange="selectPaymentMethod('cod')">
                                    <div class="icon-wrap bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="bi bi-cash-stack fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark">Cash on Delivery (COD)</div>
                                        <div class="small text-muted">Pay in cash when your fresh dairy products arrive at your doorstep in Gilgit-Baltistan.</div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Doorstep</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Order Summary -->
            <div class="col-lg-5 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 90px; z-index: 10;">
                    <div class="card-header bg-white py-3 border-bottom border-light">
                        <h5 class="mb-0 fw-bold font-serif text-dark-green">
                            <i class="bi bi-receipt text-gold me-2"></i> Order Summary
                        </h5>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <!-- Multi-vendor Seller Breakdown -->
                        <div class="mb-3">
                            <div class="small fw-bold text-uppercase text-muted mb-2">Shipments ({{ $distinctSellersCount }} {{ Str::plural('Farm', $distinctSellersCount) }})</div>
                            @foreach($sellerGroups as $sellerId => $group)
                                <div class="p-2 mb-2 bg-light rounded-3 border">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark small">
                                            &#127969; {{ $group['seller']->sellerProfile->farm_name ?? $group['seller']->name }}
                                        </span>
                                        <span class="badge bg-white text-muted border small">Delivery: Rs {{ number_format($flatDeliveryFee) }}</span>
                                    </div>
                                    <ul class="list-unstyled mb-0 small text-secondary ps-2">
                                        @foreach($group['items'] as $it)
                                            <li class="d-flex justify-content-between py-1">
                                                <span>{{ $it->product->name }} &times; {{ $it->quantity }}</span>
                                                <span class="fw-semibold">Rs {{ number_format($it->line_total) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>

                        <hr class="text-muted opacity-25">

                        <!-- Pricing Breakdown -->
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Items Subtotal</span>
                            <span class="fw-semibold">Rs {{ number_format($subtotal) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivery Fee ({{ $distinctSellersCount }} &times; Rs {{ $flatDeliveryFee }})</span>
                            <span class="fw-semibold text-dark">Rs {{ number_format($totalDeliveryFee) }}</span>
                        </div>
                        @if($discount > 0)
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Discount</span>
                                <span>- Rs {{ number_format($discount) }}</span>
                            </div>
                        @endif

                        <hr class="text-muted opacity-25 my-3">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <span class="fs-5 fw-bold text-dark font-serif">Grand Total</span>
                                <div class="text-muted small">Inclusive of all taxes & delivery</div>
                            </div>
                            <span class="fs-4 fw-bold text-primary font-serif">Rs {{ number_format($grandTotal) }}</span>
                        </div>

                        <!-- Place Order Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-shield-check fs-5"></i> Place Order (Rs {{ number_format($grandTotal) }})
                        </button>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="bi bi-patch-check-fill text-gold me-1"></i> 100% Organic Quality Guaranteed &bull; Safe Checkout
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function selectPaymentMethod(method) {
        document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('border-primary', 'bg-light'));
        
        const label = document.getElementById('label_' + method);
        if (label) {
            label.classList.add('border-primary', 'bg-light');
        }

        const bankNote = document.getElementById('bankDetailsNote');
        if (bankNote) {
            bankNote.classList.toggle('d-none', method !== 'bank_transfer');
        }
    }

    function fillAddress(name, phone, city, area, addressLine, cardElement) {
        document.getElementById('shipping_full_name').value = name;
        document.getElementById('shipping_phone').value = phone;
        document.getElementById('shipping_city').value = city;
        document.getElementById('shipping_area').value = area;
        document.getElementById('shipping_address_line').value = addressLine;

        document.querySelectorAll('.address-card').forEach(c => c.classList.remove('border-primary', 'bg-light'));
        cardElement.classList.add('border-primary', 'bg-light');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const checkedPayment = document.querySelector('input[name="payment_method"]:checked');
        if (checkedPayment) {
            selectPaymentMethod(checkedPayment.value);
        }
    });
</script>
@endpush
@endsection
