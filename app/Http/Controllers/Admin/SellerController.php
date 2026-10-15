<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SellerApprovedMail;
use App\Mail\SellerRejectedMail;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SellerController extends Controller
{
    /**
     * Display a listing of all sellers with status and search filters.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $query = SellerProfile::with('user')->latest();

        if ($statusFilter && in_array($statusFilter, ['approved', 'pending', 'rejected', 'suspended'])) {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('farm_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('cnic_no', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $sellers = $query->paginate(15)->withQueryString();

        return view('admin.sellers.index', [
            'sellers' => $sellers,
            'currentStatus' => $statusFilter,
            'search' => $search,
        ]);
    }

    /**
     * Approve seller farm profile.
     */
    public function approve(SellerProfile $sellerProfile): RedirectResponse
    {
        $sellerProfile->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        if ($sellerProfile->user) {
            $sellerProfile->user->update(['status' => 'active']);

            try {
                \App\Models\Notification::sendTo(
                    $sellerProfile->user_id,
                    "Farm Profile Approved!",
                    "Congratulations! Your farm profile '{$sellerProfile->farm_name}' has been approved by admin. Your products are now visible in the marketplace.",
                    route('seller.dashboard'),
                    'profile_approved',
                    'bi-patch-check-fill'
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Notification error on seller approval: ' . $e->getMessage());
            }

            try {
                Mail::to($sellerProfile->user->email)->send(new SellerApprovedMail($sellerProfile->user));
            } catch (\Exception $e) {
                // Continue
            }
        }

        return back()->with('success', "Seller farm '{$sellerProfile->farm_name}' approved successfully.");
    }

    /**
     * Reject seller farm profile with required reason.
     */
    public function reject(Request $request, SellerProfile $sellerProfile): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $reason = $request->input('rejection_reason');

        $sellerProfile->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        if ($sellerProfile->user) {
            try {
                \App\Models\Notification::sendTo(
                    $sellerProfile->user_id,
                    "Farm Verification Action Required",
                    "Your farm profile application was reviewed and rejected. Reason: {$reason}. You can edit your profile details and re-apply.",
                    route('seller.profile.edit'),
                    'profile_rejected',
                    'bi-exclamation-octagon-fill'
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Notification error on seller rejection: ' . $e->getMessage());
            }

            try {
                Mail::to($sellerProfile->user->email)->send(new SellerRejectedMail($sellerProfile->user, $reason));
            } catch (\Exception $e) {
                // Continue
            }
        }

        return back()->with('success', "Seller farm '{$sellerProfile->farm_name}' was rejected and notification sent.");
    }

    /**
     * Suspend seller profile and user account (products will disappear from the shop).
     */
    public function suspend(SellerProfile $sellerProfile): RedirectResponse
    {
        $sellerProfile->update([
            'status' => 'suspended',
        ]);

        if ($sellerProfile->user) {
            $sellerProfile->user->update(['status' => 'suspended']);

            try {
                \App\Models\Notification::sendTo(
                    $sellerProfile->user_id,
                    "Account Suspended",
                    "Your farm profile and store have been temporarily suspended by the administrator.",
                    route('seller.dashboard'),
                    'account_suspended',
                    'bi-shield-x'
                );
            } catch (\Exception $e) {
                // Continue
            }
        }

        return back()->with('success', "Seller farm '{$sellerProfile->farm_name}' suspended. Their products are now hidden from the public shop.");
    }

    /**
     * Reactivate a suspended seller.
     */
    public function reactivate(SellerProfile $sellerProfile): RedirectResponse
    {
        $sellerProfile->update([
            'status' => 'approved',
        ]);

        if ($sellerProfile->user) {
            $sellerProfile->user->update(['status' => 'active']);

            try {
                \App\Models\Notification::sendTo(
                    $sellerProfile->user_id,
                    "Account Reactivated",
                    "Your seller account and farm listings have been reactivated. Welcome back!",
                    route('seller.dashboard'),
                    'account_reactivated',
                    'bi-patch-check-fill'
                );
            } catch (\Exception $e) {
                // Continue
            }
        }

        return back()->with('success', "Seller farm '{$sellerProfile->farm_name}' has been reactivated.");
    }
}
