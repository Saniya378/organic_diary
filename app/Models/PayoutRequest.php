<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'reference_no',
        'amount',
        'payment_method',
        'account_title',
        'account_number',
        'bank_name',
        'status',
        'seller_notes',
        'admin_notes',
        'transaction_reference',
        'transaction_proof',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'integer',
        'processed_at' => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rs ' . number_format($this->amount);
    }

    public function getProofUrlAttribute(): ?string
    {
        return $this->transaction_proof ? asset('storage/' . $this->transaction_proof) : null;
    }

    public static function generateReferenceNo(): string
    {
        do {
            $ref = 'PO-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        } while (self::where('reference_no', $ref)->exists());

        return $ref;
    }
}
