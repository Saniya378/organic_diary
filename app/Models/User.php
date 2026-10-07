<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get user avatar URL with graceful fallback.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return Storage::url($this->avatar);
        }

        if ($this->isSeller() && $this->sellerProfile && $this->sellerProfile->photo_path && Storage::disk('public')->exists($this->sellerProfile->photo_path)) {
            return Storage::url($this->sellerProfile->photo_path);
        }

        $name = urlencode($this->name ?: 'User');
        return "https://ui-avatars.com/api/?name={$name}&background=2F5D3A&color=ffffff&size=200&bold=true";
    }

    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function sellerProfile(): HasOne
    {
        return $this->hasOne(SellerProfile::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function sellerProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function buyerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function sellerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function orderGroups(): HasMany
    {
        return $this->hasMany(OrderGroup::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function buyerDeals(): HasMany
    {
        return $this->hasMany(Deal::class, 'buyer_id');
    }

    public function sellerDeals(): HasMany
    {
        return $this->hasMany(Deal::class, 'seller_id');
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'changed_by');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class, 'seller_id');
    }

    /**
     * Lifetime net earnings for seller (delivered orders: subtotal minus platform commission).
     */
    public function getLifetimeEarningsAttribute(): int
    {
        return (int) $this->sellerOrders()
            ->where('status', 'delivered')
            ->selectRaw('COALESCE(SUM(subtotal - commission), 0) as total_net')
            ->value('total_net');
    }

    /**
     * Total amount already paid out / transferred.
     */
    public function getCompletedPayoutsTotalAttribute(): int
    {
        return (int) $this->payoutRequests()
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Total amount in pending payout requests under review.
     */
    public function getPendingPayoutsTotalAttribute(): int
    {
        return (int) $this->payoutRequests()
            ->where('status', 'pending')
            ->sum('amount');
    }

    /**
     * Current available balance that can be requested for withdrawal.
     */
    public function getAvailablePayoutBalanceAttribute(): int
    {
        $available = $this->lifetime_earnings - $this->completed_payouts_total - $this->pending_payouts_total;
        return max(0, (int) $available);
    }
}
