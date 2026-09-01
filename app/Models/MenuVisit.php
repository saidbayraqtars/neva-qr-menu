<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Günlük menü görüntülenme sayacı (restoran + gün + masa etiketi).
 * Kişisel veri tutulmaz; yalnızca toplam sayılar.
 */
class MenuVisit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'visited_on' => 'date',
        'views' => 'integer',
        'visitors' => 'integer',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
