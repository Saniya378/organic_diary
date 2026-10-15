@extends('layouts.public')

@section('title', 'Shopping Basket & Cold Delivery')

@section('content')
<div class="container py-4 py-lg-5">
    <!-- Breadcrumb & Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-success text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('shop.index') }}" class="text-success text-decoration-none">Shop</a></li>
                <li class="breadcrumb-item active" aria-current="page">Shopping Basket</li>
            </ol>
        </nav>
        <h2 class="heading-serif mb-1">Your Dairy Basket</h2>
        <p class="text-muted small mb-0">Fresh high-altitude organic dairy sourced directly from Gilgit-Baltistan herders.</p>
    </div>

    <!-- Active Cart Container -->
    <div id="activeCartSection" class="{{ ($is_empty ?? true) ? 'd-none' : '' }}">
        <div class="row g-4">
            <!-- Items Table (Left/Main Column) -->
            <div class="col-lg-8">
                <div class="card p-3 p-md-4 shadow-sm border-0 mb-3">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light small text-uppercase" style="letter-spacing: 0.5px;">
                                <tr>
                                    <th scope="col" style="min-width: 240px;">Product & Farm</th>
                                    <th scope="col" class="text-center">Price</th>
                                    <th scope="col" class="text-center" style="min-width: 140px;">Quantity</th>
                                    <th scope="col" class="text-end">Total</th>
                                    <th scope="col" class="text-center" style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="cartTableBody">
                                @if(!empty($items))
                                    @foreach($items as $item)
                                        @php
                                            $product = $item->product;
                                            $variant = $item->variant;
                                            $unitPrice = $item->unit_price;
                                            $lineTotal = $item->line_total;
                                            $maxStock = $variant ? $variant->stock : $product->stock;
                                        @endphp
                                        <tr id="cartRow-{{ $item->id }}">
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-light border flex-shrink-0" style="width: 60px; height: 60px; overflow: hidden;">
                                                        @if($product->primary_image)
                                                            <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null; this.src='{{ asset('images/products/fresh_milk.jpg') }}';">
                                                        @else
                                                            <i class="bi bi-egg-fried fs-4 text-success"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="heading-serif mb-0">
                                                            <a href="{{ route('shop.show', $product->slug) }}" class="text-dark text-decoration-none hover-gold">
                                                                {{ $product->name }}
                                                            </a>
                                                        </h6>
                                                        @if($variant)
                                                            <span class="badge badge-status-pending text-dark small">{{ $variant->label }}</span>
                                                        @else
                                                            <small class="text-muted">{{ $product->unit }}</small>
                                                        @endif
                                                        <div class="text-muted" style="font-size: 0.75rem;">
                                                            <i class="bi bi-geo-alt text-danger me-1"></i>{{ $product->seller->sellerProfile->farm_name ?? 'Mountain Farm' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Unit Price -->
                                            <td class="text-center">
                                                <span class="fw-semibold text-dark">{{ $item->formatted_unit_price ?? ('Rs ' . number_format($unitPrice)) }}</span>
                                            </td>

                                            <!-- Quantity Stepper -->
                                            <td class="text-center">
                                                <div class="input-group input-group-sm mx-auto" style="max-width: 120px;">
                                                    <button class="btn btn-outline-custom" type="button" onclick="stepCartQty({{ $item->id }}, -1)">
                                                        <i class="bi bi-dash"></i>
                                                    </button>
                                                    <input type="number" 
                                                           id="qtyInput-{{ $item->id }}" 
                                                           class="form-control text-center fw-bold px-1" 
                                                           value="{{ $item->quantity }}" 
                                                           min="1" 
                                                           max="{{ max(1, $maxStock) }}" 
                                                           onchange="onQtyInputChange({{ $item->id }}, this.value)">
                                                    <button class="btn btn-outline-custom" type="button" onclick="stepCartQty({{ $item->id }}, 1)">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Stock: {{ $maxStock }}</small>
                                            </td>

                                            <!-- Line Total -->
                                            <td class="text-end">
                                                <strong id="lineTotal-{{ $item->id }}" class="heading-serif fs-6 text-success">
                                                    {{ $item->formatted_line_total ?? ('Rs ' . number_format($lineTotal)) }}
                                                </strong>
                                            </td>

                                            <!-- Remove Action -->
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2 rounded-circle" onclick="removeCartLineItem({{ $item->id }})" title="Remove item">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('shop.index') }}" class="btn btn-outline-darkgreen">
                        <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Order Summary Card (Right Column) -->
            <div class="col-lg-4">
                <div class="card p-4 shadow-sm border-0 sticky-top" style="top: 100px;">
                    <h5 class="heading-serif mb-3">Order Summary</h5>

                    <div class="d-flex flex-column gap-2 small pb-3 border-bottom">
                        <div class="d-flex justify-content-between text-muted">
                            <span>Basket Subtotal:</span>
                            <strong id="summarySubtotal" class="text-dark">{{ $formatted_subtotal ?? 'Rs 0' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between text-muted">
                            <span>Delivery Fee (GB Flat Rate):</span>
                            <strong id="summaryDeliveryFee" class="text-dark">{{ $formatted_delivery_fee ?? 'Rs 150' }}</strong>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-baseline my-3">
                        <span class="fw-bold text-dark fs-6">Grand Total:</span>
                        <span id="summaryGrandTotal" class="display-6 heading-serif fw-bold text-success fs-3">{{ $formatted_grand_total ?? 'Rs 0' }}</span>
                    </div>

                    <div class="card bg-light border p-3 rounded-4 small mb-4 text-muted">
                        <div class="d-flex align-items-center gap-2 mb-1 text-dark fw-bold">
                            <i class="bi bi-snow text-primary"></i> Cold-Chain Dispatch
                        </div>
                        Packaged in insulated thermal boxes directly from local valleys with same-day morning routing.
                    </div>

                    @auth
                        <a href="{{ route('checkout.index') }}" class="btn btn-primary w-100 py-2 fs-6 shadow">
                            Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    @else
                        <a href="{{ route('login', ['redirect' => route('checkout.index')]) }}" class="btn btn-primary w-100 py-2 fs-6 shadow">
                            Login & Checkout <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State Container -->
    <div id="emptyCartSection" class="{{ !($is_empty ?? true) ? 'd-none' : '' }}">
        <div class="card p-5 text-center shadow-sm border-0 my-4">
            <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center text-muted mb-3" style="width: 80px; height: 80px; background-color: #FAF7F0;">
                <i class="bi bi-bag-x fs-1 text-warning"></i>
            </div>
            <h3 class="heading-serif mb-2">Your Dairy Basket is Empty</h3>
            <p class="text-muted small mx-auto mb-4" style="max-width: 440px;">
                You have no organic dairy products in your basket. Browse pure mountain milk, slow-churned bilona ghee, cultured butter, and raw cheeses straight from Gilgit-Baltistan herders.
            </p>
            <div>
                <a href="{{ route('shop.index') }}" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-shop me-1"></i> Explore Dairy Marketplace
                </a>
            </div>
        </div>
    </div>
</div>

<!-- AJAX Stepper & Cart Update Scripts -->
<script>
    async function stepCartQty(itemId, change) {
        const input = document.getElementById(`qtyInput-${itemId}`);
        if (!input) return;

        let currentVal = parseInt(input.value) || 1;
        let newVal = currentVal + change;
        if (newVal < 1) newVal = 1;

        input.value = newVal;
        await sendCartUpdate(itemId, newVal);
    }

    async function onQtyInputChange(itemId, val) {
        let quantity = parseInt(val) || 1;
        if (quantity < 1) quantity = 1;
        document.getElementById(`qtyInput-${itemId}`).value = quantity;
        await sendCartUpdate(itemId, quantity);
    }

    async function sendCartUpdate(itemId, quantity) {
        try {
            const response = await fetch(`/cart/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ quantity: quantity })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Update line total and quantity
                const lineTotalEl = document.getElementById(`lineTotal-${itemId}`);
                if (lineTotalEl) lineTotalEl.innerText = data.formatted_line_total;

                const qtyInput = document.getElementById(`qtyInput-${itemId}`);
                if (qtyInput) qtyInput.value = data.quantity;

                // Update summary totals
                updateOrderSummary(data);
                window.updateCartBadges(data.cart_count);

                if (data.is_capped) {
                    window.showToast(data.message, false);
                }
            } else {
                window.showToast(data.message || 'Could not update item quantity.', false);
            }
        } catch (err) {
            console.error('Cart update error:', err);
            window.showToast('Network error while updating cart.', false);
        }
    }

    async function removeCartLineItem(itemId) {
        if (!confirm('Are you sure you want to remove this item from your basket?')) {
            return;
        }

        try {
            const response = await fetch(`/cart/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Remove row from DOM
                const row = document.getElementById(`cartRow-${itemId}`);
                if (row) row.remove();

                window.updateCartBadges(data.cart_count);
                updateOrderSummary(data);
                window.showToast(data.message, true);

                if (data.is_empty) {
                    document.getElementById('activeCartSection').classList.add('d-none');
                    document.getElementById('emptyCartSection').classList.remove('d-none');
                }
            } else {
                window.showToast(data.message || 'Could not remove item.', false);
            }
        } catch (err) {
            console.error('Cart remove error:', err);
            window.showToast('Network error while removing item.', false);
        }
    }

    function updateOrderSummary(data) {
        const subtotalEl = document.getElementById('summarySubtotal');
        const deliveryEl = document.getElementById('summaryDeliveryFee');
        const grandTotalEl = document.getElementById('summaryGrandTotal');

        if (subtotalEl) subtotalEl.innerText = data.formatted_subtotal;
        if (deliveryEl) deliveryEl.innerText = data.formatted_delivery_fee;
        if (grandTotalEl) grandTotalEl.innerText = data.formatted_grand_total;
    }
</script>
@endsection
