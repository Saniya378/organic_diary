<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\CommissionSettlement;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CommissionController extends Controller
{
    /**
     * Display seller's platform commission ledger, dues, and payment submission UI.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $totalDirectSales = (int) $user->sellerOrders()
            ->where('status', 'delivered')
            ->sum('total');

        $totalCommissionAccrued = $user->total_commission_accrued;
        $totalCommissionSettled = $user->total_commission_settled;
        $pendingCommissionSettlements = $user->pending_commission_settlements_total;
        $outstandingDue = $user->outstanding_commission_due;

        $settlements = CommissionSettlement::where('seller_id', $user->id)
            ->latest()
            ->paginate(10);

        // Official Admin Accounts for commission deposit
        $adminBankDetails = [
            'bank_name' => Setting::get('bank_name', 'Meezan Bank Ltd'),
            'bank_account_title' => Setting::get('bank_account_title', 'Organic Dairy Gilgit-Baltistan'),
            'bank_account_number' => Setting::get('bank_account_number', '01020304050607'),
            'bank_iban' => Setting::get('bank_iban', 'PK36MEZN0001020304050607'),
            'bank_branch' => Setting::get('bank_branch', 'Gilgit City Branch'),
            'easypaisa_title' => Setting::get('easypaisa_title', 'Organic Dairy Gilgit'),
            'easypaisa_number' => Setting::get('easypaisa_number', '0345-1234567'),
            'jazzcash_title' => Setting::get('jazzcash_title', 'Organic Dairy Gilgit'),
            'jazzcash_number' => Setting::get('jazzcash_number', '0300-7654321'),
        ];

        return view('seller.commissions.index', [
            'totalDirectSales' => $totalDirectSales,
            'totalCommissionAccrued' => $totalCommissionAccrued,
            'totalCommissionSettled' => $totalCommissionSettled,
            'pendingCommissionSettlements' => $pendingCommissionSettlements,
            'outstandingDue' => $outstandingDue,
            'settlements' => $settlements,
            'adminBankDetails' => $adminBankDetails,
        ]);
    }

    /**
     * Submit a commission payment proof to administration.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'payment_channel' => ['required', 'string', 'in:easypaisa,jazzcash,sadapay,nayapay,bank_transfer'],
            'transaction_id' => ['required', 'string', 'max:100'],
            'proof_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'seller_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $proofImagePath = null;
        if ($request->hasFile('proof_image') && $request->file('proof_image')->isValid()) {
            $proofImagePath = $request->file('proof_image')->store('commission_proofs', 'public');
        }

        $settlement = CommissionSettlement::create([
            'seller_id' => $user->id,
            'amount' => $request->amount,
            'payment_channel' => $request->payment_channel,
            'transaction_id' => trim($request->transaction_id),
            'proof_image' => $proofImagePath,
            'status' => 'pending',
            'seller_notes' => $request->seller_notes,
        ]);

        // Send alert notification to Admins
        $admins = User::where('role', 'admin')->get();
        $farmName = $user->sellerProfile->farm_name ?? $user->name;
        foreach ($admins as $admin) {
            Notification::sendTo(
                $admin->id,
                "New Commission Settlement from {$farmName}",
                "Farmer {$farmName} submitted a commission deposit of Rs " . number_format($settlement->amount) . " (TRX: {$settlement->transaction_id}).",
                route('admin.commissions.index'),
                'commission_settlement',
                'bi-cash-coin'
            );
        }

        return redirect()->route('seller.commissions.index')
            ->with('success', 'Your commission payment submission for Rs ' . number_format($settlement->amount) . ' has been recorded. Admin will verify your TRX ID.');
    }
}
