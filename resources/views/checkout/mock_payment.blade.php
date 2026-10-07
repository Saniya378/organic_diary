@extends('layouts.public')

@section('title', 'Payment Gateway Simulator - Organic Dairy GB')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            @if(session('error'))
                <div class="alert alert-danger rounded-4 shadow-sm mb-4 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-x-circle-fill fs-4"></i>
                        <div>
                            <strong>Payment Simulation Failed</strong>
                            <div class="small">{{ session('error') }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <!-- Gateway Header -->
                <div class="card-header bg-dark-green text-white p-4 text-center">
                    <div class="badge bg-warning text-dark mb-2 px-3 py-1 rounded-pill fw-bold">
                        <i class="bi bi-shield-lock-fill me-1"></i> SIMULATION ENVIRONMENT
                    </div>
                    <h4 class="mb-1 font-serif text-white">Digital Payment Gateway</h4>
                    <p class="text-white-50 small mb-0">Himalayan Organic Dairy Safe Transaction Portal</p>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <!-- Order Reference & Total -->
                    <div class="bg-light p-3 rounded-3 border mb-4 text-center">
                        <span class="text-muted small text-uppercase fw-semibold">Amount to Pay</span>
                        <h2 class="text-primary font-serif fw-bold my-1">
                            Rs {{ number_format($orderGroup->grand_total) }}
                        </h2>
                        <div class="small text-secondary">
                            Order Reference: <strong>#{{ $orderGroup->group_no }}</strong>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2">Payment Details</h6>
                        <ul class="list-group list-group-flush border rounded-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted">Method:</span>
                                <span class="fw-semibold">{{ strtoupper($orderGroup->payment_method) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted">Customer Name:</span>
                                <span class="fw-semibold">{{ $orderGroup->shipping_full_name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted">Card / Account Ref:</span>
                                <span class="font-monospace">
                                    @if($payment && $payment->last4)
                                        &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; {{ $payment->last4 }}
                                    @else
                                        &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; 4242
                                    @endif
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted">Current Status:</span>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                    Pending Payment
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 small mb-4">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        This is a sandbox simulator. Click <strong>Authorize & Pay (Success)</strong> to simulate a successful bank authorization, or <strong>Decline (Fail)</strong> to test failure recovery.
                    </div>

                    <!-- Simulation Action Buttons -->
                    <div class="d-grid gap-3">
                        <form action="{{ route('checkout.payment.mock.process', $orderGroup->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="success">
                            <input type="hidden" name="last4" value="{{ $payment->last4 ?? '4242' }}">
                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check-circle-fill fs-5"></i> Authorize & Pay (Success)
                            </button>
                        </form>

                        <form action="{{ route('checkout.payment.mock.process', $orderGroup->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="fail">
                            <button type="submit" class="btn btn-outline-danger btn-lg w-100 rounded-pill fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-x-circle-fill fs-5"></i> Decline / Fail Transaction
                            </button>
                        </form>
                    </div>

                    <!-- Switch to COD option -->
                    <hr class="my-4 text-muted opacity-25">

                    <div class="text-center">
                        <p class="small text-muted mb-2">Want to pay with cash instead?</p>
                        <form action="{{ route('checkout.payment.switch-cod', $orderGroup->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-light border rounded-pill px-4 btn-sm fw-semibold text-dark">
                                <i class="bi bi-cash-stack text-success me-1"></i> Switch to Cash on Delivery (COD)
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-footer bg-light text-center py-3 border-top small text-muted">
                    <i class="bi bi-lock-fill text-success"></i> 256-Bit SSL Encrypted Sandbox
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
