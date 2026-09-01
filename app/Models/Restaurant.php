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

    /** publish_status değerleri — yayın işinin (job) ilerleyişi. */
    public const PUBLISH_QUEUED = 'queued';
    public const PUBLISH_DNS = 'dns';
    public const PUBLISH_VERIFYING = 'verifying';
    public const PUBLISH_LIVE = 'live';
    public const PUBLISH_FAILED = 'failed';

    public const DEFAULT_TEMPLATE = 'minimalist-kaffe';

    /**
     * GÜVENLİK: yayın/sahiplik kolonları kütle atamaya KAPALI.
     * Bunlar yalnızca servis katmanından forceFill ile yazılır.
     */
    protected $guarded = [
        'id',
        'user_id',
        'slug',
        'subdomain',
        'status',
        'menu_version',
        'publish_status',
        'publish_error',
        'dns_provisioned_at',
        'verified_at',
        'last_health_check_at',
        'submitted_at',
        'approved_at',
        'published_at',
        'rejection_reason',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'template_settings' => 'array',
        'show_prices' => 'boolean',
        'show_calories' => 'boolean',
        'menu_version' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'dns_provisioned_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_health_check_at' => 'datetime',
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

    /** Onaya gönderilmiş, admin kararı bekliyor. */
    public function isAwaitingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /** Sahibin geçerli aboneliğindeki paket (yoksa en son abonelik, o da yoksa null). */
    public function currentPlan(): ?Plan
    {
        return $this->owner?->currentPlan();
    }

    /**
     * "Hosting Hariç" paketi: menü müşteride barınır. Alt domain / masa QR YOK;
     * yalnızca kullanıcının girdiği dış linke özel bağımsız statik QR üretilir.
     */
    public function isSelfHosted(): bool
    {
        return ! $this->planAllows('subdomain');
    }

    /** Paket bu yeteneğe izin veriyor mu? (config/neva.php › plan_features) */
    public function planAllows(string $feature): bool
    {
        return in_array($feature, $this->planFeatures(), true);
    }

    public function planFeatures(): array
    {
        return (array) ($this->planMatrix()['features'] ?? []);
    }

    /** Limit değeri; null = sınırsız. */
    public function planLimit(string $key): ?int
    {
        $limits = (array) ($this->planMatrix()['limits'] ?? []);

        return array_key_exists($key, $limits) ? $limits[$key] : 0;
    }

    private function planMatrix(): array
    {
        $matrix = (array) config('neva.plan_features');
        $slug = $this->currentPlan()?->slug;

        return $matrix[$slug] ?? $matrix['default'];
    }

    /** Menü içeriği değişti → cache anahtarı yenilensin. */
    public function bumpMenuVersion(): void
    {
        $this->newQueryWithoutScopes()->whereKey($this->getKey())->increment('menu_version');
        $this->menu_version = (int) $this->menu_version + 1;
    }

    /** Canlı menü önbellek anahtarı. */
    public function menuCacheKey(string $suffix = 'html'): string
    {
        return sprintf('tenant:%s:v%d:%s', $this->subdomain ?: $this->slug, (int) $this->menu_version, $suffix);
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
