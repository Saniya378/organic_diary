<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the settings configuration screen.
     */
    public function index(): View
    {
        $deliveryFee = Setting::get('delivery_fee', '150');
        $commissionPercent = Setting::get('commission_percent', '5');
        $lowStockThreshold = Setting::get('low_stock_threshold', '10');

        $allowedCitiesRaw = Setting::get('allowed_cities', '[]');
        $allowedCities = is_string($allowedCitiesRaw) ? json_decode($allowedCitiesRaw, true) : $allowedCitiesRaw;
        if (!is_array($allowedCities)) {
            $allowedCities = ['Gilgit', 'Skardu', 'Hunza', 'Ghizer', 'Astore', 'Nagar', 'Diamer'];
        }

        $dealDiscount3Days = Setting::get('deal_discount_3_days', '5');
        $dealDiscount1Week = Setting::get('deal_discount_1_week', '10');
        $dealDiscount1Month = Setting::get('deal_discount_1_month', '15');

        // Bank and Online Transfer Payment Settings
        $bankName = Setting::get('bank_name', 'Meezan Bank Ltd');
        $bankAccountTitle = Setting::get('bank_account_title', 'Organic Dairy Gilgit-Baltistan');
        $bankAccountNumber = Setting::get('bank_account_number', '01020304050607');
        $bankIban = Setting::get('bank_iban', 'PK36MEZN0001020304050607');
        $bankBranch = Setting::get('bank_branch', 'Gilgit City Branch');

        $easypaisaTitle = Setting::get('easypaisa_title', 'Organic Dairy Gilgit');
        $easypaisaNumber = Setting::get('easypaisa_number', '0345-1234567');

        $jazzcashTitle = Setting::get('jazzcash_title', 'Organic Dairy Gilgit');
        $jazzcashNumber = Setting::get('jazzcash_number', '0300-7654321');

        $paymentInstructions = Setting::get('payment_instructions', 'Please transfer the exact order amount to any of our official accounts listed above. Once the transfer is complete, enter your Transaction ID (TRX ID) and upload payment proof. Our administration team will verify and approve your order.');

        return view('admin.settings.index', [
            'deliveryFee' => $deliveryFee,
            'commissionPercent' => $commissionPercent,
            'lowStockThreshold' => $lowStockThreshold,
            'allowedCities' => implode(', ', $allowedCities),
            'dealDiscount3Days' => $dealDiscount3Days,
            'dealDiscount1Week' => $dealDiscount1Week,
            'dealDiscount1Month' => $dealDiscount1Month,
            'bankName' => $bankName,
            'bankAccountTitle' => $bankAccountTitle,
            'bankAccountNumber' => $bankAccountNumber,
            'bankIban' => $bankIban,
            'bankBranch' => $bankBranch,
            'easypaisaTitle' => $easypaisaTitle,
            'easypaisaNumber' => $easypaisaNumber,
            'jazzcashTitle' => $jazzcashTitle,
            'jazzcashNumber' => $jazzcashNumber,
            'paymentInstructions' => $paymentInstructions,
        ]);
    }

    /**
     * Update platform settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'delivery_fee' => ['required', 'integer', 'min:0'],
            'commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'low_stock_threshold' => ['required', 'integer', 'min:1'],
            'allowed_cities' => ['required', 'string'],
            'deal_discount_3_days' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'deal_discount_1_week' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'deal_discount_1_month' => ['nullable', 'numeric', 'min:0', 'max:50'],
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
        ]);

        // Parse allowed cities from comma/newline separated string
        $citiesArray = array_values(array_filter(array_map('trim', preg_split('/[,\r\n]+/', $request->input('allowed_cities')))));

        Setting::set('delivery_fee', (string) $request->input('delivery_fee'));
        Setting::set('commission_percent', (string) $request->input('commission_percent'));
        Setting::set('low_stock_threshold', (string) $request->input('low_stock_threshold'));
        Setting::set('allowed_cities', json_encode($citiesArray));
        if ($request->has('deal_discount_3_days')) {
            Setting::set('deal_discount_3_days', (string) $request->input('deal_discount_3_days', 5));
        }
        if ($request->has('deal_discount_1_week')) {
            Setting::set('deal_discount_1_week', (string) $request->input('deal_discount_1_week', 10));
        }
        if ($request->has('deal_discount_1_month')) {
            Setting::set('deal_discount_1_month', (string) $request->input('deal_discount_1_month', 15));
        }

        // Save Payment Accounts Settings
        Setting::set('bank_name', (string) $request->input('bank_name', 'Meezan Bank Ltd'));
        Setting::set('bank_account_title', (string) $request->input('bank_account_title', 'Organic Dairy Gilgit-Baltistan'));
        Setting::set('bank_account_number', (string) $request->input('bank_account_number', '01020304050607'));
        Setting::set('bank_iban', (string) $request->input('bank_iban', 'PK36MEZN0001020304050607'));
        Setting::set('bank_branch', (string) $request->input('bank_branch', 'Gilgit City Branch'));
        Setting::set('easypaisa_title', (string) $request->input('easypaisa_title', 'Organic Dairy Gilgit'));
        Setting::set('easypaisa_number', (string) $request->input('easypaisa_number', '0345-1234567'));
        Setting::set('jazzcash_title', (string) $request->input('jazzcash_title', 'Organic Dairy Gilgit'));
        Setting::set('jazzcash_number', (string) $request->input('jazzcash_number', '0300-7654321'));
        Setting::set('payment_instructions', (string) $request->input('payment_instructions', ''));

        return back()->with('success', 'Platform settings and bank payment details have been successfully updated.');
    }
}
