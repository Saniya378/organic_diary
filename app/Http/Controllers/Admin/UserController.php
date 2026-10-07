<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display listing of users with search and role/status filters.
     */
    public function index(Request $request): View
    {
        $roleFilter = $request->query('role');
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $query = User::with(['sellerProfile', 'orders', 'sellerOrders'])->latest();

        if ($roleFilter && in_array($roleFilter, ['buyer', 'seller', 'admin'])) {
            $query->where('role', $roleFilter);
        }

        if ($statusFilter && in_array($statusFilter, ['active', 'suspended'])) {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'currentRole' => $roleFilter,
            'currentStatus' => $statusFilter,
            'search' => $search,
        ]);
    }

    /**
     * Suspend a user account.
     */
    public function suspend(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot suspend your own admin account.');
        }

        $user->update(['status' => 'suspended']);

        if ($user->role === 'seller' && $user->sellerProfile) {
            $user->sellerProfile->update(['status' => 'suspended']);
        }

        return back()->with('success', "User '{$user->name}' has been suspended.");
    }

    /**
     * Reactivate a suspended user account.
     */
    public function reactivate(User $user): RedirectResponse
    {
        $user->update(['status' => 'active']);

        if ($user->role === 'seller' && $user->sellerProfile) {
            // Restore to approved if they were previously approved
            $user->sellerProfile->update(['status' => 'approved']);
        }

        return back()->with('success', "User '{$user->name}' has been reactivated.");
    }
}
