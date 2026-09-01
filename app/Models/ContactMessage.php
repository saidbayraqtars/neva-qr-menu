<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_READ = 'read';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = ['name', 'email', 'phone', 'subject', 'body', 'status', 'ip', 'handled_by', 'handled_at'];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }
}
