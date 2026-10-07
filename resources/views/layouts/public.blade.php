<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Organic Dairy') | Gilgit-Baltistan Multi-Vendor Marketplace</title>
    <meta name="description" content="@yield('meta_description', 'Pure, natural, farm-fresh organic dairy products sourced directly from the mountain pastures of Gilgit-Baltistan, Pakistan.')">

    <!-- Google Fonts & Styles -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Top Announcement Bar -->
    <div class="py-2 text-white text-center small fw-semibold" style="background-color: var(--od-dark-green); border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div class="container d-flex justify-content-between align-items-center">
            <span><i class="bi bi-geo-alt-fill text-warning me-1"></i> Serving Gilgit, Skardu, Hunza, Ghizer, Astore & Nagar</span>
            <div class="d-none d-md-flex align-items-center gap-3">
                <span><i class="bi bi-shield-check text-warning me-1"></i> 100% Certified Organic & Fresh</span>
                <span class="text-white-50">|</span>
                <a href="{{ route('track-order.form') }}" class="text-white text-decoration-none hover-gold"><i class="bi bi-truck me-1"></i> Track Order</a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg sticky-top navbar-light bg-white shadow-sm py-3" style="border-bottom: 1px solid var(--od-border);">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background-color: var(--od-primary);">
                    <i class="bi bi-flower1 fs-4"></i>
                </div>
                <div>
                    <span class="d-block heading-serif fs-4 lh-1" style="color: var(--od-dark-green); letter-spacing: -0.5px;">Organic Dairy</span>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 1.5px; color: var(--od-gold) !important;">Gilgit-Baltistan</small>
                </div>
            </a>

            <!-- Mobile Action Buttons (Search & Cart & Notifications & Toggle) -->
            <div class="d-flex align-items-center gap-2 d-lg-none">
                @include('partials.notifications-dropdown')
                <a href="{{ route('cart.index') }}" class="btn btn-outline-custom position-relative p-2 px-3">
                    <i class="bi bi-bag fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill badge-status-new live-cart-count" style="font-size: 0.7rem;">
                        {{ $cartCount ?? 0 }}
                    </span>
                </a>
                <button class="navbar-toggler border-0 p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMainCollapse" aria-controls="navbarMainCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="bi bi-list fs-2 text-dark"></i>
                </button>
            </div>

            <!-- Navbar Links and Search -->
            <div class="collapse navbar-collapse" id="navbarMainCollapse">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fw-semibold gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active text-success fw-bold' : '' }}" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('shop*') ? 'active text-success fw-bold' : '' }}" href="{{ route('shop.index') }}">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative {{ request()->is('deals*') ? 'active text-success fw-bold' : '' }}" href="{{ route('deals.index') }}">
                            Farm Deals
                            <span class="badge rounded-pill bg-danger text-white ms-1" style="font-size: 0.65rem;">15% Off</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('farmers*') ? 'active text-success fw-bold' : '' }}" href="{{ route('farmers.index') }}">Farmers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('track-order*') ? 'active text-success fw-bold' : '' }}" href="{{ route('track-order.form') }}">Track Order</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('about*') ? 'active text-success fw-bold' : '' }}" href="{{ route('about') }}">About</a>
                    </li>
                </ul>

                <!-- Search Form -->
                <form action="{{ route('shop.index') }}" method="GET" class="d-flex me-lg-3 my-2 my-lg-0 position-relative" style="min-width: 240px;">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-start-0 rounded-end-pill ps-0 text-sm live-search-input" placeholder="Search milk, ghee, cheese..." aria-label="Search" autocomplete="off">
                    </div>
                </form>

                <!-- User & Cart Controls (Desktop) -->
                <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
                    <!-- Notifications Dropdown -->
                    @include('partials.notifications-dropdown')

                    @auth
                        <div class="dropdown">
                            <button class="btn btn-outline-darkgreen dropdown-toggle d-flex align-items-center gap-2 py-1 px-2.5 rounded-pill shadow-sm" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="rounded-circle overflow-hidden border border-1 border-white flex-shrink-0" style="width: 28px; height: 28px; background-color: var(--od-primary);">
                                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-100 h-100 object-fit-cover">
                                </div>
                                <span class="d-none d-xl-inline fw-semibold">{{ Str::limit(Auth::user()->name, 14) }}</span>
                                <span class="badge {{ Auth::user()->isAdmin() ? 'badge-status-new' : (Auth::user()->isSeller() ? 'badge-status-pending' : 'badge-status-ok') }} text-capitalize ms-1" style="font-size: 0.7rem;">
                                    {{ Auth::user()->role }}
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2 rounded-3" aria-labelledby="userMenuDropdown" style="min-width: 220px; border: 1px solid var(--od-border) !important;">
                                <li class="px-3 py-2 border-bottom d-flex align-items-center gap-2">
                                    <div class="rounded-circle overflow-hidden border flex-shrink-0" style="width: 38px; height: 38px; background-color: var(--od-primary);">
                                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-100 h-100 object-fit-cover">
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-dark text-truncate">{{ Auth::user()->name }}</div>
                                        <small class="text-muted text-truncate d-block">{{ Auth::user()->email }}</small>
                                    </div>
                                </li>
                                @if(Auth::user()->isAdmin())
                                    <li><a class="dropdown-item py-2 fw-semibold" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 text-danger me-2"></i> Admin Panel</a></li>
                                @elseif(Auth::user()->isSeller())
                                    <li><a class="dropdown-item py-2 fw-semibold" href="{{ route('seller.dashboard') }}"><i class="bi bi-shop text-warning me-2"></i> Seller Dashboard</a></li>
                                @endif
                                <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}"><i class="bi bi-person text-success me-2"></i> My Profile</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('addresses.index') }}"><i class="bi bi-geo-alt text-primary me-2"></i> Address Book</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('orders.index') }}"><i class="bi bi-box-seam text-info me-2"></i> My Orders</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('notifications.index') }}"><i class="bi bi-bell text-warning me-2"></i> Notifications</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('buyer.deals.index') }}"><i class="bi bi-calendar2-check text-warning me-2"></i> My Deals / Subscriptions</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger fw-semibold"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-darkgreen">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            Register
                        </a>
                    @endauth

                    <!-- Cart Icon Desktop -->
                    <a href="{{ route('cart.index') }}" class="btn btn-outline-custom position-relative p-2 px-3 d-none d-lg-inline-flex align-items-center" title="Shopping Cart">
                        <i class="bi bi-bag fs-5 text-dark"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill badge-status-new live-cart-count" style="font-size: 0.72rem;">
                            {{ $cartCount ?? 0 }}
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Global Flash Alerts Container -->
    <div class="container my-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 d-flex align-items-center gap-2 border-0 shadow-sm" role="alert" style="background-color: #EBF5EE; color: #2F5D3A;">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('status'))
            <div class="alert alert-warning alert-dismissible fade show rounded-4 d-flex align-items-center gap-2 border-0 shadow-sm" role="alert" style="background-color: #FEF8E7; color: #9C6B08;">
                <i class="bi bi-info-circle-fill fs-5"></i>
                <div>{{ session('status') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4 d-flex align-items-center gap-2 border-0 shadow-sm" role="alert" style="background-color: #FDF0EE; color: #C53030;">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert" style="background-color: #FDF0EE; color: #C53030;">
                <div class="d-flex align-items-center gap-2 mb-2 fw-bold">
                    <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                    <span>Please fix the following issues:</span>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="text-white mt-auto pt-5 pb-4" style="background-color: var(--od-dark-green); border-top: 3px solid var(--od-gold);">
        <div class="container">
            <div class="row g-4 pb-4 border-bottom border-secondary border-opacity-25">
                <!-- Column 1: Brand & Mission -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-dark" style="width: 40px; height: 40px; background-color: var(--od-gold);">
                            <i class="bi bi-flower1 fs-4"></i>
                        </div>
                        <h4 class="heading-serif text-white mb-0">Organic Dairy</h4>
                    </div>
                    <p class="text-white-50 small pe-lg-3">
                        Gilgit-Baltistan's first decentralized multi-vendor organic dairy marketplace. Connecting native high-pasture herders from Hunza, Skardu, and Ghizer directly to your kitchen.
                    </p>
                    <div class="d-flex gap-2 mt-3">
                        <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 rounded-pill px-3 py-2"><i class="bi bi-check2-circle text-warning me-1"></i> Grass Fed</span>
                        <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 rounded-pill px-3 py-2"><i class="bi bi-snow text-warning me-1"></i> Cold Chain</span>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="heading-serif text-white mb-3" style="color: var(--od-gold) !important;">Marketplace Links</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><a href="{{ route('shop.index') }}" class="text-white-50 text-decoration-none hover-gold"><i class="bi bi-chevron-right text-warning me-1 small"></i> Browse All Dairy Products</a></li>
                        <li><a href="{{ route('shop.index', ['category' => 'ghee']) }}" class="text-white-50 text-decoration-none hover-gold"><i class="bi bi-chevron-right text-warning me-1 small"></i> Pure Desi & Yak Ghee</a></li>
                        <li><a href="{{ route('shop.index', ['category' => 'butter']) }}" class="text-white-50 text-decoration-none hover-gold"><i class="bi bi-chevron-right text-warning me-1 small"></i> Churned Mountain Butter</a></li>
                        <li><a href="{{ route('farmers.index') }}" class="text-white-50 text-decoration-none hover-gold"><i class="bi bi-chevron-right text-warning me-1 small"></i> Certified Organic Farmers</a></li>
                        <li><a href="{{ route('track-order.form') }}" class="text-white-50 text-decoration-none hover-gold"><i class="bi bi-chevron-right text-warning me-1 small"></i> Live Order Tracking</a></li>
                        <li><a href="{{ route('register') }}" class="text-white-50 text-decoration-none hover-gold"><i class="bi bi-chevron-right text-warning me-1 small"></i> Register as Farm Seller</a></li>
                    </ul>
                </div>

                <!-- Column 3: Regional Coverage & Support -->
                <div class="col-lg-4 col-md-12">
                    <h5 class="heading-serif text-white mb-3" style="color: var(--od-gold) !important;">GB Coverage & Support</h5>
                    <p class="text-white-50 small mb-2">
                        Currently delivering fresh morning batches across:
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-dark bg-opacity-50 text-white-50 border border-secondary border-opacity-50">Gilgit</span>
                        <span class="badge bg-dark bg-opacity-50 text-white-50 border border-secondary border-opacity-50">Skardu</span>
                        <span class="badge bg-dark bg-opacity-50 text-white-50 border border-secondary border-opacity-50">Hunza</span>
                        <span class="badge bg-dark bg-opacity-50 text-white-50 border border-secondary border-opacity-50">Ghizer</span>
                        <span class="badge bg-dark bg-opacity-50 text-white-50 border border-secondary border-opacity-50">Astore</span>
                        <span class="badge bg-dark bg-opacity-50 text-white-50 border border-secondary border-opacity-50">Nagar</span>
                    </div>
                    <div class="small text-white-50 d-flex flex-column gap-1.5">
                        <div><i class="bi bi-envelope text-warning me-2"></i> salam@organicdairy.test</div>
                        <div><i class="bi bi-telephone text-warning me-2"></i> +92 5811 455000 (Gilgit Hub)</div>
                        <div>
                            <a href="https://wa.me/923555123456?text={{ rawurlencode('Hello Organic Dairy GB! I would like to inquire about fresh mountain dairy products and orders.') }}" target="_blank" rel="noopener noreferrer" class="text-white text-decoration-none hover-gold d-inline-flex align-items-center">
                                <i class="bi bi-whatsapp text-success me-2 fs-6"></i> +92 355 5123456 (WhatsApp Orders)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pt-3 small text-white-50">
                <div>&copy; {{ date('Y') }} Organic Dairy Marketplace. All rights reserved. Gilgit-Baltistan, Pakistan.</div>
                <div class="mt-2 mt-md-0">
                    <span class="text-warning">Flat Rate Delivery: Rs 150</span> &bull; 100% Cash on Delivery & Digital Payments
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Notification Container (Floating Bottom-Right) -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <div id="cartToast" class="toast align-items-center border-0 shadow-lg rounded-4" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex p-2">
                <div class="toast-body d-flex align-items-center gap-2 fw-semibold" id="cartToastBody">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <span id="cartToastMessage">Item added to cart</span>
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Global Cart Client-side Fetch & LocalStorage Sync Script -->
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const IS_AUTHENTICATED = {{ Auth::check() ? 'true' : 'false' }};

        // Show interactive Toast notification
        window.showToast = function(message, isSuccess = true) {
            const toastEl = document.getElementById('cartToast');
            const toastMsgEl = document.getElementById('cartToastMessage');
            const toastBodyEl = document.getElementById('cartToastBody');

            if (!toastEl) return;

            toastMsgEl.innerText = message;
            if (isSuccess) {
                toastEl.style.backgroundColor = '#EBF5EE';
                toastEl.style.color = '#2F5D3A';
                toastBodyEl.querySelector('i').className = 'bi bi-check-circle-fill fs-5 text-success';
            } else {
                toastEl.style.backgroundColor = '#FDF0EE';
                toastEl.style.color = '#C53030';
                toastBodyEl.querySelector('i').className = 'bi bi-exclamation-triangle-fill fs-5 text-danger';
            }

            const bsToast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3500 });
            bsToast.show();
        };

        // Update all live cart count badges in DOM
        window.updateCartBadges = function(count) {
            document.querySelectorAll('.live-cart-count').forEach(el => {
                el.innerText = count;
            });
        };

        // Global Add To Cart via Fetch (AJAX)
        window.addToCart = async function(productId, variantId = null, quantity = 1, buttonElement = null) {
            let originalContent = '';
            if (buttonElement) {
                originalContent = buttonElement.innerHTML;
                buttonElement.disabled = true;
                buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Adding...';
            }

            try {
                const response = await fetch('{{ route('cart.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        variant_id: variantId,
                        quantity: quantity
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    window.updateCartBadges(data.cart_count);
                    window.showToast(data.message, true);

                    // Update localStorage for guests
                    if (!IS_AUTHENTICATED) {
                        let guestCart = JSON.parse(localStorage.getItem('guest_cart') || '[]');
                        const existingIndex = guestCart.findIndex(item => item.product_id == productId && item.variant_id == variantId);
                        if (existingIndex > -1) {
                            guestCart[existingIndex].quantity += quantity;
                        } else {
                            guestCart.push({ product_id: productId, variant_id: variantId, quantity: quantity });
                        }
                        localStorage.setItem('guest_cart', JSON.stringify(guestCart));
                    }
                } else {
                    window.showToast(data.message || 'Could not add item to cart.', false);
                }
            } catch (err) {
                console.error('Add to cart error:', err);
                window.showToast('Network error while adding to basket. Please try again.', false);
            } finally {
                if (buttonElement) {
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = originalContent;
                }
            }
        };

        // Sync and Merge Guest Cart on Login
        document.addEventListener('DOMContentLoaded', async () => {
            if (IS_AUTHENTICATED) {
                const guestCartStr = localStorage.getItem('guest_cart');
                if (guestCartStr) {
                    const guestCart = JSON.parse(guestCartStr);
                    if (Array.isArray(guestCart) && guestCart.length > 0) {
                        try {
                            const res = await fetch('{{ route('cart.merge') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': CSRF_TOKEN
                                },
                                body: JSON.stringify({ items: guestCart })
                            });
                            const result = await res.json();
                            if (result.success) {
                                window.updateCartBadges(result.cart_count);
                                localStorage.removeItem('guest_cart');
                            }
                        } catch (e) {
                            console.error('Error merging guest cart:', e);
                        }
                    }
                }
            }
        });
    </script>

    @include('partials.live-search')
    @include('partials.whatsapp-widget')

    @stack('scripts')
</body>
</html>
