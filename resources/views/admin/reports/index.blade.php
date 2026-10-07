@extends('layouts.panel')

@section('title', 'Sales & Financial Reports - Admin Center')
@section('page_heading', 'Marketplace Reports & Financial Analytics')

@section('menu_items')
    @include('admin.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Date Range Filter & CSV Export Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-xs fw-bold text-muted text-uppercase">From:</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-xs fw-bold text-muted text-uppercase">To:</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                </div>
                <button type="submit" class="btn btn-sm btn-darkgreen rounded-pill px-3">
                    <i class="bi bi-funnel me-1"></i> Apply Filter
                </button>
            </form>

            <div>
                <a href="{{ route('admin.reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-outline-success rounded-pill px-4 fw-semibold">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export to CSV
                </a>
            </div>
        </div>
    </div>

    <!-- 4 KPI Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-xs text-muted text-uppercase fw-bold">Gross Sales</span>
                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-darkgreen mb-1">Rs {{ number_format($totalSales) }}</h3>
                <div class="text-xs text-muted">Including shipping and tax</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-xs text-muted text-uppercase fw-bold">Product Subtotal</span>
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-basket2"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark mb-1">Rs {{ number_format($totalSubtotal) }}</h3>
                <div class="text-xs text-muted">Net goods value</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-xs text-muted text-uppercase fw-bold">Commission Revenue</span>
                    <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-percent"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-gold mb-1">Rs {{ number_format($totalCommission) }}</h3>
                <div class="text-xs text-muted">5% Marketplace platform fee</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-xs text-muted text-uppercase fw-bold">Active Orders</span>
                    <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-bag-check"></i>
                    </div>
                </div>
                <h3 class="font-serif fw-bold text-dark mb-1">{{ number_format($ordersCount) }}</h3>
                <div class="text-xs text-muted">Fulfillments in selected period</div>
            </div>
        </div>
    </div>

    <!-- Section 1: Sales by Date -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <h5 class="font-serif fw-bold text-dark mb-3">
            <i class="bi bi-calendar-range text-darkgreen me-2"></i> Daily Sales Breakdown
        </h5>
        @if($salesByDate->isEmpty())
            <p class="text-muted small mb-0">No sales recorded during this date range.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-3">Date</th>
                            <th>Orders</th>
                            <th>Items Subtotal</th>
                            <th>Platform Commission</th>
                            <th class="text-end pe-3">Gross Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salesByDate as $row)
                            <tr>
                                <td class="ps-3 fw-semibold text-dark">{{ Carbon\Carbon::parse($row->date)->format('M d, Y') }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $row->orders_count }} orders</span></td>
                                <td>Rs {{ number_format($row->total_subtotal) }}</td>
                                <td class="text-muted">Rs {{ number_format($row->total_commission) }}</td>
                                <td class="text-end pe-3 fw-bold text-darkgreen">Rs {{ number_format($row->total_revenue) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="row g-4">
        <!-- Section 2: Sales by Seller -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h5 class="font-serif fw-bold text-dark mb-3">
                    <i class="bi bi-shop text-darkgreen me-2"></i> Sales by Farm Seller
                </h5>
                @if($salesBySeller->isEmpty())
                    <p class="text-muted small mb-0">No seller sales recorded during this period.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small">
                                    <th class="ps-3">Seller Farm</th>
                                    <th>Orders</th>
                                    <th>Commission</th>
                                    <th class="text-end pe-3">Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesBySeller as $sellerRow)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $sellerRow->seller->sellerProfile->farm_name ?? $sellerRow->seller->name ?? 'Farm' }}</div>
                                            <div class="text-xs text-muted">{{ $sellerRow->seller->sellerProfile->location ?? '' }}</div>
                                        </td>
                                        <td>{{ $sellerRow->orders_count }}</td>
                                        <td class="text-muted">Rs {{ number_format($sellerRow->total_commission) }}</td>
                                        <td class="text-end pe-3 fw-bold text-darkgreen">Rs {{ number_format($sellerRow->total_revenue) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Section 3: Sales by Category -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h5 class="font-serif fw-bold text-dark mb-3">
                    <i class="bi bi-pie-chart text-darkgreen me-2"></i> Sales by Category
                </h5>
                @if($salesByCategory->isEmpty())
                    <p class="text-muted small mb-0">No category sales recorded during this period.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small">
                                    <th class="ps-3">Category</th>
                                    <th>Units Sold</th>
                                    <th class="text-end pe-3">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesByCategory as $catRow)
                                    <tr>
                                        <td class="ps-3 fw-semibold text-dark">{{ $catRow->category_name }}</td>
                                        <td>{{ number_format($catRow->total_units_sold) }} pcs</td>
                                        <td class="text-end pe-3 fw-bold text-darkgreen">Rs {{ number_format($catRow->total_revenue) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
