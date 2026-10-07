<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Organic Dairy Gilgit-Baltistan</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container-fluid p-0 min-vh-100 d-flex flex-column">
        <div class="row g-0 flex-grow-1">
            <!-- Left Green Brand Hero Panel -->
            <div class="col-lg-5 d-none d-lg-flex flex-column justify-content-between p-5 text-white position-relative" style="background: linear-gradient(135deg, rgba(31, 58, 39, 0.92) 0%, rgba(21, 39, 26, 0.96) 100%), url('{{ asset('images/banners/auth_bg.jpg') }}') center/cover no-repeat; border-right: 4px solid var(--od-gold);">
                <div>
                    <a href="{{ url('/') }}" class="text-decoration-none d-flex align-items-center gap-2 mb-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-dark" style="width: 48px; height: 48px; background-color: var(--od-gold);">
                            <i class="bi bi-flower1 fs-3"></i>
                        </div>
                        <div>
                            <h3 class="heading-serif text-white mb-0">Organic Dairy</h3>
                            <small class="text-warning text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 2px;">Gilgit-Baltistan</small>
                        </div>
                    </a>
                </div>

                <div class="my-auto py-5">
                    <span class="badge bg-white bg-opacity-10 text-warning border border-warning border-opacity-50 rounded-pill px-3 py-2 mb-3">
                        <i class="bi bi-award me-1"></i> 100% High-Altitude Organic
                    </span>
                    <h1 class="display-6 heading-serif text-white fw-bold mb-3">
                        From pristine Himalayan pastures to your doorstep.
                    </h1>
                    <p class="text-white-50 lead fs-6 mb-4">
                        Sign in to access your farm products, manage multi-vendor orders, track cold-chain deliveries, or restock pure desi ghee and fresh milk.
                    </p>

                    <div class="d-flex flex-column gap-2 small text-white-50">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-warning"></i> Direct from verified farmers in Hunza, Skardu & Ghizer
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-warning"></i> Zero preservatives, unpasteurized live cultures
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-warning"></i> Fast local delivery in Gilgit-Baltistan
                        </div>
                    </div>
                </div>

                <div class="small text-white-50 pt-4 border-top border-white border-opacity-10 d-flex justify-content-between">
                    <span>&copy; {{ date('Y') }} Organic Dairy Marketplace</span>
                    <a href="{{ url('/') }}" class="text-warning text-decoration-none">Back to Store &rarr;</a>
                </div>
            </div>

            <!-- Right Form Section -->
            <div class="col-lg-7 d-flex align-items-center justify-content-center p-4 p-md-5" style="background-color: var(--od-bg);">
                <div class="w-100" style="max-width: 460px;">
                    <!-- Mobile Brand Header -->
                    <div class="d-lg-none text-center mb-4">
                        <a href="{{ url('/') }}" class="text-decoration-none d-inline-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-dark" style="width: 40px; height: 40px; background-color: var(--od-gold);">
                                <i class="bi bi-flower1 fs-4"></i>
                            </div>
                            <h4 class="heading-serif text-dark mb-0">Organic Dairy</h4>
                        </a>
                    </div>

                    <div class="card p-4 p-md-5 shadow-sm border-0">
                        <div class="text-center mb-4">
                            <h2 class="heading-serif mb-1" style="color: var(--od-dark-green);">Welcome Back</h2>
                            <p class="text-muted small">Enter your registered email and password to sign in</p>
                        </div>

                        <!-- Flash Alerts -->
                        @if(session('success'))
                            <div class="alert alert-success rounded-4 small mb-3 border-0" style="background-color: #EBF5EE; color: #2F5D3A;">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('status'))
                            <div class="alert alert-warning rounded-4 small mb-3 border-0" style="background-color: #FEF8E7; color: #9C6B08;">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger rounded-4 small mb-3 border-0" style="background-color: #FDF0EE; color: #C53030;">
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('login.post') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label small fw-bold text-dark">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control bg-light border-start-0 @error('email') is-invalid @enderror" placeholder="name@domain.com" required autofocus>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="password" class="form-label small fw-bold text-dark mb-0">Password</label>
                                    <a href="{{ route('password.request') }}" class="small text-muted text-decoration-none hover-gold" style="font-size: 0.78rem;">Forgot password?</a>
                                </div>
                                <div class="input-group mt-1">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control bg-light border-start-0 border-end-0 @error('password') is-invalid @enderror" placeholder="••••••••" required>
                                    <button type="button" class="btn btn-light border border-start-0 text-muted toggle-password" data-target="password" title="Show/Hide Password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" name="remember" id="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                                    <label for="remember" class="form-check-label small text-muted">Remember me</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 mb-3">
                                Sign In to Account <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </form>

                        <div class="text-center pt-3 border-top border-light">
                            <span class="text-muted small">Don't have an account?</span>
                            <a href="{{ route('register') }}" class="small fw-bold text-success text-decoration-none ms-1">Create an Account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-password').forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                });
            });
        });
    </script>
</body>
</html>
