<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', 'in:buyer,seller'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];

        if ($this->input('role') === 'seller') {
            $rules['farm_name'] = ['required', 'string', 'max:255'];
            $rules['location'] = ['required', 'string', 'max:255'];
            $rules['description'] = ['nullable', 'string', 'max:1000'];
            $rules['photo'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Please select whether you want to register as a Buyer or a Seller.',
            'password.min' => 'The password must be at least 8 characters and include at least one letter and one number.',
            'farm_name.required' => 'Farm or Dairy Business Name is required for sellers.',
            'location.required' => 'Farm Location (e.g., Hunza, Skardu, Gilgit) is required for sellers.',
        ];
    }
}
