@auth
<div class="dropdown d-inline-block position-relative notif-dropdown-wrapper">
    <button class="btn btn-outline-custom position-relative p-2 px-2.5 rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" 
            type="button" 
            id="notificationDropdownBtn" 
            data-bs-toggle="dropdown" 
            aria-expanded="false" 
            title="Notifications"
            style="width: 40px; height: 40px;">
        <i class="bi bi-bell fs-5 text-dark"></i>
        <span id="globalNotifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow-sm" style="font-size: 0.65rem; display: none;">
            0
        </span>
    </button>

    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 rounded-4 p-0 notif-dropdown-menu" 
         aria-labelledby="notificationDropdownBtn" 
         style="width: 360px; max-width: 90vw; border: 1px solid var(--od-border) !important; z-index: 1060;">
        
        <!-- Dropdown Header -->
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light rounded-top-4">
            <div class="d-flex align-items-center gap-2">
                <h6 class="mb-0 fw-bold text-dark heading-serif">Notifications</h6>
                <span id="notifHeaderCount" class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5" style="font-size: 0.7rem; display: none;">0 unread</span>
            </div>
            <button type="button" class="btn btn-sm btn-link p-0 text-success text-decoration-none fw-semibold small" id="markAllReadBtn" style="font-size: 0.78rem;">
                <i class="bi bi-check2-all me-1"></i> Mark all read
            </button>
        </div>

        <!-- Notification Items List -->
        <div id="notifItemsContainer" class="overflow-y-auto" style="max-height: 360px;">
            <div class="text-center py-4 text-muted small" id="notifLoadingState">
                <div class="spinner-border spinner-border-sm text-success me-1" role="status"></div> Loading updates...
            </div>
            <div class="text-center py-4 text-muted small d-none" id="notifEmptyState">
                <i class="bi bi-bell-slash fs-3 text-muted d-block mb-1 opacity-50"></i>
                No notifications right now.
            </div>
            <div id="notifList"></div>
        </div>

        <!-- Dropdown Footer -->
        <div class="p-2.5 border-top text-center bg-light rounded-bottom-4">
            <a href="{{ route('notifications.index') }}" class="small text-decoration-none fw-bold text-success">
                View All Notifications <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

<!-- Floating Real-Time Live Toast Notification Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;" id="liveToastContainer"></div>

<script>
    (function() {
        let lastNotifIds = new Set();
        let isFirstLoad = true;

        async function fetchNotifications() {
            try {
                const response = await fetch("{{ route('notifications.index') }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) return;
                const data = await response.json();

                updateNotificationBadge(data.unread_count);
                renderNotificationList(data.notifications, data.unread_count);

                // Detect new notifications and pop live toast
                if (!isFirstLoad && data.notifications && data.notifications.length > 0) {
                    data.notifications.forEach(n => {
                        if (!n.is_read && !lastNotifIds.has(n.id)) {
                            showLiveToast(n);
                        }
                    });
                }

                if (data.notifications) {
                    lastNotifIds = new Set(data.notifications.map(n => n.id));
                }
                isFirstLoad = false;
            } catch (err) {
                console.error('Notification poll error:', err);
            }
        }

        function updateNotificationBadge(unreadCount) {
            const badge = document.getElementById('globalNotifBadge');
            const headerCount = document.getElementById('notifHeaderCount');

            if (badge) {
                if (unreadCount > 0) {
                    badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    badge.style.display = 'inline-block';
                    badge.classList.add('animate__animated', 'animate__pulse');
                } else {
                    badge.style.display = 'none';
                }
            }

            if (headerCount) {
                if (unreadCount > 0) {
                    headerCount.textContent = `${unreadCount} unread`;
                    headerCount.style.display = 'inline-block';
                } else {
                    headerCount.style.display = 'none';
                }
            }
        }

        function renderNotificationList(notifications, unreadCount) {
            const listEl = document.getElementById('notifList');
            const loadingEl = document.getElementById('notifLoadingState');
            const emptyEl = document.getElementById('notifEmptyState');

            if (!listEl) return;
            if (loadingEl) loadingEl.classList.add('d-none');

            if (!notifications || notifications.length === 0) {
                listEl.innerHTML = '';
                if (emptyEl) emptyEl.classList.remove('d-none');
                return;
            }

            if (emptyEl) emptyEl.classList.add('d-none');

            let html = '';
            notifications.forEach(n => {
                const unreadClass = n.is_read ? 'bg-white text-muted' : 'bg-success bg-opacity-10 text-dark fw-semibold';
                const unreadDot = !n.is_read ? '<span class="rounded-circle bg-success flex-shrink-0" style="width: 8px; height: 8px;"></span>' : '';
                
                html += `
                    <a href="${n.action_url}" class="d-flex align-items-start gap-2.5 p-3 border-bottom text-decoration-none notif-item transition-all ${unreadClass}" style="border-color: #F0ECE1 !important;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm ${n.is_read ? 'bg-light text-muted' : 'bg-white text-success'}" style="width: 36px; height: 36px; border: 1px solid var(--od-border);">
                            <i class="bi ${n.icon || 'bi-bell'} fs-6"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between mb-0.5">
                                <span class="small ${n.is_read ? 'fw-semibold text-dark' : 'fw-bold text-dark-green'} text-truncate d-block">${n.title}</span>
                                ${unreadDot}
                            </div>
                            <p class="small text-muted mb-1 text-truncate-2" style="font-size: 0.78rem; line-height: 1.35;">${n.body}</p>
                            <span class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i>${n.created_at_human}</span>
                        </div>
                    </a>
                `;
            });

            listEl.innerHTML = html;
        }

        function showLiveToast(notification) {
            const container = document.getElementById('liveToastContainer');
            if (!container) return;

            const toastId = 'toast-' + notification.id + '-' + Date.now();
            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center bg-white border-0 shadow-lg rounded-4 mb-2 show" role="alert" aria-live="assertive" aria-atomic="true" style="border-left: 4px solid var(--od-primary) !important; max-width: 340px;">
                    <div class="d-flex p-3 align-items-start gap-2">
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 34px; height: 34px;">
                            <i class="bi ${notification.icon || 'bi-bell'} fs-6"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <strong class="d-block text-dark small fw-bold text-truncate">${notification.title}</strong>
                            <small class="text-muted text-truncate-2 d-block mb-1" style="font-size: 0.75rem;">${notification.body}</small>
                            <a href="${notification.action_url}" class="btn btn-sm btn-primary py-0 px-2 rounded-pill fw-bold" style="font-size: 0.7rem;">
                                View Now &rarr;
                            </a>
                        </div>
                        <button type="button" class="btn-close ms-auto p-1" data-bs-dismiss="toast" aria-label="Close" style="font-size: 0.7rem;"></button>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', toastHtml);

            // Auto dismiss toast after 8 seconds
            setTimeout(() => {
                const el = document.getElementById(toastId);
                if (el) el.remove();
            }, 8000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            fetchNotifications();

            // Real-time polling every 8 seconds
            setInterval(fetchNotifications, 8000);

            // Mark all read button handler
            const markAllBtn = document.getElementById('markAllReadBtn');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    try {
                        await fetch("{{ route('notifications.mark-all-read') }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        fetchNotifications();
                    } catch (err) {
                        console.error('Mark all read error:', err);
                    }
                });
            }
        });
    })();
</script>

<style>
    .notif-item:hover {
        background-color: #FAF7F0 !important;
    }
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endauth
