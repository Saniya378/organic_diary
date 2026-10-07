<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'action_url',
        'icon',
        'data',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Send a notification to a specific user.
     */
    public static function sendTo(
        int|User $user,
        string $title,
        string $body,
        ?string $actionUrl = null,
        string $type = 'general',
        string $icon = 'bi-bell',
        array $data = []
    ): self {
        $userId = ($user instanceof User) ? $user->id : $user;

        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'icon' => $icon,
            'data' => $data,
        ]);
    }
}
