<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'amount',
        'payment_channel',
        'transaction_id',
        'proof_image',
        'status',
        'seller_notes',
        'admin_notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
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

        return asset('storage/' . $this->proof_image);
    }
}
