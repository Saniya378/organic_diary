<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}" href="{{ route('admin.sellers.index') }}">
        <i class="bi bi-person-check"></i> Approve Sellers
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
        <i class="bi bi-patch-check"></i> Approve Products
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
        <i class="bi bi-cart-check"></i> All Orders
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
        <i class="bi bi-people"></i> Users
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}">
        <i class="bi bi-credit-card"></i> Payments (Buyer)
    </a>
</li>
<li class="nav-item">
    <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.payouts.*') ? 'active' : '' }}" href="{{ route('admin.payouts.index') }}">
        <span><i class="bi bi-wallet2 me-1.5"></i> Seller Payouts</span>
        @php
            $pendingPayoutsCount = \App\Models\PayoutRequest::where('status', 'pending')->count();
        @endphp
        @if($pendingPayoutsCount > 0)
            <span class="badge bg-warning text-dark rounded-pill text-xs px-2">{{ $pendingPayoutsCount }}</span>
        @endif
    </a>
</li>
<li class="nav-item">
    <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.commissions.*') ? 'active' : '' }}" href="{{ route('admin.commissions.index') }}">
        <span><i class="bi bi-cash-stack me-1.5"></i> Commission Collections</span>
        @php
            $pendingCommissionCount = \App\Models\CommissionSettlement::where('status', 'pending')->count();
        @endphp
        @if($pendingCommissionCount > 0)
            <span class="badge bg-warning text-dark rounded-pill text-xs px-2">{{ $pendingCommissionCount }}</span>
        @endif
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
        <i class="bi bi-bar-chart-line"></i> Reports
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
        <i class="bi bi-tags"></i> Categories
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
        <i class="bi bi-sliders"></i> Settings
    </a>
</li>
