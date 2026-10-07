<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'otp',
        'type',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Generate a new 6-digit OTP for email and type.
     */
    public static function generateFor(string $email, string $type, int $expireMinutes = 10): string
    {
        // Delete previous unused OTPs for this email and type
        static::where('email', $email)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->delete();

        $otp = sprintf('%06d', random_int(100000, 999999));

        static::create([
            'email' => strtolower(trim($email)),
            'otp' => $otp,
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes($expireMinutes),
        ]);

        return $otp;
    }

    /**
     * Check and verify an OTP.
     */
    public static function verifyOtp(string $email, string $otp, string $type): bool
    {
        $record = static::where('email', strtolower(trim($email)))
            ->where('type', $type)
            ->where('otp', trim($otp))
            ->whereNull('verified_at')
            ->where('expires_at', '>=', Carbon::now())
            ->latest()
            ->first();

        if (!$record) {
            return false;
        }

        $record->update(['verified_at' => Carbon::now()]);
        return true;
    }

    /**
     * Check if a verified OTP exists within the last X minutes (useful for multi-step flows).
     */
    public static function isRecentlyVerified(string $email, string $type, int $withinMinutes = 15): bool
    {
        return static::where('email', strtolower(trim($email)))
            ->where('type', $type)
            ->whereNotNull('verified_at')
            ->where('verified_at', '>=', Carbon::now()->subMinutes($withinMinutes))
            ->exists();
    }
}
