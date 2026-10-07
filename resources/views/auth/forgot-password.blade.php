<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Organic Dairy Gilgit-Baltistan</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container-fluid p-0 min-vh-100 d-flex flex-column">
        <div class="row g-0 flex-grow-1">
            <!-- Left Green Brand Hero Panel -->
            <div class="col-lg-5 d-none d-lg-flex flex-column justify-content-between p-5 text-white" style="background-color: var(--od-dark-green); border-right: 4px solid var(--od-gold);">
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
                        <i class="bi bi-shield-lock me-1"></i> Account Recovery
                    </span>
                    <h1 class="display-6 heading-serif text-white fw-bold mb-3">
                        Forgot your password? No problem.
                    </h1>
                    <p class="text-white-50 lead fs-6 mb-4">
                        Enter your registered email address and we'll instantly send you a secure 6-digit OTP code to verify your identity and set a new password.
                    </p>
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

                    <div class="card p-4 p-md-5 shadow-sm border-0 rounded-4">
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-key fs-3"></i>
                            </div>
                            <h2 class="heading-serif mb-1" style="color: var(--od-dark-green);">Reset Password</h2>
                            <p class="text-muted small">Enter your email and we'll send you a 6-digit OTP reset code.</p>
                        </div>

                        <!-- Flash Alerts -->
                        @if(session('status'))
                            <div class="alert alert-success rounded-4 small mb-3 border-0" style="background-color: #EBF5EE; color: #2F5D3A;">
                                <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger rounded-4 small mb-3 border-0" style="background-color: #FDF0EE; color: #C53030;">
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('password.email') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="email" class="form-label small fw-bold text-dark">Registered Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control bg-light border-start-0 @error('email') is-invalid @enderror" placeholder="name@domain.com" required autofocus>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 mb-3 rounded-3">
                                <i class="bi bi-shield-lock me-1"></i> Send 6-Digit Reset Code
                            </button>
                        </form>

                        <div class="text-center pt-3 border-top border-light">
                            <a href="{{ route('login') }}" class="small fw-semibold text-muted text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i> Return to Sign In
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
