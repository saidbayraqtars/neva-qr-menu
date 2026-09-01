<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RestaurantTable extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_scanned_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (RestaurantTable $table) {
            $table->qr_token ??= Str::lower(Str::random(12));
        });
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function getRouteKeyName(): string
    {
        return 'qr_token';
    }
}
