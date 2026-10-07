<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PayoutController extends Controller
{
    /**
     * Display all seller payout requests with filters and stats.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        $query = PayoutRequest::with(['seller.sellerProfile', 'processor']);

        if ($status !== 'all' && in_array($status, ['pending', 'completed', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($request->filled('q')) {
            $search = trim($request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('account_title', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhere('transaction_reference', 'like', "%{$search}%")
                  ->orWhereHas('seller', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                             $spq->where('farm_name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        $payouts = $query->latest()->paginate(15)->withQueryString();

        // Financial KPIs
        $totalPendingAmount = PayoutRequest::where('status', 'pending')->sum('amount');
        $totalPendingCount = PayoutRequest::where('status', 'pending')->count();
        $totalCompletedAmount = PayoutRequest::where('status', 'completed')->sum('amount');
        $totalCompletedCount = PayoutRequest::where('status', 'completed')->count();

        return view('admin.payouts.index', [
            'payouts' => $payouts,
            'currentStatus' => $status,
            'totalPendingAmount' => $totalPendingAmount,
            'totalPendingCount' => $totalPendingCount,
            'totalCompletedAmount' => $totalCompletedAmount,
            'totalCompletedCount' => $totalCompletedCount,
        ]);
    }

    /**
     * Approve and mark payout request as completed / transferred.
     */
    public function approve(Request $request, PayoutRequest $payout): RedirectResponse
    {
        if (!$payout->isPending()) {
            return back()->with('error', 'This payout request has already been processed.');
        }

        $request->validate([
            'transaction_reference' => ['required', 'string', 'max:100'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
            'transaction_proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'], // 5MB max
        ], [
            'transaction_reference.required' => 'Please enter the bank/wallet transaction reference ID.',
        ]);

        $proofPath = null;
        if ($request->hasFile('transaction_proof')) {
            $proofPath = $request->file('transaction_proof')->store('payout_proofs', 'public');
        }

        $payout->update([
            'status' => 'completed',
            'transaction_reference' => trim($request->transaction_reference),
            'admin_notes' => $request->admin_notes,
            'transaction_proof' => $proofPath,
            'processed_at' => now(),
            'processed_by' => Auth::id(),
        ]);

        // Send real-time notification to the seller
        Notification::create([
            'user_id' => $payout->seller_id,
            'title' => 'Payout Transferred #' . $payout->reference_no,
            'message' => 'Your withdrawal of Rs ' . number_format($payout->amount) . ' has been approved and transferred via ' . ucfirst(str_replace('_', ' ', $payout->payment_method)) . '. TRX ID: ' . $payout->transaction_reference,
            'type' => 'payout_completed',
            'action_url' => route('seller.payouts.index'),
            'is_read' => false,
        ]);

        return back()->with('success', 'Payout #' . $payout->reference_no . ' approved and marked as transferred successfully.');
    }

    /**
     * Reject a payout request and restore funds to the seller's wallet.
     */
    public function reject(Request $request, PayoutRequest $payout): RedirectResponse
    {
        if (!$payout->isPending()) {
            return back()->with('error', 'This payout request has already been processed.');
        }

        $request->validate([
            'admin_notes' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'admin_notes.required' => 'Please provide a clear reason for rejecting the payout request.',
        ]);

        $payout->update([
            'status' => 'rejected',
            'admin_notes' => trim($request->admin_notes),
            'processed_at' => now(),
            'processed_by' => Auth::id(),
        ]);

        // Send notification to seller
        Notification::create([
            'user_id' => $payout->seller_id,
            'title' => 'Payout Request Declined #' . $payout->reference_no,
            'message' => 'Your payout request for Rs ' . number_format($payout->amount) . ' was rejected: ' . $payout->admin_notes . '. The amount remains available in your balance.',
            'type' => 'payout_rejected',
            'action_url' => route('seller.payouts.index'),
            'is_read' => false,
        ]);

        return back()->with('success', 'Payout request #' . $payout->reference_no . ' has been rejected and the funds returned to the seller balance.');
    }
}
