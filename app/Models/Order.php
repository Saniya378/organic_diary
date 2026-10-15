<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_group_id',
        'order_no',
        'buyer_id',
        'seller_id',
        'subtotal',
        'delivery_fee',
        'commission',
        'total',
        'status',
        'payment_status',
        'rider_name',
        'rider_phone',
        'delivery_slot',
        'cancel_reason',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'delivery_fee' => 'integer',
        'commission' => 'integer',
        'total' => 'integer',
    ];

    public function orderGroup(): BelongsTo
    {
        return $this->belongsTo(OrderGroup::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rs ' . number_format($this->total);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rs ' . number_format($this->subtotal);
    }

    public function getFormattedDeliveryFeeAttribute(): string
    {
        return 'Rs ' . number_format($this->delivery_fee);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isCancelledBySeller(): bool
    {
        if ($this->status !== 'cancelled') {
            return false;
        }

        $reason = strtolower($this->cancel_reason ?? '');
        if (str_contains($reason, 'seller') || str_contains($reason, 'farm')) {
            return true;
        }

        $latestHistory = $this->statusHistories->firstWhere('status', 'cancelled');
        if ($latestHistory && (str_contains(strtolower($latestHistory->note), 'seller') || $latestHistory->changed_by === $this->seller_id)) {
            return true;
        }

        return false;
    }

    public function isCancelledByAdmin(): bool
    {
        if ($this->status !== 'cancelled') {
            return false;
        }

        $reason = strtolower($this->cancel_reason ?? '');
        if (str_contains($reason, 'admin')) {
            return true;
        }

        $latestHistory = $this->statusHistories->firstWhere('status', 'cancelled');
        if ($latestHistory && str_contains(strtolower($latestHistory->note), 'admin')) {
            return true;
        }

        return false;
    }

    public function isCancelledByBuyer(): bool
    {
        if ($this->status !== 'cancelled') {
            return false;
        }

        return !$this->isCancelledBySeller() && !$this->isCancelledByAdmin();
    }
}
