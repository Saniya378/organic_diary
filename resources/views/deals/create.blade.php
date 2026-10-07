@extends('layouts.public')

@section('title', 'Configure Deal - ' . $product->name)

@section('content')
<div class="py-4" style="background-color: var(--od-bg); min-height: 80vh;">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('deals.index') }}" class="text-decoration-none text-muted">Farm Deals</a></li>
                <li class="breadcrumb-item active" aria-current="page">Subscribe to {{ $product->name }}</li>
            </ol>
        </nav>

        <form action="{{ route('deals.store') }}" method="POST" id="dealForm">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">

            <div class="row g-4">
                <!-- Left: Deal Configuration -->
                <div class="col-lg-8">
                    <!-- 1. Select Deal Duration -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 28px; height: 28px; background-color: var(--od-primary);">1</span>
                            <h5 class="heading-serif mb-0">Select Your Deal Duration</h5>
                        </div>

                        <div class="row g-3">
                            <!-- 3 Days Deal -->
                            <div class="col-md-4">
                                <label class="card h-100 p-3 rounded-4 border-2 cursor-pointer transition-hover deal-plan-card text-center position-relative {{ $initialDuration === '3_days' ? 'border-primary bg-light' : 'border' }}">
                                    <input type="radio" name="duration_type" value="3_days" class="d-none" data-days="3" data-discount="5" {{ $initialDuration === '3_days' ? 'checked' : '' }} onchange="updateDealCalculations()">
                                    <span class="badge bg-success position-absolute top-0 end-0 m-2">5% OFF</span>
                                    <i class="bi bi-calendar-check fs-2 text-dark-green mb-2 d-block"></i>
                                    <h6 class="fw-bold mb-1">3 Days Deal</h6>
                                    <small class="text-muted d-block mb-2">Trial Pack</small>
                                    <span class="badge bg-light text-dark border">3 Morning Deliveries</span>
                                </label>
                            </div>

                            <!-- 1 Week Deal -->
                            <div class="col-md-4">
                                <label class="card h-100 p-3 rounded-4 border-2 cursor-pointer transition-hover deal-plan-card text-center position-relative {{ $initialDuration === '1_week' ? 'border-primary bg-light' : 'border' }}">
                                    <input type="radio" name="duration_type" value="1_week" class="d-none" data-days="7" data-discount="10" {{ $initialDuration === '1_week' ? 'checked' : '' }} onchange="updateDealCalculations()">
                                    <span class="badge bg-warning text-dark fw-bold position-absolute top-0 end-0 m-2">10% OFF</span>
                                    <i class="bi bi-calendar-week fs-2 text-gold mb-2 d-block"></i>
                                    <h6 class="fw-bold mb-1">1 Week Deal</h6>
                                    <small class="text-muted d-block mb-2">Weekly Routine</small>
                                    <span class="badge bg-light text-dark border">7 Days Continuous</span>
                                </label>
                            </div>

                            <!-- 1 Month Deal -->
                            <div class="col-md-4">
                                <label class="card h-100 p-3 rounded-4 border-2 cursor-pointer transition-hover deal-plan-card text-center position-relative {{ $initialDuration === '1_month' ? 'border-primary bg-light' : 'border' }}">
                                    <input type="radio" name="duration_type" value="1_month" class="d-none" data-days="30" data-discount="15" {{ $initialDuration === '1_month' ? 'checked' : '' }} onchange="updateDealCalculations()">
                                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">15% OFF</span>
                                    <i class="bi bi-calendar-month fs-2 text-dark-green mb-2 d-block"></i>
                                    <h6 class="fw-bold mb-1">1 Month Deal</h6>
                                    <small class="text-muted d-block mb-2">Family Supply</small>
                                    <span class="badge bg-light text-dark border">30 Days Supply</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Delivery Preferences -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 28px; height: 28px; background-color: var(--od-primary);">2</span>
                            <h5 class="heading-serif mb-0">Delivery Preferences</h5>
                        </div>

                        <div class="row g-3">
                            <!-- Daily Quantity -->
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Daily Quantity (per drop)</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(-1)"><i class="bi bi-dash"></i></button>
                                    <input type="number" id="qtyInput" name="quantity_per_day" value="1" min="1" max="20" class="form-control text-center fw-bold" readonly onchange="updateDealCalculations()">
                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(1)"><i class="bi bi-plus"></i></button>
                                </div>
                                <small class="text-muted" id="unitText">{{ $product->unit }} per day</small>
                            </div>

                            <!-- Preferred Slot -->
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Preferred Delivery Slot</label>
                                <select name="delivery_slot" class="form-select">
                                    <option value="morning" selected>Morning (6:00 AM - 8:00 AM)</option>
                                    <option value="evening">Evening (5:00 PM - 7:00 PM)</option>
                                </select>
                                <small class="text-muted">Direct doorstep drop</small>
                            </div>

                            <!-- Start Date -->
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Start Delivery Date</label>
                                <input type="date" name="start_date" id="startDate" class="form-control" value="{{ \Carbon\Carbon::tomorrow()->toDateString() }}" min="{{ \Carbon\Carbon::today()->toDateString() }}">
                                <small class="text-muted">First drop-off date</small>
                            </div>
                        </div>

                        <!-- Special Instructions -->
                        <div class="mt-3">
                            <label class="form-label small fw-bold text-muted">Gate / Rider Instructions (Optional)</label>
                            <input type="text" name="special_instructions" class="form-control" placeholder="e.g., Leave bottle inside the front porch basket, ring bell once.">
                        </div>
                    </div>

                    <!-- 3. Delivery Address -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 28px; height: 28px; background-color: var(--od-primary);">3</span>
                            <h5 class="heading-serif mb-0">Delivery Address</h5>
                        </div>

                        @if($addresses->isNotEmpty())
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Choose Saved Address</label>
                                <select name="address_id" id="addressSelect" class="form-select" onchange="toggleNewAddressFields(this)">
                                    @foreach($addresses as $addr)
                                        <option value="{{ $addr->id }}" {{ $addr->is_default ? 'selected' : '' }}>
                                            {{ $addr->full_name }} - {{ $addr->address_line }}, {{ $addr->area }}, {{ $addr->city }} ({{ $addr->phone }})
                                        </option>
                                    @endforeach
                                    <option value="">+ Enter a new delivery address</option>
                                </select>
                            </div>
                        @endif

                        <div id="newAddressFields" style="{{ $addresses->isNotEmpty() ? 'display: none;' : '' }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Contact Name</label>
                                    <input type="text" name="full_name" class="form-control" value="{{ Auth::user()?->name }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="{{ Auth::user()?->phone }}" placeholder="0355xxxxxxx">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">City</label>
                                    <select name="city" class="form-select">
                                        <option value="Gilgit">Gilgit</option>
                                        <option value="Skardu">Skardu</option>
                                        <option value="Hunza">Hunza</option>
                                        <option value="Ghizer">Ghizer</option>
                                        <option value="Astore">Astore</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Area / Sector</label>
                                    <input type="text" name="area" class="form-control" placeholder="e.g. Jutial / Sadpara Road">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">Full Address Line</label>
                                    <input type="text" name="address_line" class="form-control" placeholder="House/Street/Apartment details">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Payment Preference -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 28px; height: 28px; background-color: var(--od-primary);">4</span>
                            <h5 class="heading-serif mb-0">Payment Method</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="card p-3 rounded-4 border-2 cursor-pointer transition-hover h-100">
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="radio" name="payment_method" value="cod" checked class="form-check-input">
                                        <div>
                                            <h6 class="fw-bold mb-0"><i class="bi bi-cash-stack text-success me-1"></i> Cash on Delivery</h6>
                                            <small class="text-muted">Pay the farm rider daily or weekly at your door.</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="card p-3 rounded-4 border-2 cursor-pointer transition-hover h-100">
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="radio" name="payment_method" value="card" class="form-check-input">
                                        <div>
                                            <h6 class="fw-bold mb-0"><i class="bi bi-credit-card-2-front text-primary me-1"></i> Card / Digital Simulator</h6>
                                            <small class="text-muted">Pre-pay upfront for the entire deal duration.</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Deal Order Summary -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 100px;">
                        <h5 class="heading-serif border-bottom pb-3 mb-3">Deal Summary</h5>

                        <!-- Product Pill -->
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="rounded-3 overflow-hidden border" style="width: 60px; height: 60px; min-width: 60px;">
                                @if($product->primary_image)
                                    <img src="{{ asset('storage/' . $product->primary_image) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <i class="bi bi-egg-fried fs-2 text-muted"></i>
                                @endif
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">{{ $product->name }}</h6>
                                <small class="text-muted d-block">By {{ $product->seller->sellerProfile->farm_name ?? 'Farm' }}</small>
                                <span class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 0.7rem;">100% Organic</span>
                            </div>
                        </div>

                        <!-- Calculation Breakdown -->
                        <div class="small">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Base Daily Price:</span>
                                <span class="fw-bold">Rs {{ number_format($product->price) }} / {{ $product->unit }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Duration Plan:</span>
                                <span class="fw-bold" id="summaryPlanLabel">1 Week Deal (7 Days)</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Daily Quantity:</span>
                                <span class="fw-bold" id="summaryQty">1</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Scheduled Drops:</span>
                                <span class="fw-bold" id="summaryTotalDays">7 Deliveries</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <span id="summarySubtotal">Rs 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 text-success fw-bold">
                                <span>Deal Discount (<span id="summaryDiscountPct">10</span>%):</span>
                                <span id="summaryDiscountAmount">-Rs 0</span>
                            </div>

                            <div class="p-3 rounded-3 mb-4 bg-light border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark fs-6">Net Payable:</span>
                                    <span class="fs-4 fw-bold text-dark-green font-serif" id="summaryTotal">Rs 0</span>
                                </div>
                                <small class="text-muted d-block text-end" id="summarySavings">You save Rs 0</small>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        @auth
                            <button type="submit" class="btn btn-primary rounded-pill w-100 py-3 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check2-circle fs-5"></i> Activate Deal Now
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary rounded-pill w-100 py-3 fw-bold shadow-sm text-center">
                                Login to Activate Deal
                            </a>
                        @endauth

                        <p class="text-muted small text-center mt-3 mb-0">
                            <i class="bi bi-shield-check text-success me-1"></i> Pause or cancel your deal deliveries anytime with 1 click.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const baseUnitPrice = {{ (int) $product->price }};

    function adjustQty(delta) {
        const input = document.getElementById('qtyInput');
        let current = parseInt(input.value) || 1;
        current = Math.max(1, Math.min(20, current + delta));
        input.value = current;
        updateDealCalculations();
    }

    function toggleNewAddressFields(select) {
        const fields = document.getElementById('newAddressFields');
        if (fields) {
            fields.style.display = select.value === '' ? 'block' : 'none';
        }
    }

    function updateDealCalculations() {
        const selectedRadio = document.querySelector('input[name="duration_type"]:checked');
        const days = parseInt(selectedRadio.getAttribute('data-days')) || 7;
        const discountPct = parseInt(selectedRadio.getAttribute('data-discount')) || 10;
        const qty = parseInt(document.getElementById('qtyInput').value) || 1;

        // Visual highlight for plan cards
        document.querySelectorAll('.deal-plan-card').forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                card.classList.add('border-primary', 'bg-light');
                card.classList.remove('border');
            } else {
                card.classList.remove('border-primary', 'bg-light');
                card.classList.add('border');
            }
        });

        // Calculation
        const subtotal = baseUnitPrice * qty * days;
        const discountAmount = Math.round((subtotal * discountPct) / 100);
        const finalTotal = subtotal - discountAmount;

        // Labels
        const labels = {
            3: '3 Days Trial Deal (3 Days)',
            7: '1 Week Routine Deal (7 Days)',
            30: '1 Month Family Supply (30 Days)'
        };

        document.getElementById('summaryPlanLabel').textContent = labels[days] || (days + ' Days Deal');
        document.getElementById('summaryQty').textContent = qty;
        document.getElementById('summaryTotalDays').textContent = days + ' Deliveries';
        document.getElementById('summarySubtotal').textContent = 'Rs ' + subtotal.toLocaleString();
        document.getElementById('summaryDiscountPct').textContent = discountPct;
        document.getElementById('summaryDiscountAmount').textContent = '-Rs ' + discountAmount.toLocaleString();
        document.getElementById('summaryTotal').textContent = 'Rs ' + finalTotal.toLocaleString();
        document.getElementById('summarySavings').textContent = 'You save Rs ' + discountAmount.toLocaleString() + ' with this deal!';
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateDealCalculations();
    });
</script>
@endsection
