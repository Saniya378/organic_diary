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

        return view('seller.profile.edit', [
            'user' => $user,
            'profile' => $profile,
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
            'description' => ['required', 'string', 'max:2000'],
            'phone' => ['required', 'string', 'max:25'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
            'remove_photo' => ['nullable', 'boolean'],
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
            'description' => $validated['description'],
        ];

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

        return back()->with('success', 'Your farmer profile and farm images were updated successfully.');
    }
}
