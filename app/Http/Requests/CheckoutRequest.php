<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedCitiesSetting = Setting::get('allowed_cities', '[]');
        $allowedCities = is_string($allowedCitiesSetting) ? json_decode($allowedCitiesSetting, true) : $allowedCitiesSetting;
        if (!is_array($allowedCities) || empty($allowedCities)) {
            $allowedCities = ['Gilgit', 'Skardu', 'Hunza', 'Ghizer', 'Astore', 'Nagar', 'Diamer'];
        }

        return [
            'shipping_full_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:20'],
            'shipping_city' => ['required', 'string', Rule::in($allowedCities)],
            'shipping_area' => ['required', 'string', 'max:255'],
            'shipping_address_line' => ['required', 'string', 'max:500'],
            'save_address' => ['nullable', 'boolean'],
            'payment_method' => ['required', 'string', Rule::in(['cod', 'bank_transfer', 'wallet', 'card'])],

            // Card Mock Fields (never stored raw)
            'card_name' => ['nullable', 'required_if:payment_method,card', 'string', 'max:255'],
            'card_number' => ['nullable', 'required_if:payment_method,card', 'string', 'max:25'],
            'card_expiry' => ['nullable', 'required_if:payment_method,card', 'string', 'max:10'],
            'card_cvc' => ['nullable', 'required_if:payment_method,card', 'string', 'max:5'],

            // Mobile Wallet Fields
            'wallet_provider' => ['nullable', 'required_if:payment_method,wallet', 'string', 'max:50'],
            'wallet_account_no' => ['nullable', 'required_if:payment_method,wallet', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_city.in' => 'Please select a supported Gilgit-Baltistan service city.',
            'card_number.required_if' => 'Please provide a valid card number for card payment.',
            'wallet_account_no.required_if' => 'Please enter your mobile wallet account number.',
        ];
    }
}
