<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}" href="{{ route('seller.dashboard') }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('seller.products.*') ? 'active' : '' }}" href="{{ route('seller.products.index') }}">
        <i class="bi bi-box-seam"></i> My Products
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}" href="{{ route('seller.orders.index') }}">
        <i class="bi bi-cart-check"></i> Orders
    </a>
</li>
<li class="nav-item">
    <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('seller.payments.*') ? 'active' : '' }}" href="{{ route('seller.payments.index') }}">
        <span><i class="bi bi-credit-card-2-front me-1.5"></i> Buyer Payments</span>
        @php
            $sellerPendingPaymentsCount = \App\Models\Order::where('seller_id', Auth::id())
                ->where('payment_status', 'under_review')
                ->count();
        @endphp
        @if($sellerPendingPaymentsCount > 0)
            <span class="badge bg-warning text-dark rounded-pill text-xs px-2 fw-bold">{{ $sellerPendingPaymentsCount }}</span>
        @endif
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('seller.deals.*') ? 'active' : '' }}" href="{{ route('seller.deals.index') }}">
        <i class="bi bi-calendar2-check"></i> Farm Deals
    </a>
</li>
<li class="nav-item">
    <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('seller.inventory.*') ? 'active' : '' }}" href="{{ route('seller.inventory.index') }}">
        <span><i class="bi bi-stack me-1.5"></i> Inventory &amp; Stock</span>
        @php
            $sellerLowStockThreshold = (int) \App\Models\Setting::get('low_stock_threshold', 10);
            $sellerLowStockCount = \App\Models\Product::where('seller_id', Auth::id())
                ->where('stock', '<=', $sellerLowStockThreshold)
                ->count();
        @endphp
        @if($sellerLowStockCount > 0)
            <span class="badge bg-danger text-white rounded-pill text-xs px-2" title="{{ $sellerLowStockCount }} items low in stock">{{ $sellerLowStockCount }}</span>
        @endif
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('seller.earnings.*') ? 'active' : '' }}" href="{{ route('seller.earnings.index') }}">
        <i class="bi bi-graph-up-arrow"></i> Sales Analytics
    </a>
</li>
<li class="nav-item">
    <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('seller.commissions.*') ? 'active' : '' }}" href="{{ route('seller.commissions.index') }}">
        <span><i class="bi bi-cash-coin me-1.5"></i> Admin Commission</span>
        @php
            $sellerDueCommission = Auth::user()->outstanding_commission_due;
        @endphp
        @if($sellerDueCommission > 0)
            <span class="badge bg-danger text-white rounded-pill text-xs px-2" title="Outstanding Due: Rs {{ number_format($sellerDueCommission) }}">Rs {{ number_format($sellerDueCommission) }}</span>
        @endif
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('seller.payouts.*') ? 'active' : '' }}" href="{{ route('seller.payouts.index') }}">
        <i class="bi bi-wallet2"></i> Payouts &amp; Wallet
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('seller.profile.*') ? 'active' : '' }}" href="{{ route('seller.profile.edit') }}">
        <i class="bi bi-person-gear"></i> Farm Profile
    </a>
</li>
