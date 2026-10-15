<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\Storage;

class SellerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'farm_name',
        'location',
        'serviceable_cities',
        'serviceable_areas',
        'delivery_fee',
        'free_delivery_threshold',
        'min_order_amount',
        'description',
        'photo_path',
        'cnic_no',
        'cnic_front',
        'cnic_back',
        'status',
        'rejection_reason',
        'bank_name',
        'bank_account_title',
        'bank_account_number',
        'bank_iban',
        'bank_branch',
        'easypaisa_title',
        'easypaisa_number',
        'jazzcash_title',
        'jazzcash_number',
        'payment_instructions',
    ];

    protected $casts = [
        'serviceable_cities' => 'array',
        'delivery_fee' => 'integer',
        'free_delivery_threshold' => 'integer',
        'min_order_amount' => 'integer',
    ];

    /**
     * Check if a city is serviced by this seller.
     */
    public function isCityServiceable(?string $city): bool
    {
        if (empty($city)) {
            return true;
        }

        $cities = $this->serviceable_cities;
        if (empty($cities) || !is_array($cities)) {
            return true; // No restriction specified means all cities supported
        }

        return in_array(strtolower(trim($city)), array_map('strtolower', $cities));
    }

    /**
     * Calculate effective delivery fee for a given seller order subtotal.
     */
    public function calculateDeliveryFee(int $subtotal): int
    {
        if ($this->free_delivery_threshold && $this->free_delivery_threshold > 0 && $subtotal >= $this->free_delivery_threshold) {
            return 0; // Free delivery threshold achieved
        }

        if ($this->delivery_fee !== null) {
            return (int) $this->delivery_fee;
        }

        return (int) Setting::get('delivery_fee', 150);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('seller_profiles.status', 'approved')
            ->whereHas('user', function ($q) {
                $q->where('status', 'active');
            });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getApprovedProductsCountAttribute(): int
    {
        if (!$this->user) {
            return 0;
        }

        if ($this->user->relationLoaded('products')) {
            return $this->user->products->where('status', 'approved')->count();
        }

        return $this->user->products()->where('status', 'approved')->count();
    }

    public function getAverageRatingAttribute(): float
    {
        if (!$this->user) {
            return 5.0;
        }
        
        $avg = Review::whereHas('product', function ($q) {
            $q->where('seller_id', $this->user_id);
        })->avg('rating');

        return round((float) ($avg ?? 5.0), 1);
    }

    public function getCnicFrontUrlAttribute(): ?string
    {
        if (!$this->cnic_front) {
            return null;
        }

        return Storage::disk('public')->url($this->cnic_front);
    }

    public function getCnicBackUrlAttribute(): ?string
    {
        if (!$this->cnic_back) {
            return null;
        }

        return Storage::disk('public')->url($this->cnic_back);
    }

    public function getIsCnicFrontPdfAttribute(): bool
    {
        return $this->cnic_front && str_ends_with(strtolower($this->cnic_front), '.pdf');
    }

    public function getIsCnicBackPdfAttribute(): bool
    {
        return $this->cnic_back && str_ends_with(strtolower($this->cnic_back), '.pdf');
    }

    public function hasCnic(): bool
    {
        return !empty($this->cnic_no) || !empty($this->cnic_front) || !empty($this->cnic_back);
    }
}

