<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'extra_table_price' => 'decimal:2',
        'setup_table_limit' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /** Fiziksel QR basım & kurulum paketi mi? (taban masa limiti + masa başı ek ücret) */
    public function hasTablePricing(): bool
    {
        return $this->setup_table_limit !== null && $this->extra_table_price !== null;
    }
}
