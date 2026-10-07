<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | Organic Dairy Gilgit-Baltistan</title>
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
                        <i class="bi bi-shield-check me-1"></i> Security Verification
                    </span>
                    <h1 class="display-6 heading-serif text-white fw-bold mb-3">
                        Choose a strong new password.
                    </h1>
                    <p class="text-white-50 lead fs-6 mb-4">
                        Enter the 6-digit OTP code sent to your email address along with your new password to restore your account access.
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
                                <i class="bi bi-lock fs-3"></i>
                            </div>
                            <h2 class="heading-serif mb-1" style="color: var(--od-dark-green);">Set New Password</h2>
                            <p class="text-muted small">Enter the OTP from your email and your new password below.</p>
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
                                    <div><i class="bi bi-exclamation-circle me-1"></i> {{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('password.update.otp') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label small fw-bold text-dark">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email" value="{{ old('email', $email) }}" class="form-control bg-light border-start-0 @error('email') is-invalid @enderror" placeholder="name@domain.com" required>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="otp" class="form-label small fw-bold text-dark">6-Digit OTP Code <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                                    <input type="text" name="otp" id="otp" value="{{ old('otp') }}" maxlength="6" class="form-control bg-light border-start-0 fw-bold letter-spacing-2 @error('otp') is-invalid @enderror" placeholder="e.g. 123456" required autofocus style="letter-spacing: 3px;">
                                </div>
                                <div class="form-text text-muted" style="font-size: 0.72rem;">Check your email inbox for the 6-digit code.</div>
                                @error('otp')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label small fw-bold text-dark">New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                                    <input type="password" name="password" id="password" class="form-control bg-light border-start-0 border-end-0 @error('password') is-invalid @enderror" placeholder="Min. 8 chars with letters & numbers" required>
                                    <button type="button" class="btn btn-light border border-start-0 text-muted toggle-password" data-target="password" title="Show/Hide Password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text text-muted" style="font-size: 0.72rem;">Must be at least 8 characters with letters & numbers.</div>
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label small fw-bold text-dark">Confirm New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-check2-circle"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control bg-light border-start-0 border-end-0" placeholder="Repeat your new password" required>
                                    <button type="button" class="btn btn-light border border-start-0 text-muted toggle-password" data-target="password_confirmation" title="Show/Hide Password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 mb-3 rounded-3">
                                <i class="bi bi-check2-circle me-1"></i> Update Password & Sign In
                            </button>
                        </form>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light">
                            <form action="{{ route('password.resend.otp') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="email" value="{{ old('email', $email) }}">
                                <button type="submit" class="btn btn-link p-0 small text-decoration-none fw-semibold text-success">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Resend OTP Code
                                </button>
                            </form>

                            <a href="{{ route('login') }}" class="small fw-semibold text-muted text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i> Back to Login
                            </a>
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
