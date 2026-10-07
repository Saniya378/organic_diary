<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Control Panel') | Organic Dairy Gilgit-Baltistan</title>

    <!-- Google Fonts & Styles -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        .sidebar {
            background-color: var(--od-dark-green);
            min-height: 100vh;
            color: #FFFFFF;
        }
        .sidebar-brand {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.25rem 1.5rem;
        }
        .sidebar-nav .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
        }
        .sidebar-nav .nav-link:hover {
            color: #FFFFFF;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar-nav .nav-link.active {
            color: #FFFFFF;
            background-color: var(--od-primary);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .panel-topbar {
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--od-border);
            height: 70px;
        }
        .panel-content {
            background-color: var(--od-bg);
            min-height: calc(100vh - 70px);
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <aside class="col-lg-2 col-md-3 sidebar d-none d-md-block">
                <div class="sidebar-brand d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-dark" style="width: 38px; height: 38px; background-color: var(--od-gold);">
                        <i class="bi bi-flower1 fs-4"></i>
                    </div>
                    <div>
                        <span class="heading-serif text-white fs-5 lh-1 d-block">Organic Dairy</span>
                        <small class="text-warning text-uppercase fw-bold" style="font-size: 0.62rem; letter-spacing: 1px;">
                            {{ Auth::user()->role === 'admin' ? 'Administrator' : 'Farmer Portal' }}
                        </small>
                    </div>
                </div>

                <div class="p-3">
                    <div class="text-uppercase text-white-50 fw-bold px-3 mb-2" style="font-size: 0.68rem; letter-spacing: 1px;">
                        Navigation Menu
                    </div>
                    <ul class="nav flex-column sidebar-nav">
                        @yield('menu_items')
                    </ul>

                    <hr class="border-white border-opacity-10 my-4">

                    <ul class="nav flex-column sidebar-nav">
                        <li class="nav-item">
                            <a href="{{ url('/') }}" class="nav-link text-white-50" target="_blank">
                                <i class="bi bi-globe"></i> View Public Store
                            </a>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start">
                                    <i class="bi bi-box-arrow-right"></i> Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- Main Panel Area -->
            <div class="col-lg-10 col-md-9 col-12 d-flex flex-column">
                <!-- Topbar -->
                <header class="panel-topbar px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <!-- Mobile Offcanvas Trigger -->
                        <button class="btn btn-sm btn-outline-darkgreen d-md-none p-1 px-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                            <i class="bi bi-list fs-5"></i>
                        </button>
                        <h5 class="heading-serif mb-0 text-dark">@yield('page_heading', 'Dashboard')</h5>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <!-- Notifications Dropdown -->
                        @include('partials.notifications-dropdown')

                        <span class="badge {{ Auth::user()->isAdmin() ? 'badge-status-new' : 'badge-status-pending' }} text-uppercase">
                            {{ Auth::user()->role }}
                        </span>

                        <div class="dropdown">
                            <button class="btn btn-outline-custom dropdown-toggle d-flex align-items-center gap-2 py-1 px-2.5 rounded-pill shadow-sm" type="button" id="panelUserMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="rounded-circle overflow-hidden border border-1 border-white flex-shrink-0" style="width: 28px; height: 28px; background-color: var(--od-primary);">
                                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-100 h-100 object-fit-cover">
                                </div>
                                <span class="fw-semibold text-sm">{{ Auth::user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2 rounded-3" aria-labelledby="panelUserMenu" style="min-width: 220px; border: 1px solid var(--od-border) !important;">
                                <li class="px-3 py-2 border-bottom d-flex align-items-center gap-2">
                                    <div class="rounded-circle overflow-hidden border flex-shrink-0" style="width: 36px; height: 36px; background-color: var(--od-primary);">
                                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-100 h-100 object-fit-cover">
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-dark text-truncate">{{ Auth::user()->name }}</div>
                                        <small class="text-muted text-truncate d-block">{{ Auth::user()->email }}</small>
                                    </div>
                                </li>
                                <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}"><i class="bi bi-person text-success me-2"></i> My Account</a></li>
                                <li><a class="dropdown-item py-2" href="{{ url('/') }}"><i class="bi bi-shop text-primary me-2"></i> Marketplace</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </header>

                <!-- Seller Pending Status Banner -->
                @if(Auth::user()->isSeller() && Auth::user()->sellerProfile && Auth::user()->sellerProfile->status === 'pending')
                    <div class="bg-warning bg-opacity-25 border-bottom border-warning px-4 py-3 text-dark d-flex align-items-center gap-3">
                        <i class="bi bi-clock-history fs-3 text-warning"></i>
                        <div>
                            <strong class="d-block text-dark">Seller Verification Pending</strong>
                            <small class="text-secondary">Your farm account is under review by administrator. Seller accounts need admin approval before products go live in the public marketplace catalog.</small>
                        </div>
                    </div>
                @endif

                <!-- Flash Alerts inside Panel -->
                <div class="px-4 pt-3">
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

                <!-- Panel Content -->
                <main class="panel-content p-4">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <!-- Mobile Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start sidebar text-white" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-dark" style="width: 32px; height: 32px; background-color: var(--od-gold);">
                    <i class="bi bi-flower1 fs-5"></i>
                </div>
                <h5 class="heading-serif text-white mb-0" id="mobileSidebarLabel">Organic Dairy</h5>
            </div>
            <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-3">
            <ul class="nav flex-column sidebar-nav">
                @yield('menu_items')
            </ul>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
