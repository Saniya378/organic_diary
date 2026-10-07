<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PayoutController extends Controller
{
    /**
     * Display seller wallet and payout requests history.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        
        $lifetimeEarnings = $user->lifetime_earnings;
        $completedPayouts = $user->completed_payouts_total;
        $pendingPayouts = $user->pending_payouts_total;
        $availableBalance = $user->available_payout_balance;

        $payouts = PayoutRequest::where('seller_id', $user->id)
            ->latest()
            ->paginate(10);

        // Retrieve last used withdrawal destination to pre-populate modal
        $lastPayout = PayoutRequest::where('seller_id', $user->id)
            ->latest()
            ->first();

        return view('seller.payouts.index', [
            'lifetimeEarnings' => $lifetimeEarnings,
            'completedPayouts' => $completedPayouts,
            'pendingPayouts' => $pendingPayouts,
            'availableBalance' => $availableBalance,
            'payouts' => $payouts,
            'lastPayout' => $lastPayout,
        ]);
    }

    /**
     * Submit a new withdrawal / payout request.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $available = $user->available_payout_balance;

        $request->validate([
            'amount' => ['required', 'integer', 'min:500'],
            'payment_method' => ['required', 'string', 'in:easypaisa,jazzcash,sadapay,nayapay,bank_transfer'],
            'account_title' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100', 'required_if:payment_method,bank_transfer'],
            'seller_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.min' => 'The minimum withdrawal request amount is Rs 500.',
            'bank_name.required_if' => 'Please provide your Bank Name for bank transfer.',
        ]);

        if ($request->amount > $available) {
            return back()
                ->withInput()
                ->with('error', 'Requested amount (Rs ' . number_format($request->amount) . ') exceeds your available balance (Rs ' . number_format($available) . ').');
        }

        $payout = PayoutRequest::create([
            'seller_id' => $user->id,
            'reference_no' => PayoutRequest::generateReferenceNo(),
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'account_title' => trim($request->account_title),
            'account_number' => trim($request->account_number),
            'bank_name' => $request->payment_method === 'bank_transfer' ? trim($request->bank_name) : null,
            'status' => 'pending',
            'seller_notes' => $request->seller_notes,
        ]);

        // Send alert notification to all Admins
        $admins = User::where('role', 'admin')->get();
        $farmName = $user->sellerProfile->farm_name ?? $user->name;
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'New Payout Request #' . $payout->reference_no,
                'message' => "Farmer {$farmName} requested a payout of Rs " . number_format($payout->amount) . " via " . ucfirst(str_replace('_', ' ', $payout->payment_method)),
                'type' => 'payout_request',
                'action_url' => route('admin.payouts.index'),
                'is_read' => false,
            ]);
        }

        return redirect()->route('seller.payouts.index')
            ->with('success', 'Withdrawal request #' . $payout->reference_no . ' submitted for Rs ' . number_format($payout->amount) . '. Administration will verify and disburse the funds.');
    }

    /**
     * Cancel a pending withdrawal request.
     */
    public function cancel(PayoutRequest $payout): RedirectResponse
    {
        if ($payout->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$payout->isPending()) {
            return back()->with('error', 'Only pending payout requests can be cancelled.');
        }

        $payout->delete();

        return redirect()->route('seller.payouts.index')
            ->with('success', 'Payout request cancelled. Amount has been restored to your available balance.');
    }
}
