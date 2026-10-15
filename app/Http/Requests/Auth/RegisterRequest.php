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
            $rules['cnic_no'] = ['required', 'string', 'max:20', 'regex:/^[0-9]{5}-?[0-9]{7}-?[0-9]{1}$/'];
            $rules['cnic_front'] = ['required', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'];
            $rules['cnic_back'] = ['required', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'];
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
            'cnic_no.required' => 'Original CNIC number is required for seller verification.',
            'cnic_no.regex' => 'Please enter a valid 13-digit Pakistani CNIC number (e.g., 71101-1234567-1).',
            'cnic_front.required' => 'Please upload the front side of your original CNIC card.',
            'cnic_front.mimes' => 'CNIC front side must be an image (JPG, PNG, WebP) or PDF document.',
            'cnic_front.max' => 'CNIC front side document must not exceed 5MB.',
            'cnic_back.required' => 'Please upload the back side of your original CNIC card.',
            'cnic_back.mimes' => 'CNIC back side must be an image (JPG, PNG, WebP) or PDF document.',
            'cnic_back.max' => 'CNIC back side document must not exceed 5MB.',
        ];
    }
}
