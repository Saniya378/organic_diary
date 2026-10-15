<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && $user->isSeller() && $user->sellerProfile && $user->sellerProfile->status === 'approved';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'string', 'max:50'],
            'stock' => ['required', 'integer', 'min:0'],
            'batch_no' => ['nullable', 'string', 'max:50'],
            'produced_on' => ['nullable', 'date'],
            'shelf_life_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'expires_at' => ['nullable', 'date'],
            'is_organic' => ['nullable', 'boolean'],
            'deals_enabled' => ['nullable', 'boolean'],

            // Images validation: max 5 images, each max 2MB (2048KB), jpg, jpeg, png, webp
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],

            // Variants validation
            'variants' => ['nullable', 'array'],
            'variants.*.label' => ['required_with:variants', 'string', 'max:100'],
            'variants.*.price' => ['required_with:variants', 'integer', 'min:1'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.max' => 'You may upload up to 5 images per product.',
            'images.*.max' => 'Each product image must be less than 2 MB in size.',
            'images.*.mimes' => 'Images must be in JPG, PNG, or WebP format.',
            'variants.*.label.required_with' => 'Please provide a label for all pack-size variants.',
        ];
    }
}
