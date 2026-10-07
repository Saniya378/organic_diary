<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'farm_name',
        'location',
        'description',
        'photo_path',
        'status',
        'rejection_reason',
    ];

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
}
