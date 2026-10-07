<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_group_id',
        'method',
        'payment_channel',
        'sender_name',
        'sender_account',
        'transaction_id',
        'proof_image',
        'gateway_ref',
        'last4',
        'amount',
        'status',
        'admin_notes',
        'verified_by',
        'verified_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function orderGroup(): BelongsTo
    {
        return $this->belongsTo(OrderGroup::class);
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rs ' . number_format($this->amount);
    }

    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getProofImageUrlAttribute(): ?string
    {
        if (!$this->proof_image) {
            return null;
        }

        if (str_starts_with($this->proof_image, 'http')) {
            return $this->proof_image;
        }

        return asset('storage/' . $this->proof_image);
    }
}
