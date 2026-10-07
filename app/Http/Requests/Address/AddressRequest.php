<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'area' => ['required', 'string', 'max:150'],
            'address_line' => ['required', 'string', 'max:500'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
