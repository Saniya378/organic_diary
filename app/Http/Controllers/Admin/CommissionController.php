<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionSettlement;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CommissionController extends Controller
{
    /**
     * Display listing of commission settlements submitted by sellers.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $query = CommissionSettlement::with(['seller.sellerProfile', 'verifiedByUser'])->latest();

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('seller', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                             $spq->where('farm_name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        $settlements = $query->paginate(15)->withQueryString();

        $totalApprovedSum = CommissionSettlement::where('status', 'approved')->sum('amount');
        $totalPendingCount = CommissionSettlement::where('status', 'pending')->count();
        $totalPendingSum = CommissionSettlement::where('status', 'pending')->sum('amount');

        return view('admin.commissions.index', [
            'settlements' => $settlements,
            'currentStatus' => $statusFilter,
            'search' => $search,
            'totalApprovedSum' => $totalApprovedSum,
            'totalPendingCount' => $totalPendingCount,
            'totalPendingSum' => $totalPendingSum,
        ]);
    }

    /**
     * Approve a seller commission deposit.
     */
    public function approve(Request $request, CommissionSettlement $settlement): RedirectResponse
    {
        if ($settlement->isApproved()) {
            return back()->with('info', 'This commission payment is already approved.');
        }

        $settlement->update([
            'status' => 'approved',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'admin_notes' => $request->input('admin_notes', 'Verified and cleared by administration.'),
        ]);

        Notification::sendTo(
            $settlement->seller_id,
            'Commission Payment Approved',
            'Your commission deposit of Rs ' . number_format($settlement->amount) . ' (TRX: ' . $settlement->transaction_id . ') has been verified and cleared.',
            route('seller.commissions.index'),
            'commission_approved',
            'bi-check-circle-fill'
        );

        return back()->with('success', 'Commission payment of Rs ' . number_format($settlement->amount) . ' approved successfully.');
    }

    /**
     * Reject a seller commission deposit.
     */
    public function reject(Request $request, CommissionSettlement $settlement): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ]);

        $settlement->update([
            'status' => 'rejected',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        Notification::sendTo(
            $settlement->seller_id,
            'Commission Payment Rejected',
            'Your commission deposit of Rs ' . number_format($settlement->amount) . ' was rejected: ' . $request->input('admin_notes'),
            route('seller.commissions.index'),
            'commission_rejected',
            'bi-x-circle-fill'
        );

        return back()->with('success', 'Commission payment marked as rejected.');
    }
}
