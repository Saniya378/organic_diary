@extends('layouts.panel')

@section('title', 'Manage Users - Admin Center')
@section('page_heading', 'Platform Users & Access Control')

@section('menu_items')
    @include('admin.partials.menu')
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <ul class="nav nav-pills gap-1 flex-wrap">
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-1 text-sm {{ !$currentRole ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.users.index', array_filter(['status' => $currentStatus, 'q' => $search])) }}">
                            All Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentRole === 'buyer' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.users.index', array_filter(['role' => 'buyer', 'status' => $currentStatus, 'q' => $search])) }}">
                            Buyers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentRole === 'seller' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.users.index', array_filter(['role' => 'seller', 'status' => $currentStatus, 'q' => $search])) }}">
                            Sellers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentRole === 'admin' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('admin.users.index', array_filter(['role' => 'admin', 'status' => $currentStatus, 'q' => $search])) }}">
                            Admins
                        </a>
                    </li>
                </ul>

                <span class="text-muted small mx-1">|</span>

                <ul class="nav nav-pills gap-1 flex-wrap">
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'active' ? 'active bg-success' : 'text-dark' }}" href="{{ route('admin.users.index', array_filter(['role' => $currentRole, 'status' => 'active', 'q' => $search])) }}">
                            Active
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-1 text-sm {{ $currentStatus === 'suspended' ? 'active bg-danger' : 'text-dark' }}" href="{{ route('admin.users.index', array_filter(['role' => $currentRole, 'status' => 'suspended', 'q' => $search])) }}">
                            Suspended
                        </a>
                    </li>
                </ul>
            </div>

            <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex gap-2">
                @if($currentRole)
                    <input type="hidden" name="role" value="{{ $currentRole }}">
                @endif
                @if($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="input-group input-group-sm" style="min-width: 250px;">
                    <input type="text" name="q" class="form-control" placeholder="Search name, email, phone..." value="{{ $search }}">
                    <button type="submit" class="btn btn-outline-darkgreen">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        @if($users->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-people fs-1 text-gold d-block mb-3"></i>
                <h5 class="font-serif fw-bold text-dark">No Users Found</h5>
                <p class="small mb-0">No users match the specified criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small">
                            <th class="ps-4">User</th>
                            <th>Role</th>
                            <th>Account Status</th>
                            <th>Farm / Activity</th>
                            <th>Registered</th>
                            <th class="text-end pe-4">Account Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-darkgreen fw-bold" style="width: 40px; height: 40px; min-width: 40px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                            <div class="text-xs text-muted">{{ $user->email }}</div>
                                            <div class="text-xs text-muted"><i class="bi bi-telephone me-1"></i>{{ $user->phone ?? 'No phone' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $roleBadges = [
                                            'admin' => 'bg-dark text-white',
                                            'seller' => 'bg-success-subtle text-success border border-success-subtle',
                                            'buyer' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        ];
                                    @endphp
                                    <span class="badge {{ $roleBadges[$user->role] ?? 'bg-light text-dark' }} text-capitalize px-2.5 py-1 rounded-pill text-xs">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->status === 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-xs">
                                            <i class="bi bi-check-circle-fill me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 text-xs">
                                            <i class="bi bi-slash-circle me-1"></i> Suspended
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->role === 'seller' && $user->sellerProfile)
                                        <div class="fw-medium text-dark text-sm">{{ $user->sellerProfile->farm_name }}</div>
                                        <div class="text-xs text-muted">{{ $user->sellerProfile->location }} ({{ ucfirst($user->sellerProfile->status) }})</div>
                                    @elseif($user->role === 'buyer')
                                        <div class="text-sm text-dark">{{ $user->orders->count() }} Orders Placed</div>
                                    @else
                                        <div class="text-xs text-muted">System Administrator</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-sm text-dark">{{ $user->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-muted">{{ $user->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="text-end pe-4">
                                    @if($user->id === auth()->id())
                                        <span class="text-xs text-muted fst-italic">Logged In (Self)</span>
                                    @elseif($user->status === 'active')
                                        <form action="{{ route('admin.users.suspend', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to suspend this user?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="bi bi-pause-circle me-1"></i> Suspend
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.users.reactivate', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Reactivate this user account?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                <i class="bi bi-play-circle me-1"></i> Reactivate
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
