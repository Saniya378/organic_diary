<?php

namespace App\Http\Controllers;

use App\Http\Requests\Address\AddressRequest;
use App\Models\Address;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $addresses = $user->addresses()->orderBy('is_default', 'desc')->latest()->get();
        $allowedCitiesSetting = Setting::get('allowed_cities', '["Gilgit", "Skardu", "Hunza", "Ghizer", "Astore", "Nagar", "Diamer"]');
        $allowedCities = json_decode($allowedCitiesSetting, true) ?: ['Gilgit', 'Skardu', 'Hunza', 'Ghizer', 'Astore', 'Nagar', 'Diamer'];

        return view('addresses.index', compact('addresses', 'allowedCities'));
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();
        $isDefault = $request->boolean('is_default') || $user->addresses()->count() === 0;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'area' => $validated['area'],
            'address_line' => $validated['address_line'],
            'is_default' => $isDefault,
        ]);

        return redirect()->route('addresses.index')->with('success', 'New delivery address added successfully.');
    }

    public function update(AddressRequest $request, Address $address): RedirectResponse
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validated();
        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            Auth::user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'area' => $validated['area'],
            'address_line' => $validated['address_line'],
            'is_default' => $isDefault,
        ]);

        return redirect()->route('addresses.index')->with('success', 'Address updated successfully.');
    }

    public function destroy(Address $address): RedirectResponse
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $firstRemaining = Auth::user()->addresses()->first();
            if ($firstRemaining) {
                $firstRemaining->update(['is_default' => true]);
            }
        }

        return redirect()->route('addresses.index')->with('success', 'Address deleted successfully.');
    }

    public function setDefault(Address $address): RedirectResponse
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        Auth::user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return redirect()->route('addresses.index')->with('success', 'Default address updated.');
    }
}
