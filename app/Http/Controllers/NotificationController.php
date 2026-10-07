<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Get list of notifications (or JSON for real-time polling).
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();

        if ($request->wantsJson() || $request->ajax()) {
            $notifications = Notification::where('user_id', $user->id)
                ->latest()
                ->take(15)
                ->get()
                ->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'type' => $n->type,
                        'title' => $n->title,
                        'body' => $n->body,
                        'icon' => $n->icon ?: 'bi-bell',
                        'action_url' => route('notifications.open', $n->id),
                        'direct_url' => $n->action_url,
                        'is_read' => !is_null($n->read_at),
                        'created_at_human' => $n->created_at ? $n->created_at->diffForHumans() : '',
                        'created_at_formatted' => $n->created_at ? $n->created_at->format('M d, h:i A') : '',
                    ];
                });

            $unreadCount = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount,
                'notifications' => $notifications,
            ]);
        }

        $allNotifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('notifications.index', [
            'notifications' => $allNotifications,
        ]);
    }

    /**
     * Click notification: mark as read and redirect directly to target URL.
     */
    public function openAndRedirect(Notification $notification): RedirectResponse
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to notification.');
        }

        $notification->markAsRead();

        if (!empty($notification->action_url)) {
            return redirect($notification->action_url);
        }

        if (Auth::user()->isSeller()) {
            return redirect()->route('seller.dashboard');
        }

        return redirect()->route('orders.index');
    }

    /**
     * Mark a single notification as read via AJAX or POST.
     */
    public function markAsRead(Notification $notification): JsonResponse|RedirectResponse
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $notification->markAsRead();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse|RedirectResponse
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
