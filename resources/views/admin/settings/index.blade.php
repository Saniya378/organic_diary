@extends('layouts.panel')

@section('title', 'Platform Settings - Admin Center')
@section('page_heading', 'Platform Configuration & Policies')

@section('menu_items')
    @include('admin.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">

            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-sliders fs-3"></i>
                    </div>
                    <div>
                        <h4 class="font-serif fw-bold text-dark mb-1">Global Marketplace Settings</h4>
                        <p class="text-muted small mb-0">Configure fees, commissions, inventory alert rules, and regional delivery coverage.</p>
                    </div>
                </div>

                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <!-- Delivery Fee -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark text-sm">
                                Standard Delivery Fee (Rs) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted fw-semibold">PKR</span>
                                <input type="number" name="delivery_fee" class="form-control form-control-lg @error('delivery_fee') is-invalid @enderror" value="{{ old('delivery_fee', $deliveryFee) }}" min="0" required>
                            </div>
                            <div class="form-text text-muted">Flat delivery charge applied per seller order.</div>
                            @error('delivery_fee')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Commission Percent -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark text-sm">
                                Platform Commission (%) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.1" name="commission_percent" class="form-control form-control-lg @error('commission_percent') is-invalid @enderror" value="{{ old('commission_percent', $commissionPercent) }}" min="0" max="100" required>
                                <span class="input-group-text bg-light text-muted fw-semibold">%</span>
                            </div>
                            <div class="form-text text-muted">Marketplace fee deducted automatically from seller item subtotal.</div>
                            @error('commission_percent')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Low Stock Threshold -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark text-sm">
                                Low-Stock Alert Threshold <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="low_stock_threshold" class="form-control form-control-lg @error('low_stock_threshold') is-invalid @enderror" value="{{ old('low_stock_threshold', $lowStockThreshold) }}" min="1" required>
                                <span class="input-group-text bg-light text-muted fw-semibold">Units</span>
                            </div>
                            <div class="form-text text-muted">Products and variants at or below this count will trigger warning tags.</div>
                            @error('low_stock_threshold')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Allowed Cities -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark text-sm">
                                Supported Delivery Cities & Regions <span class="text-danger">*</span>
                            </label>
                            <textarea name="allowed_cities" class="form-control @error('allowed_cities') is-invalid @enderror" rows="3" placeholder="Gilgit, Skardu, Hunza, Ghizer, Astore, Nagar, Diamer" required>{{ old('allowed_cities', $allowedCities) }}</textarea>
                            <div class="form-text text-muted">Enter city names separated by commas. These will populate the checkout delivery city dropdown.</div>
                            @error('allowed_cities')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            <!-- Preview Badges -->
                            <div class="mt-3">
                                <span class="text-xs text-muted fw-bold text-uppercase d-block mb-2">Active Service Areas:</span>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach(explode(',', $allowedCities) as $city)
                                        @if(trim($city))
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 text-xs">
                                                <i class="bi bi-geo-alt-fill me-1"></i> {{ trim($city) }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- System Farm Deals Settings -->
                        <div class="col-12 mt-4 pt-3 border-top">
                            <h5 class="font-serif fw-bold text-dark mb-1">
                                <i class="bi bi-tag-fill text-gold me-2"></i> Farm Deals &amp; Subscription Discounts
                            </h5>
                            <p class="text-muted small mb-3">Adjust standard deal discounts offered to buyers across all fresh dairy subscriptions.</p>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark text-sm">3 Days Trial Discount (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.5" name="deal_discount_3_days" class="form-control" value="{{ old('deal_discount_3_days', $dealDiscount3Days) }}" min="0" max="50" required>
                                        <span class="input-group-text bg-light">%</span>
                                    </div>
                                    <div class="form-text text-muted">Default: 5%</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark text-sm">1 Week Routine Discount (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.5" name="deal_discount_1_week" class="form-control" value="{{ old('deal_discount_1_week', $dealDiscount1Week) }}" min="0" max="50" required>
                                        <span class="input-group-text bg-light">%</span>
                                    </div>
                                    <div class="form-text text-muted">Default: 10%</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark text-sm">1 Month Family Discount (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.5" name="deal_discount_1_month" class="form-control" value="{{ old('deal_discount_1_month', $dealDiscount1Month) }}" min="0" max="50" required>
                                        <span class="input-group-text bg-light">%</span>
                                    </div>
                                    <div class="form-text text-muted">Default: 15%</div>
                                </div>
                            </div>
                        </div>

                        <!-- Real-time Online Bank & Mobile Accounts -->
                        <div class="col-12 mt-4 pt-4 border-top">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <h5 class="font-serif fw-bold text-dark mb-1">
                                        <i class="bi bi-bank2 text-success me-2"></i> Official Bank &amp; Mobile Wallet Accounts
                                    </h5>
                                    <p class="text-muted small mb-0">These bank account details will be presented to buyers during checkout for direct online transfers and proof verification.</p>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 text-xs">Real-Time Verification</span>
                            </div>

                            <div class="bg-light p-4 rounded-4 border mt-3">
                                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-building me-1"></i> Bank Account Information</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $bankName) }}" placeholder="e.g. Meezan Bank Ltd">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">Account Title</label>
                                        <input type="text" name="bank_account_title" class="form-control" value="{{ old('bank_account_title', $bankAccountTitle) }}" placeholder="e.g. Organic Dairy Gilgit-Baltistan">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">Account Number</label>
                                        <input type="text" name="bank_account_number" class="form-control font-monospace" value="{{ old('bank_account_number', $bankAccountNumber) }}" placeholder="01020304050607">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">IBAN Number</label>
                                        <input type="text" name="bank_iban" class="form-control font-monospace" value="{{ old('bank_iban', $bankIban) }}" placeholder="PK36MEZN0001020304050607">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-dark">Branch / City</label>
                                        <input type="text" name="bank_branch" class="form-control" value="{{ old('bank_branch', $bankBranch) }}" placeholder="e.g. Gilgit Main Branch, NPI Plaza">
                                    </div>
                                </div>

                                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-phone me-1"></i> Mobile Wallet Accounts (Easypaisa &amp; JazzCash)</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">Easypaisa Account Title</label>
                                        <input type="text" name="easypaisa_title" class="form-control" value="{{ old('easypaisa_title', $easypaisaTitle) }}" placeholder="e.g. Organic Dairy Gilgit">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">Easypaisa Mobile Number</label>
                                        <input type="text" name="easypaisa_number" class="form-control font-monospace" value="{{ old('easypaisa_number', $easypaisaNumber) }}" placeholder="0345-1234567">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">JazzCash Account Title</label>
                                        <input type="text" name="jazzcash_title" class="form-control" value="{{ old('jazzcash_title', $jazzcashTitle) }}" placeholder="e.g. Organic Dairy Gilgit">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">JazzCash Mobile Number</label>
                                        <input type="text" name="jazzcash_number" class="form-control font-monospace" value="{{ old('jazzcash_number', $jazzcashNumber) }}" placeholder="0300-7654321">
                                    </div>
                                </div>

                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle me-1"></i> Payment Instructions for Buyers</h6>
                                <div>
                                    <textarea name="payment_instructions" class="form-control" rows="3" placeholder="Enter instructions for buyers on how to pay and submit TRX ID...">{{ old('payment_instructions', $paymentInstructions) }}</textarea>
                                    <div class="form-text text-muted">This instruction text will appear on the buyer's payment screen.</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                        <button type="submit" class="btn btn-darkgreen btn-lg rounded-pill px-5 shadow-sm fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Save Platform Settings
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>
@endsection
