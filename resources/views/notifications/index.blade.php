@extends(Auth::user()->role === 'admin' || Auth::user()->role === 'seller' ? 'layouts.panel' : 'layouts.public')

@section('title', 'Notifications - Organic Dairy')
@section('page_heading', 'Notifications')

@if(Auth::user()->role === 'seller')
    @section('menu_items')
        @include('seller.partials.menu')
    @endsection
@endif

@section('content')
<div class="{{ Auth::user()->role === 'buyer' ? 'container py-5' : 'container-fluid p-0' }}">
    <div class="row g-4 justify-content-center">
        <div class="col-lg-10">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h3 class="heading-serif fw-bold text-dark-green mb-1">
                            <i class="bi bi-bell-fill text-gold me-2"></i> Your Notifications
                        </h3>
                        <p class="text-muted small mb-0">
                            Real-time order progression updates, payment status alerts, and announcements.
                        </p>
                    </div>

                    @if($notifications->whereNull('read_at')->count() > 0)
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-darkgreen rounded-pill px-3 py-2 fw-semibold btn-sm shadow-sm">
                                <i class="bi bi-check2-all me-1"></i> Mark All as Read
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Notifications List -->
            @if($notifications->isEmpty())
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-bell-slash fs-2 text-muted"></i>
                    </div>
                    <h5 class="heading-serif text-dark mb-1">No Notifications Yet</h5>
                    <p class="text-muted small mb-4">When you receive new orders, status changes, or payment confirmations, they will appear right here.</p>
                    <a href="{{ Auth::user()->isSeller() ? route('seller.dashboard') : route('shop.index') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center mx-auto">
                        <i class="bi bi-arrow-left me-2"></i> {{ Auth::user()->isSeller() ? 'Go to Dashboard' : 'Explore Farm Products' }}
                    </a>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $n)
                            <a href="{{ route('notifications.open', $n->id) }}" 
                               class="list-group-item list-group-item-action p-4 d-flex align-items-start gap-3 border-bottom transition-all {{ is_null($n->read_at) ? 'bg-success bg-opacity-10' : 'bg-white' }}"
                               style="border-color: #F0ECE1 !important;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm {{ is_null($n->read_at) ? 'bg-white text-success' : 'bg-light text-muted' }}" 
                                     style="width: 46px; height: 46px; border: 1px solid var(--od-border);">
                                    <i class="bi {{ $n->icon ?: 'bi-bell' }} fs-5"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="mb-0 {{ is_null($n->read_at) ? 'fw-bold text-dark-green' : 'fw-semibold text-dark' }}">
                                            {{ $n->title }}
                                        </h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <i class="bi bi-clock me-1"></i> {{ $n->created_at->diffForHumans() }}
                                            </small>
                                            @if(is_null($n->read_at))
                                                <span class="badge bg-success rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">New</span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-2" style="font-size: 0.88rem; line-height: 1.4;">
                                        {{ $n->body }}
                                    </p>
                                    @if($n->action_url)
                                        <span class="small fw-bold text-success text-decoration-none d-inline-flex align-items-center">
                                            View Details &rarr;
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
