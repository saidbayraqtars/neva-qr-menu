<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Havale/EFT hesabı. Kayıt sonrası ödeme talimatında ve e-postasında listelenir.
 */
class PaymentAccount extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /** IBAN her zaman boşluksuz ve büyük harf saklanır — karşılaştırma tutarlı olsun. */
    public function setIbanAttribute(?string $value): void
    {
        $this->attributes['iban'] = self::normalizeIban((string) $value);
    }

    public static function normalizeIban(string $value): string
    {
        return Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $value) ?? '');
    }

    /** Ekranda dörderli gruplanmış hali: TR12 3456 7890 ... */
    public function getFormattedIbanAttribute(): string
    {
        return trim(chunk_split($this->iban, 4, ' '));
    }

    /**
     * IBAN geçerli mi — mod-97 sağlaması.
     *
     * Uzunluk kontrolü yetmez: tek hane yanlış girilmiş bir IBAN da 26 karakter
     * olur ve para yanlış hesaba gider ya da havale reddedilir. Bu sağlama
     * bankaların kullandığı standart (ISO 13616) kontrolüdür.
     */
    public static function ibanIsValid(string $value): bool
    {
        $iban = self::normalizeIban($value);

        // Türkiye IBAN'ı 26 karakter; başka ülke gerekirse uzunluk tablosu gerekir.
        if (! preg_match('/^TR\d{24}$/', $iban)) {
            return false;
        }

        // İlk dört karakter sona taşınır, harfler sayıya çevrilir (A=10 ... Z=35).
        $rearranged = substr($iban, 4).substr($iban, 0, 4);
        $numeric = '';

        foreach (str_split($rearranged) as $char) {
            $numeric .= ctype_alpha($char) ? (string) (ord($char) - 55) : $char;
        }

        // Sayı 128 bit'i aşıyor; parça parça mod alınır.
        $remainder = '';
        foreach (str_split($numeric, 7) as $chunk) {
            $remainder = (string) ((int) ($remainder.$chunk) % 97);
        }

        return $remainder === '1';
    }
}
