<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID = 'paid';

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'table_count' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /** Seçilen plan + (fiziksel QR paketi ise) masa sayısına göre toplam tutar. */
    public static function computeAmount(?Plan $plan, ?int $tableCount): float
    {
        if (! $plan) {
            return 0.0;
        }

        $amount = (float) $plan->price;

        if ($plan->setup_table_limit && $plan->extra_table_price && $tableCount) {
            $extra = max(0, $tableCount - $plan->setup_table_limit);
            $amount += $extra * (float) $plan->extra_table_price;
        }

        return round($amount, 2);
    }
}
