<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealDelivery;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DealController extends Controller
{
    /**
     * Seller's Deals Management & Today's Delivery Runs
     */
    public function index(Request $request): View
    {
        $sellerId = Auth::id();
        $statusFilter = $request->query('status');
        $durationFilter = $request->query('duration');

        $dealsQuery = Deal::where('seller_id', $sellerId)
            ->with(['buyer', 'product.images', 'address', 'deliveries'])
            ->latest();

        if ($statusFilter && in_array($statusFilter, ['active', 'paused', 'completed', 'cancelled'])) {
            $dealsQuery->where('status', $statusFilter);
        }

        if ($durationFilter && in_array($durationFilter, ['3_days', '1_week', '1_month'])) {
            $dealsQuery->where('duration_type', $durationFilter);
        }

        $deals = $dealsQuery->paginate(12)->withQueryString();

        // Today's scheduled deliveries for this farm
        $today = Carbon::today()->toDateString();
        $todayDeliveries = DealDelivery::whereHas('deal', function ($q) use ($sellerId) {
            $q->where('seller_id', $sellerId)->where('status', 'active');
        })
        ->where('scheduled_date', $today)
        ->with(['deal.buyer', 'deal.product', 'deal.address'])
        ->orderByRaw("FIELD(deal_id, 1) ASC")
        ->get();

        // KPIs
        $kpis = [
            'active_deals' => Deal::where('seller_id', $sellerId)->where('status', 'active')->count(),
            'today_pending_deliveries' => $todayDeliveries->where('status', 'scheduled')->count(),
            'completed_deals' => Deal::where('seller_id', $sellerId)->where('status', 'completed')->count(),
            'total_deals_revenue' => (int) Deal::where('seller_id', $sellerId)->whereIn('status', ['active', 'completed'])->sum('total_amount'),
            'count_3_days' => Deal::where('seller_id', $sellerId)->where('duration_type', '3_days')->count(),
            'count_1_week' => Deal::where('seller_id', $sellerId)->where('duration_type', '1_week')->count(),
            'count_1_month' => Deal::where('seller_id', $sellerId)->where('duration_type', '1_month')->count(),
        ];

        $sellerProducts = \App\Models\Product::where('seller_id', $sellerId)->with('category')->get();

        return view('seller.deals.index', [
            'deals' => $deals,
            'todayDeliveries' => $todayDeliveries,
            'sellerProducts' => $sellerProducts,
            'kpis' => $kpis,
            'currentStatus' => $statusFilter,
            'currentDuration' => $durationFilter,
        ]);
    }

    /**
     * Seller view of specific deal schedule
     */
    public function show(Deal $deal): View
    {
        if ($deal->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this deal.');
        }

        $deal->load(['buyer', 'product.images', 'address', 'deliveries']);

        return view('seller.deals.show', [
            'deal' => $deal,
        ]);
    }

    /**
     * Mark single day delivery as delivered/dispatched
     */
    public function markDelivery(DealDelivery $delivery, Request $request): RedirectResponse|JsonResponse
    {
        $deal = $delivery->deal;

        if ($deal->seller_id !== Auth::id()) {
            abort(403);
        }

        $status = $request->input('status', 'delivered');
        if (!in_array($status, ['delivered', 'dispatched', 'skipped'])) {
            $status = 'delivered';
        }

        $delivery->update([
            'status' => $status,
            'delivered_at' => $status === 'delivered' ? now() : null,
            'notes' => $request->input('notes'),
        ]);

        // Recalculate delivered days
        $deliveredCount = $deal->deliveries()->where('status', 'delivered')->count();
        $deal->update([
            'delivered_days' => $deliveredCount,
        ]);

        // Auto complete deal if all days delivered
        if ($deliveredCount >= $deal->total_days) {
            $deal->update([
                'status' => 'completed',
                'payment_status' => 'paid',
            ]);

            Notification::create([
                'user_id' => $deal->buyer_id,
                'title' => 'Deal Completed! 🎉',
                'body' => "Congratulations! All {$deal->total_days} days of your {$deal->title} have been fulfilled.",
                'type' => 'deal',
            ]);
        } else if ($status === 'delivered') {
            Notification::create([
                'user_id' => $deal->buyer_id,
                'title' => "Day {$delivery->day_number} Delivered",
                'body' => "Your daily fresh batch of {$deal->product->name} ({$deal->quantity_per_day}x) has been delivered to your doorstep.",
                'type' => 'deal',
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $status,
                'delivered_days' => $deal->fresh()->delivered_days,
                'deal_status' => $deal->fresh()->status,
                'message' => "Day {$delivery->day_number} marked as {$status}.",
            ]);
        }

        return back()->with('success', "Day {$delivery->day_number} marked as {$status} successfully.");
    }
}
