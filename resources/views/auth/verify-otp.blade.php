<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email OTP | Organic Dairy Gilgit-Baltistan</title>
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
                        <i class="bi bi-shield-check me-1"></i> Two-Step Verification
                    </span>
                    <h1 class="display-6 heading-serif text-white fw-bold mb-3">
                        Verifying your authentic Himalayan account.
                    </h1>
                    <p class="text-white-50 lead fs-6 mb-4">
                        We send a 6-digit security code to your email to safeguard pure organic marketplace transactions and prevent unauthorized account creations.
                    </p>
                </div>

                <div class="small text-white-50 pt-4 border-top border-white border-opacity-10 d-flex justify-content-between">
                    <span>&copy; {{ date('Y') }} Organic Dairy Marketplace</span>
                    <a href="{{ url('/') }}" class="text-warning text-decoration-none">Back to Store &rarr;</a>
                </div>
            </div>

            <!-- Right Form Section -->
            <div class="col-lg-7 d-flex align-items-center justify-content-center p-4 p-md-5" style="background-color: var(--od-bg);">
                <div class="w-100" style="max-width: 480px;">
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
                            <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-envelope-check fs-2"></i>
                            </div>
                            <h2 class="heading-serif mb-1" style="color: var(--od-dark-green);">Verify Your Email</h2>
                            <p class="text-muted small mb-1">
                                We've sent a 6-digit code to
                            </p>
                            <span class="badge bg-light text-dark border px-3 py-2 fs-6 fw-bold">
                                {{ $email }}
                            </span>
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

                        <form action="{{ route('register.verify.post') }}" method="POST" id="otpForm">
                            @csrf

                            <div class="mb-4">
                                <label for="otp" class="form-label small fw-bold text-dark text-center w-100 mb-2">
                                    Enter 6-Digit Verification Code
                                </label>

                                <div class="d-flex justify-content-center gap-2 mb-2" id="otpBoxes">
                                    <input type="text" maxlength="1" class="form-control text-center fs-3 fw-bold otp-digit" style="width: 50px; height: 58px; border-radius: 10px;" autofocus>
                                    <input type="text" maxlength="1" class="form-control text-center fs-3 fw-bold otp-digit" style="width: 50px; height: 58px; border-radius: 10px;">
                                    <input type="text" maxlength="1" class="form-control text-center fs-3 fw-bold otp-digit" style="width: 50px; height: 58px; border-radius: 10px;">
                                    <input type="text" maxlength="1" class="form-control text-center fs-3 fw-bold otp-digit" style="width: 50px; height: 58px; border-radius: 10px;">
                                    <input type="text" maxlength="1" class="form-control text-center fs-3 fw-bold otp-digit" style="width: 50px; height: 58px; border-radius: 10px;">
                                    <input type="text" maxlength="1" class="form-control text-center fs-3 fw-bold otp-digit" style="width: 50px; height: 58px; border-radius: 10px;">
                                </div>

                                <input type="hidden" name="otp" id="otpHiddenInput" value="{{ old('otp') }}">

                                <div class="text-center text-muted small mt-2">
                                    <i class="bi bi-clock-history me-1"></i> Code expires in <span id="timerCountdown" class="fw-bold text-dark">10:00</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 mb-3 rounded-3" id="verifyBtn">
                                <i class="bi bi-shield-check me-1"></i> Verify Code & Activate Account
                            </button>
                        </form>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light">
                            <form action="{{ route('register.resend-otp') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 small text-decoration-none fw-semibold text-success" id="resendBtn">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Resend Code
                                </button>
                            </form>

                            <a href="{{ route('register') }}" class="small text-muted text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i> Change Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.otp-digit');
            const hiddenInput = document.getElementById('otpHiddenInput');
            const form = document.getElementById('otpForm');

            // If old value exists, populate boxes
            if (hiddenInput.value && hiddenInput.value.length === 6) {
                hiddenInput.value.split('').forEach((char, idx) => {
                    if (inputs[idx]) inputs[idx].value = char;
                });
            }

            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    const val = e.target.value;
                    if (val.length > 1) {
                        e.target.value = val.slice(0, 1);
                    }
                    if (e.target.value !== '' && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                    updateHiddenOtp();
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });

                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
                    if (/^\d{6}$/.test(pasteData)) {
                        pasteData.split('').forEach((char, i) => {
                            if (inputs[i]) inputs[i].value = char;
                        });
                        updateHiddenOtp();
                        inputs[5].focus();
                    }
                });
            });

            function updateHiddenOtp() {
                let code = '';
                inputs.forEach(inp => code += inp.value);
                hiddenInput.value = code;
            }

            form.addEventListener('submit', function(e) {
                updateHiddenOtp();
            });

            // 10 minute countdown timer
            let timeLeft = 600;
            const timerEl = document.getElementById('timerCountdown');
            const timerInterval = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    timerEl.innerHTML = "<span class='text-danger'>Expired</span>";
                } else {
                    timeLeft--;
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    timerEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }, 1000);
        });
    </script>
</body>
</html>
