<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * İşletme sahibi ile platform admini arasındaki mesaj konusu.
 */
class Conversation extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'user_id', 'restaurant_id', 'subject', 'status', 'priority',
        'last_message_at', 'last_sender_role', 'unread_for_user', 'unread_for_admin',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'unread_for_user' => 'integer',
        'unread_for_admin' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('id');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest('id')->limit(1);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }
}
