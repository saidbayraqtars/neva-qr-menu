<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    public const ROLE_OWNER = 'owner';
    public const ROLE_ADMIN = 'admin';

    /** Şifre belirleme linkinin geçerlilik süresi (saat). */
    public const SETUP_TOKEN_HOURS = 72;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'password_setup_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'password_setup_expires_at' => 'datetime',
        ];
    }

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function mustChangePassword(): bool
    {
        return (bool) $this->must_change_password;
    }

    /* ----------------------------------------------------------------------
     | Şifre belirleme jetonu (düz metin şifre saklamanın yerine geçer)
     |----------------------------------------------------------------------*/

    /**
     * Yeni tek kullanımlık jeton üretir; DB'ye yalnız hash'i yazılır.
     * Dönen DÜZ jeton sadece linki kurmak için kullanılır, saklanmaz.
     */
    public function issuePasswordSetupToken(): string
    {
        $plain = Str::random(48);

        $this->forceFill([
            'password_setup_token' => hash('sha256', $plain),
            'password_setup_expires_at' => now()->addHours(self::SETUP_TOKEN_HOURS),
            'must_change_password' => true,
        ])->save();

        return $plain;
    }

    public function passwordSetupTokenIsValid(string $plain): bool
    {
        return $this->password_setup_token !== null
            && $this->password_setup_expires_at !== null
            && $this->password_setup_expires_at->isFuture()
            && hash_equals($this->password_setup_token, hash('sha256', $plain));
    }

    /** Jetonla gelen kullanıcı kalıcı şifresini belirler; jeton tüketilir. */
    public function completePasswordSetup(string $newPassword): void
    {
        $this->forceFill([
            'password' => Hash::make($newPassword),
            'password_setup_token' => null,
            'password_setup_expires_at' => null,
            'must_change_password' => false,
        ])->save();
    }

    /** Aktif (geçerli) aboneliği — yoksa null. */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()->with('plan')->latest('id')->get()
            ->first(fn (Subscription $s) => $s->isValid());
    }

    public function currentPlan(): ?Plan
    {
        return $this->activeSubscription()?->plan
            ?? $this->subscriptions()->with('plan')->latest('id')->first()?->plan;
    }
}
