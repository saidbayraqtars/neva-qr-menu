<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';

    public const DEFAULT_TEMPLATE = 'minimalist-kaffe';

    protected $guarded = ['id'];

    protected $casts = [
        'opening_hours' => 'array',
        'template_settings' => 'array',
        'show_prices' => 'boolean',
        'show_calories' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** owner() ile aynı; factory ->for() ve isim beklentileri için takma ad. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class)->orderBy('sort_order');
    }

    public function subdomainRequests(): HasMany
    {
        return $this->hasMany(SubdomainRequest::class);
    }

    public function isLive(): bool
    {
        return $this->status === self::STATUS_APPROVED && filled($this->subdomain);
    }

    /** Sahibin en güncel aboneliğindeki paket (yoksa null). */
    public function currentPlan(): ?Plan
    {
        return $this->owner?->subscriptions()->with('plan')->latest('id')->first()?->plan;
    }

    /**
     * "Hosting Hariç" paketi: menü müşteride barınır. Alt domain / masa QR YOK;
     * yalnızca kullanıcının girdiği dış linke özel bağımsız statik QR üretilir.
     */
    public function isSelfHosted(): bool
    {
        return $this->currentPlan()?->slug === 'hosting-haric';
    }

    /** Seçili QR tasarım tanımı (geçersizse ilk tasarıma düşer). */
    public function qrDesignConfig(): array
    {
        $designs = config('neva.qr_designs');

        return $designs[$this->qr_design] ?? reset($designs);
    }

    public function scopeLive($query)
    {
        return $query->where('status', self::STATUS_APPROVED)->whereNotNull('subdomain');
    }

    /** Geçerli şablon anahtarları. */
    public static function templateKeys(): array
    {
        return array_keys(config('neva.templates'));
    }

    /** Bu restoranın şablon tanımı (geçersizse varsayılana düşer). */
    public function templateConfig(): array
    {
        $templates = config('neva.templates');

        return $templates[$this->template] ?? $templates[self::DEFAULT_TEMPLATE];
    }

    public function logoScale(): float
    {
        return (float) (config('neva.logo_sizes.'.($this->logo_size ?: 'medium').'.scale') ?? 1.0);
    }
}
