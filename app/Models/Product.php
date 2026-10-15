<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'unit',
        'stock',
        'batch_no',
        'produced_on',
        'shelf_life_days',
        'expires_at',
        'expiry_notified_at',
        'is_organic',
        'deals_enabled',
        'status',
        'rejection_reason',
        'commission_rate',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'shelf_life_days' => 'integer',
        'is_organic' => 'boolean',
        'deals_enabled' => 'boolean',
        'produced_on' => 'date',
        'expires_at' => 'date',
        'expiry_notified_at' => 'datetime',
        'commission_rate' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if ($product->produced_on && $product->shelf_life_days) {
                if (empty($product->expires_at) || $product->isDirty('produced_on') || $product->isDirty('shelf_life_days')) {
                    $product->expires_at = \Carbon\Carbon::parse($product->produced_on)->addDays((int) $product->shelf_life_days);
                }
            }
        });
    }

    /**
     * Check if product batch is already expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast() && !$this->expires_at->isToday();
    }

    /**
     * Check if product batch expires today or within 2 days.
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        if (!$this->expires_at || $this->is_expired) {
            return false;
        }

        $days = (int) now()->startOfDay()->diffInDays($this->expires_at->startOfDay(), false);
        return $days <= 2 && $days >= 0;
    }

    /**
     * Get remaining days until batch expiration.
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->expires_at->startOfDay(), false);
    }

    /**
     * Get formatted freshness status badge for UI.
     */
    public function getFreshnessStatusAttribute(): array
    {
        if (!$this->expires_at) {
            return [
                'status' => 'standard',
                'label' => 'Standard Shelf-Life',
                'badge_class' => 'bg-light text-muted border',
                'icon' => 'bi-calendar3',
            ];
        }

        $days = $this->days_until_expiry;

        if ($days < 0) {
            return [
                'status' => 'expired',
                'label' => 'Batch Expired',
                'badge_class' => 'bg-danger text-white',
                'icon' => 'bi-exclamation-triangle-fill',
            ];
        }

        if ($days === 0) {
            return [
                'status' => 'expiring_today',
                'label' => 'Expires Today',
                'badge_class' => 'bg-warning text-dark fw-bold',
                'icon' => 'bi-clock-history',
            ];
        }

        if ($days <= 2) {
            return [
                'status' => 'expiring_soon',
                'label' => "Expiring in {$days}d",
                'badge_class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-semibold',
                'icon' => 'bi-hourglass-split',
            ];
        }

        return [
            'status' => 'fresh',
            'label' => "Fresh ({$days}d left)",
            'badge_class' => 'bg-success-subtle text-success border border-success-subtle',
            'icon' => 'bi-patch-check-fill',
        ];
    }

    /**
     * Get the effective platform commission rate (%) for this product.
     * Hierarchy: Product Rate -> Category Rate -> Global Setting Default (5%).
     */
    public function getEffectiveCommissionRateAttribute(): float
    {
        if ($this->commission_rate !== null) {
            return (float) $this->commission_rate;
        }

        if ($this->category && $this->category->commission_rate !== null) {
            return (float) $this->category->commission_rate;
        }

        return (float) Setting::get('commission_percent', 5);
    }

    /**
     * Calculate admin commission for a given product line subtotal.
     */
    public function calculateCommission(int $lineTotal): int
    {
        $rate = $this->effective_commission_rate;
        return (int) round(($lineTotal * $rate) / 100);
    }

    /**
     * Scope only approved products from active & approved sellers.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('products.status', 'approved')
            ->whereHas('seller', function ($q) {
                $q->where('status', 'active')
                  ->whereHas('sellerProfile', function ($sp) {
                      $sp->where('status', 'approved');
                  });
            });
    }

    /**
     * Scope products that have recurring deals enabled by seller.
     */
    public function scopeDealsEnabled(Builder $query): Builder
    {
        return $query->where('products.deals_enabled', true);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rs ' . number_format($this->price);
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) ($this->reviews()->avg('rating') ?? 0), 1);
    }

    public function getReviewCountAttribute(): int
    {
        return (int) $this->reviews()->count();
    }

    public function getPrimaryImageAttribute(): ?string
    {
        return $this->images->first()?->path;
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        $img = $this->images->first();
        if ($img) {
            return $img->url;
        }

        if ($this->category) {
            $catSlug = $this->category->slug;
            if (file_exists(public_path('images/products/' . $catSlug . '.jpg'))) {
                return asset('images/products/' . $catSlug . '.jpg');
            }
        }

        return asset('images/products/fresh_milk.jpg');
    }

    /**
     * Check if product stock is below threshold and dispatch alert notification to seller.
     */
    public function checkAndDispatchLowStockAlert(): void
    {
        $threshold = (int) Setting::get('low_stock_threshold', 10);
        $freshStock = (int) $this->stock;

        if ($freshStock <= $threshold) {
            $sellerId = $this->seller_id;
            $title = $freshStock <= 0 ? "🚨 Sold Out: {$this->name}" : "⚠️ Low Stock Alert: {$this->name}";
            $message = $freshStock <= 0 
                ? "Your organic dairy item '{$this->name}' is now completely SOLD OUT. Update inventory or harvest fresh batches."
                : "Only {$freshStock} unit(s) remaining for '{$this->name}' (Low stock threshold: {$threshold}). Please harvest or update inventory.";

            // Prevent spamming unread duplicate notifications within 6 hours
            $alreadyNotified = Notification::where('user_id', $sellerId)
                ->where('type', 'low_stock')
                ->where('title', $title)
                ->whereNull('read_at')
                ->where('created_at', '>=', now()->subHours(6))
                ->exists();

            if (!$alreadyNotified) {
                Notification::sendTo(
                    $sellerId,
                    $title,
                    $message,
                    route('seller.products.index'),
                    'low_stock',
                    'bi-exclamation-triangle-fill'
                );
            }
        }
    }

    /**
     * Scope only non-expired products.
     */
    public function scopeNonExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>=', now()->toDateString());
        });
    }

    /**
     * Scope products expiring soon (within next 2 days).
     */
    public function scopeExpiringSoon(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '>=', now()->toDateString())
            ->where('expires_at', '<=', now()->addDays(2)->toDateString());
    }

    /**
     * Scope already expired products.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<', now()->toDateString());
    }

    /**
     * Check if product batch is near expiry or expired and dispatch notification to seller.
     */
    public function checkAndDispatchExpiryAlert(): void
    {
        if (!$this->expires_at) {
            return;
        }

        $daysLeft = $this->days_until_expiry;
        if ($daysLeft === null) {
            return;
        }

        $sellerId = $this->seller_id;
        $batchDisplay = $this->batch_no ? " (Batch: {$this->batch_no})" : "";

        if ($daysLeft < 0) {
            $title = "🚨 Batch Expired: {$this->name}";
            $message = "Your dairy batch for '{$this->name}'{$batchDisplay} reached its expiry date on {$this->expires_at->format('M d, Y')}. Please withdraw this batch from shelf.";
            $icon = 'bi-exclamation-octagon-fill';
        } elseif ($daysLeft <= 2) {
            $title = "⏳ Expiry Alert: {$this->name} ({$daysLeft}d left)";
            $message = "Batch {$batchDisplay} of '{$this->name}' will expire on {$this->expires_at->format('M d, Y')}. Consider discounting or rotating stock.";
            $icon = 'bi-hourglass-split';
        } else {
            return;
        }

        $alreadyNotified = Notification::where('user_id', $sellerId)
            ->where('title', $title)
            ->whereNull('read_at')
            ->where('created_at', '>=', now()->subHours(12))
            ->exists();

        if (!$alreadyNotified) {
            Notification::sendTo(
                $sellerId,
                $title,
                $message,
                route('seller.products.index'),
                'inventory_alert',
                $icon
            );

            $this->updateQuietly(['expiry_notified_at' => now()]);
        }
    }
}
