<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Organic Dairy Gilgit-Baltistan</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container-fluid p-0 min-vh-100 d-flex flex-column">
        <div class="row g-0 flex-grow-1">
            <!-- Left Green Brand Hero Panel -->
            <div class="col-lg-5 d-none d-lg-flex flex-column justify-content-between p-5 text-white position-relative" style="background: linear-gradient(135deg, rgba(31, 58, 39, 0.92) 0%, rgba(21, 39, 26, 0.96) 100%), url('{{ asset('images/banners/auth_bg.jpg') }}') center/cover no-repeat; border-right: 4px solid var(--od-gold); position: sticky; top: 0; height: 100vh; overflow-y: auto;">
                <div class="d-flex flex-column gap-3">
                    <a href="{{ url('/') }}" class="text-decoration-none d-flex align-items-center gap-2 mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-dark" style="width: 48px; height: 48px; background-color: var(--od-gold);">
                            <i class="bi bi-flower1 fs-3"></i>
                        </div>
                        <div>
                            <h3 class="heading-serif text-white mb-0">Organic Dairy</h3>
                            <small class="text-warning text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 2px;">Gilgit-Baltistan</small>
                        </div>
                    </a>

                    <div>
                        <span class="badge bg-white bg-opacity-10 text-warning border border-warning border-opacity-50 rounded-pill px-3 py-2 mb-3">
                            <i class="bi bi-people-fill me-1"></i> Join Our Mountain Network
                        </span>
                        <h1 class="display-6 heading-serif text-white fw-bold mb-3">
                            Join as a Dairy Enthusiast or Certified Farmer.
                        </h1>
                        <p class="text-white-50 lead fs-6 mb-4">
                            Buy raw grass-fed dairy straight from local shepherds, or register your high-altitude farm in Hunza, Skardu, or Ghizer to sell directly to consumers.
                        </p>

                        <div class="card bg-white bg-opacity-10 border-0 text-white p-3 rounded-4 mb-3">
                            <div class="d-flex align-items-center gap-2 fw-bold text-warning mb-1">
                                <i class="bi bi-info-circle-fill"></i> For Farm Sellers
                            </div>
                            <small class="text-white-70">
                                Seller accounts are manually verified by our team to maintain strict organic grass-fed standards. Once approved, you can publish unlimited fresh batches and receive online payments.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="small text-white-50 pt-4 border-top border-white border-opacity-10 d-flex justify-content-between mt-4">
                    <span>&copy; {{ date('Y') }} Organic Dairy Marketplace</span>
                    <a href="{{ url('/') }}" class="text-warning text-decoration-none">Back to Store &rarr;</a>
                </div>
            </div>

            <!-- Right Form Section -->
            <div class="col-lg-7 d-flex align-items-center justify-content-center p-4 p-md-5" style="background-color: var(--od-bg);">
                <div class="w-100 py-3" style="max-width: 540px;">
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
                            <h2 class="heading-serif mb-1" style="color: var(--od-dark-green);">Create an Account</h2>
                            <p class="text-muted small">Choose your account type and fill in your details below</p>

                            <!-- Role Toggle Pills -->
                            <div class="d-inline-flex p-1 rounded-pill bg-light border mt-2" role="group">
                                <button type="button" id="roleBtnBuyer" class="btn btn-sm px-4 py-2 rounded-pill btn-primary" onclick="setRole('buyer')">
                                    <i class="bi bi-basket2 me-1"></i> I am a Buyer
                                </button>
                                <button type="button" id="roleBtnSeller" class="btn btn-sm px-4 py-2 rounded-pill text-dark" style="background: transparent;" onclick="setRole('seller')">
                                    <i class="bi bi-shop me-1"></i> I am a Farm Seller
                                </button>
                            </div>
                        </div>

                        <!-- Flash Alerts -->
                        @if($errors->any())
                            <div class="alert alert-danger rounded-4 small mb-3 border-0" style="background-color: #FDF0EE; color: #C53030;">
                                <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle me-1"></i> Please check the following errors:</div>
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('register.post') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="role" id="roleInput" value="{{ old('role', 'buyer') }}">

                            <!-- Common Fields -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label small fw-bold text-dark">Full Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control bg-light border-start-0 @error('name') is-invalid @enderror" placeholder="e.g. Karim Ali" required>
                                    </div>
                                    @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label small fw-bold text-dark">Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-telephone"></i></span>
                                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control bg-light border-start-0 @error('phone') is-invalid @enderror" placeholder="03451234567" required>
                                    </div>
                                    @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label small fw-bold text-dark">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control bg-light border-start-0 @error('email') is-invalid @enderror" placeholder="name@domain.com" required>
                                </div>
                                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Dynamic Seller Fields Section -->
                            <div id="sellerFieldsSection" class="p-3 mb-3 rounded-4 border" style="background-color: #FAF7F0; display: {{ old('role', 'buyer') === 'seller' ? 'block' : 'none' }}; border-color: var(--od-border) !important;">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge badge-status-pending"><i class="bi bi-shop me-1"></i> Farm Information</span>
                                </div>

                                <div class="alert alert-warning py-2 px-3 rounded-3 small mb-3 border-0" style="background-color: #FEF8E7; color: #9C6B08;">
                                    <i class="bi bi-info-circle-fill me-1"></i>
                                    <strong>Note:</strong> Seller accounts need admin approval before products go live.
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="farm_name" class="form-label small fw-bold text-dark">Farm / Dairy Name <span class="text-danger">*</span></label>
                                        <input type="text" name="farm_name" id="farm_name" value="{{ old('farm_name') }}" class="form-control bg-white @error('farm_name') is-invalid @enderror" placeholder="e.g. Hunza Valley Organic Farm">
                                        @error('farm_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="location" class="form-label small fw-bold text-dark">Farm Location (GB) <span class="text-danger">*</span></label>
                                        <select name="location" id="location" class="form-select bg-white @error('location') is-invalid @enderror">
                                            <option value="">Select Valley / City</option>
                                            <option value="Karimabad, Hunza" {{ old('location') == 'Karimabad, Hunza' ? 'selected' : '' }}>Hunza Valley</option>
                                            <option value="Sadpara Road, Skardu" {{ old('location') == 'Sadpara Road, Skardu' ? 'selected' : '' }}>Skardu (Baltistan)</option>
                                            <option value="Gahkuch, Ghizer" {{ old('location') == 'Gahkuch, Ghizer' ? 'selected' : '' }}>Ghizer Valley</option>
                                            <option value="Gorikot, Astore" {{ old('location') == 'Gorikot, Astore' ? 'selected' : '' }}>Astore Valley</option>
                                            <option value="Chalt, Nagar" {{ old('location') == 'Chalt, Nagar' ? 'selected' : '' }}>Nagar Valley</option>
                                            <option value="Jutial, Gilgit" {{ old('location') == 'Jutial, Gilgit' ? 'selected' : '' }}>Gilgit City</option>
                                            <option value="Chilas, Diamer" {{ old('location') == 'Chilas, Diamer' ? 'selected' : '' }}>Diamer Valley</option>
                                        </select>
                                        @error('location') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label small fw-bold text-dark">Farm Description & Practices</label>
                                    <textarea name="description" id="description" rows="2" class="form-control bg-white @error('description') is-invalid @enderror" placeholder="Tell buyers about your livestock feeding (grass, herbs), milking methods, and tradition...">{{ old('description') }}</textarea>
                                    @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="photo" class="form-label small fw-bold text-dark">Farm / Logo Photo (Optional)</label>
                                    <input type="file" name="photo" id="photo" class="form-control bg-white @error('photo') is-invalid @enderror" accept="image/*">
                                    <small class="text-muted" style="font-size: 0.72rem;">Accepts JPG, PNG, WEBP up to 2MB.</small>
                                    @error('photo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <!-- CNIC Identity Verification Box -->
                                <div class="p-3 mt-3 rounded-3 bg-white border" style="border-left: 4px solid #1F3A27 !important;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-person-vcard-fill text-success fs-5"></i>
                                            <span class="fw-bold text-dark small">Government CNIC Identity Verification</span>
                                        </div>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">Mandatory for Approval</span>
                                    </div>
                                    <p class="text-muted small mb-3" style="font-size: 0.78rem;">
                                        Admin manually checks and verifies your real Pakistani CNIC before granting farm selling privileges to ensure marketplace authenticity.
                                    </p>

                                    <!-- CNIC Number -->
                                    <div class="mb-3">
                                        <label for="cnic_no" class="form-label small fw-bold text-dark">
                                            CNIC Number (شناختی کارڈ نمبر) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-card-heading"></i></span>
                                            <input type="text" name="cnic_no" id="cnic_no" value="{{ old('cnic_no') }}" maxlength="15" class="form-control bg-white border-start-0 font-monospace @error('cnic_no') is-invalid @enderror" placeholder="71101-1234567-1">
                                        </div>
                                        <small class="text-muted" style="font-size: 0.72rem;">Enter 13-digit CNIC without or with dashes (Format: 71101-1234567-1).</small>
                                        @error('cnic_no') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- CNIC Front and Back Upload Cards -->
                                    <div class="row g-2">
                                        <!-- CNIC Front Side -->
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-2.5 bg-light h-100 position-relative">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label for="cnic_front" class="form-label small fw-bold text-dark mb-0">
                                                        CNIC Front Side <span class="text-danger">*</span>
                                                    </label>
                                                    <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">Front (سامنے)</span>
                                                </div>
                                                <small class="text-muted d-block mb-2" style="font-size: 0.7rem;">Clear photo showing your face & CNIC number.</small>
                                                
                                                <input type="file" name="cnic_front" id="cnic_front" class="form-control form-control-sm bg-white @error('cnic_front') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/webp,application/pdf" onchange="previewCnic(this, 'cnicFrontPreview', 'cnicFrontPreviewBox')">
                                                
                                                <!-- Preview Container -->
                                                <div id="cnicFrontPreviewBox" class="mt-2 text-center rounded border bg-white p-2 d-none" style="max-height: 120px; overflow: hidden;">
                                                    <img id="cnicFrontPreview" src="" alt="CNIC Front Preview" class="img-fluid rounded" style="max-height: 100px; object-fit: contain;">
                                                    <div id="cnicFrontPdfNotice" class="d-none small text-danger py-2">
                                                        <i class="bi bi-file-earmark-pdf-fill fs-3 text-danger d-block mb-1"></i>
                                                        <span class="fw-semibold">PDF Document Selected</span>
                                                    </div>
                                                </div>
                                                @error('cnic_front') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        <!-- CNIC Back Side -->
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-2.5 bg-light h-100 position-relative">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label for="cnic_back" class="form-label small fw-bold text-dark mb-0">
                                                        CNIC Back Side <span class="text-danger">*</span>
                                                    </label>
                                                    <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">Back (پیچھے)</span>
                                                </div>
                                                <small class="text-muted d-block mb-2" style="font-size: 0.7rem;">Clear photo showing address & family number.</small>
                                                
                                                <input type="file" name="cnic_back" id="cnic_back" class="form-control form-control-sm bg-white @error('cnic_back') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/webp,application/pdf" onchange="previewCnic(this, 'cnicBackPreview', 'cnicBackPreviewBox')">
                                                
                                                <!-- Preview Container -->
                                                <div id="cnicBackPreviewBox" class="mt-2 text-center rounded border bg-white p-2 d-none" style="max-height: 120px; overflow: hidden;">
                                                    <img id="cnicBackPreview" src="" alt="CNIC Back Preview" class="img-fluid rounded" style="max-height: 100px; object-fit: contain;">
                                                    <div id="cnicBackPdfNotice" class="d-none small text-danger py-2">
                                                        <i class="bi bi-file-earmark-pdf-fill fs-3 text-danger d-block mb-1"></i>
                                                        <span class="fw-semibold">PDF Document Selected</span>
                                                    </div>
                                                </div>
                                                @error('cnic_back') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-muted mt-2" style="font-size: 0.7rem;">
                                        <i class="bi bi-shield-check text-success me-1"></i> Formats: JPG, PNG, WebP, PDF &bull; Max 5MB per file &bull; Kept private & only visible to Admin.
                                    </div>
                                </div>
                            </div>

                            <!-- Password Fields -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="password" class="form-label small fw-bold text-dark">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password" id="password" class="form-control bg-light border-start-0 border-end-0 @error('password') is-invalid @enderror" placeholder="••••••••" required>
                                        <button type="button" class="btn btn-light border border-start-0 text-muted toggle-password" data-target="password" title="Show/Hide Password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;">Min 8 chars with 1 letter & 1 number.</small>
                                    @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label small fw-bold text-dark">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control bg-light border-start-0 border-end-0" placeholder="••••••••" required>
                                        <button type="button" class="btn btn-light border border-start-0 text-muted toggle-password" data-target="password_confirmation" title="Show/Hide Password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 mb-3">
                                <i class="bi bi-shield-lock me-1"></i> Continue & Send Email OTP <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </form>

                        <div class="text-center pt-3 border-top border-light">
                            <span class="text-muted small">Already have an account?</span>
                            <a href="{{ route('login') }}" class="small fw-bold text-success text-decoration-none ms-1">Sign In</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setRole(role) {
            document.getElementById('roleInput').value = role;
            const buyerBtn = document.getElementById('roleBtnBuyer');
            const sellerBtn = document.getElementById('roleBtnSeller');
            const sellerFields = document.getElementById('sellerFieldsSection');

            const farmNameInput = document.getElementById('farm_name');
            const locationSelect = document.getElementById('location');
            const cnicNoInput = document.getElementById('cnic_no');
            const cnicFrontInput = document.getElementById('cnic_front');
            const cnicBackInput = document.getElementById('cnic_back');

            if (role === 'seller') {
                sellerBtn.className = 'btn btn-sm px-4 py-2 rounded-pill btn-primary';
                buyerBtn.className = 'btn btn-sm px-4 py-2 rounded-pill text-dark';
                buyerBtn.style.background = 'transparent';
                sellerFields.style.display = 'block';

                if (farmNameInput) farmNameInput.required = true;
                if (locationSelect) locationSelect.required = true;
                if (cnicNoInput) cnicNoInput.required = true;
                if (cnicFrontInput) cnicFrontInput.required = true;
                if (cnicBackInput) cnicBackInput.required = true;
            } else {
                buyerBtn.className = 'btn btn-sm px-4 py-2 rounded-pill btn-primary';
                sellerBtn.className = 'btn btn-sm px-4 py-2 rounded-pill text-dark';
                sellerBtn.style.background = 'transparent';
                sellerFields.style.display = 'none';

                if (farmNameInput) farmNameInput.required = false;
                if (locationSelect) locationSelect.required = false;
                if (cnicNoInput) cnicNoInput.required = false;
                if (cnicFrontInput) cnicFrontInput.required = false;
                if (cnicBackInput) cnicBackInput.required = false;
            }
        }

        function previewCnic(input, previewImgId, containerId) {
            const container = document.getElementById(containerId);
            const img = document.getElementById(previewImgId);
            const pdfNoticeId = previewImgId.replace('Preview', 'PdfNotice');
            const pdfNotice = document.getElementById(pdfNoticeId);

            if (!container || !img) return;

            if (input.files && input.files[0]) {
                const file = input.files[0];
                container.classList.remove('d-none');

                if (file.type === 'application/pdf') {
                    img.classList.add('d-none');
                    if (pdfNotice) {
                        pdfNotice.classList.remove('d-none');
                        pdfNotice.querySelector('.fw-semibold').textContent = file.name;
                    }
                } else if (file.type.startsWith('image/')) {
                    if (pdfNotice) pdfNotice.classList.add('d-none');
                    img.classList.remove('d-none');
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            } else {
                container.classList.add('d-none');
                img.src = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize role state based on old value or default
            const initialRole = document.getElementById('roleInput').value || 'buyer';
            setRole(initialRole);

            // CNIC Auto-Formatting (XXXXX-XXXXXXX-X)
            const cnicInput = document.getElementById('cnic_no');
            if (cnicInput) {
                cnicInput.addEventListener('input', function() {
                    let digits = this.value.replace(/\D/g, ''); // strip non-digits
                    if (digits.length > 13) {
                        digits = digits.substring(0, 13);
                    }
                    let formatted = '';
                    if (digits.length > 5) {
                        formatted += digits.substring(0, 5) + '-';
                        if (digits.length > 12) {
                            formatted += digits.substring(5, 12) + '-' + digits.substring(12, 13);
                        } else {
                            formatted += digits.substring(5);
                        }
                    } else {
                        formatted = digits;
                    }
                    this.value = formatted;
                });
            }

            // Password Show/Hide Toggle
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
