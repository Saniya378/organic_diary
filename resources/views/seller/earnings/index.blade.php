@extends('layouts.panel')

@section('title', 'Earnings & Payouts - Farmer Portal')
@section('page_heading', 'Farmer Financial Ledger & Earnings')

@section('menu_items')
    @include('seller.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Top Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="font-serif fw-bold text-dark mb-0">Sales &amp; Earnings Analytics</h4>
            <div class="text-muted small">Real-time ledger of orders and platform commission</div>
        </div>
        <div>
            <a href="{{ route('seller.payouts.index') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                <i class="bi bi-wallet2 me-1"></i> Manage Wallet &amp; Withdrawals &rarr;
            </a>
        </div>
    </div>

    <!-- Date Range & Preset Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <form action="{{ route('seller.earnings.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-muted">Quick Date Presets</label>
                <div class="btn-group w-100" role="group">
                    <a href="{{ route('seller.earnings.index', ['preset' => 'this_month']) }}" class="btn btn-sm {{ $preset === 'this_month' ? 'btn-primary' : 'btn-outline-secondary' }}">This Month</a>
                    <a href="{{ route('seller.earnings.index', ['preset' => 'last_30_days']) }}" class="btn btn-sm {{ $preset === 'last_30_days' ? 'btn-primary' : 'btn-outline-secondary' }}">Last 30 Days</a>
                    <a href="{{ route('seller.earnings.index', ['preset' => 'this_year']) }}" class="btn btn-sm {{ $preset === 'this_year' ? 'btn-primary' : 'btn-outline-secondary' }}">This Year</a>
                </div>
            </div>

            <div class="col-md-3">
                <label for="start_date" class="form-label small fw-semibold text-muted">From Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>

            <div class="col-md-3">
                <label for="end_date" class="form-label small fw-semibold text-muted">To Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>

            <div class="col-md-1">
                <input type="hidden" name="preset" value="custom">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-funnel-fill"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- 3 Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Gross Sales -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Gross Farm Sales</span>
                    <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-graph-up-arrow fs-5"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark-green mb-1">Rs {{ number_format($grossSales) }}</h3>
                <div class="small text-muted">From {{ $totalOrdersCount }} active & completed orders</div>
            </div>
        </div>

        <!-- Card 2: Commission -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Platform Commission</span>
                    <div class="rounded-circle p-2 bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-percent fs-5"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-danger mb-1">- Rs {{ number_format($totalCommission) }}</h3>
                <div class="small text-muted">Product-based platform fee & cold-chain logistics</div>
            </div>
        </div>

        <!-- Card 3: Net Payable -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 border-success-subtle">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Net Farmer Payable</span>
                    <div class="rounded-circle p-2 bg-warning-subtle text-gold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-primary mb-1">Rs {{ number_format($netPayable) }}</h3>
                <div class="small text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Net payable to farmer account</div>
            </div>
        </div>
    </div>

    <!-- Orders Financial Ledger Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom border-light">
            <h5 class="font-serif fw-bold text-dark-green mb-0">Order Payout Breakdown</h5>
        </div>
        @if($orders->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-receipt fs-1 text-gold d-block mb-3"></i>
                <h5 class="font-serif fw-bold text-dark">No Financial Records in this Period</h5>
                <p class="small mb-0">Try selecting a broader date range or checking back after fulfilling orders.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Order #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Item Subtotal</th>
                            <th>Platform Commission</th>
                            <th>Net Farmer Payout</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-dark">
                                    <a href="{{ route('seller.orders.show', $order->id) }}" class="text-decoration-none text-dark hover-gold">
                                        {{ $order->order_no }}
                                    </a>
                                </td>
                                <td class="small text-muted">
                                    {{ $order->created_at->format('M d, Y') }}
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $order->buyer->name }}</div>
                                    <small class="text-muted">{{ $order->orderGroup->shipping_city ?? 'Gilgit' }}</small>
                                </td>
                                <td class="fw-semibold">
                                    Rs {{ number_format($order->subtotal) }}
                                </td>
                                <td class="text-danger small">
                                    - Rs {{ number_format($order->commission) }}
                                </td>
                                <td class="fw-bold text-dark-green font-serif">
                                    Rs {{ number_format($order->subtotal - $order->commission) }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border small">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success text-white' : 'bg-warning text-dark' }} px-2 py-1 rounded-pill small">
                                        {{ strtoupper($order->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
