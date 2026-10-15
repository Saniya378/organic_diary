<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the seller's farm profile.
     */
    public function edit(): View
    {
        $user = Auth::user();
        $profile = $user->sellerProfile ?? new SellerProfile(['user_id' => $user->id]);

        $allowedCitiesSetting = \App\Models\Setting::get('allowed_cities', '[]');
        $allowedCities = is_string($allowedCitiesSetting) ? json_decode($allowedCitiesSetting, true) : $allowedCitiesSetting;
        if (!is_array($allowedCities) || empty($allowedCities)) {
            $allowedCities = ['Gilgit', 'Skardu', 'Hunza', 'Ghizer', 'Astore', 'Nagar', 'Diamer'];
        }

        return view('seller.profile.edit', [
            'user' => $user,
            'profile' => $profile,
            'allowedCities' => $allowedCities,
        ]);
    }

    /**
     * Update the seller's farm profile, user avatar, and contact phone.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'farm_name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'serviceable_cities' => ['nullable', 'array'],
            'serviceable_cities.*' => ['string', 'max:100'],
            'serviceable_areas' => ['nullable', 'string', 'max:1000'],
            'delivery_fee' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'free_delivery_threshold' => ['nullable', 'integer', 'min:0', 'max:50000'],
            'min_order_amount' => ['nullable', 'integer', 'min:0', 'max:50000'],
            'description' => ['required', 'string', 'max:2000'],
            'phone' => ['required', 'string', 'max:25'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_title' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_iban' => ['nullable', 'string', 'max:50'],
            'bank_branch' => ['nullable', 'string', 'max:100'],
            'easypaisa_title' => ['nullable', 'string', 'max:100'],
            'easypaisa_number' => ['nullable', 'string', 'max:50'],
            'jazzcash_title' => ['nullable', 'string', 'max:100'],
            'jazzcash_number' => ['nullable', 'string', 'max:50'],
            'payment_instructions' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
            'remove_photo' => ['nullable', 'boolean'],
            'cnic_no' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]{5}-?[0-9]{7}-?[0-9]{1}$/'],
            'cnic_front' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
            'cnic_back' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'],
        ]);

        // Handle User Avatar (Personal Profile Image)
        $userUpdateData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ];

        if ($request->boolean('remove_avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $userUpdateData['avatar'] = null;
        } elseif ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $userUpdateData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($userUpdateData);

        // Handle Farm Profile Info & Cover Photo
        $profileData = [
            'farm_name' => $validated['farm_name'],
            'location' => $validated['location'],
            'serviceable_cities' => $request->input('serviceable_cities', []),
            'serviceable_areas' => $request->input('serviceable_areas'),
            'delivery_fee' => $request->filled('delivery_fee') ? (int) $request->input('delivery_fee') : null,
            'free_delivery_threshold' => $request->filled('free_delivery_threshold') ? (int) $request->input('free_delivery_threshold') : null,
            'min_order_amount' => $request->filled('min_order_amount') ? (int) $request->input('min_order_amount') : null,
            'description' => $validated['description'],
            'bank_name' => $request->input('bank_name'),
            'bank_account_title' => $request->input('bank_account_title'),
            'bank_account_number' => $request->input('bank_account_number'),
            'bank_iban' => $request->input('bank_iban'),
            'bank_branch' => $request->input('bank_branch'),
            'easypaisa_title' => $request->input('easypaisa_title'),
            'easypaisa_number' => $request->input('easypaisa_number'),
            'jazzcash_title' => $request->input('jazzcash_title'),
            'jazzcash_number' => $request->input('jazzcash_number'),
            'payment_instructions' => $request->input('payment_instructions'),
        ];

        if ($request->filled('cnic_no')) {
            $profileData['cnic_no'] = $request->input('cnic_no');
        }

        if ($request->hasFile('cnic_front') && $request->file('cnic_front')->isValid()) {
            if ($user->sellerProfile && $user->sellerProfile->cnic_front && Storage::disk('public')->exists($user->sellerProfile->cnic_front)) {
                Storage::disk('public')->delete($user->sellerProfile->cnic_front);
            }
            $profileData['cnic_front'] = $request->file('cnic_front')->store('seller_cnic', 'public');
        }

        if ($request->hasFile('cnic_back') && $request->file('cnic_back')->isValid()) {
            if ($user->sellerProfile && $user->sellerProfile->cnic_back && Storage::disk('public')->exists($user->sellerProfile->cnic_back)) {
                Storage::disk('public')->delete($user->sellerProfile->cnic_back);
            }
            $profileData['cnic_back'] = $request->file('cnic_back')->store('seller_cnic', 'public');
        }

        // If seller was rejected and is re-submitting profile/documents, re-open for review
        if ($user->sellerProfile && $user->sellerProfile->status === 'rejected') {
            $profileData['status'] = 'pending';
            $profileData['rejection_reason'] = null;
        }

        if ($request->boolean('remove_photo')) {
            if ($user->sellerProfile && $user->sellerProfile->photo_path && Storage::disk('public')->exists($user->sellerProfile->photo_path)) {
                Storage::disk('public')->delete($user->sellerProfile->photo_path);
            }
            $profileData['photo_path'] = null;
        } elseif ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            if ($user->sellerProfile && $user->sellerProfile->photo_path && Storage::disk('public')->exists($user->sellerProfile->photo_path)) {
                Storage::disk('public')->delete($user->sellerProfile->photo_path);
            }
            $profileData['photo_path'] = $request->file('photo')->store('sellers', 'public');
        }

        SellerProfile::updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return back()->with('success', 'Your farmer profile and verification details were updated successfully.');
    }
}
