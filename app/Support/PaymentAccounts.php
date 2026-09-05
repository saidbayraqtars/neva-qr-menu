<?php

namespace App\Support;

use App\Models\PaymentAccount;
use Illuminate\Support\Collection;

/**
 * Ödeme talimatında gösterilecek hesapların TEK KAYNAĞI.
 *
 * Kayıt sonrası ekran ve havale talimatı e-postası buradan besleniyor; ikisi
 * ayrı ayrı sorgulasaydı biri güncellenmeden kalırdı.
 *
 * Tablo boşsa `.env`'deki eski tek hesaba düşer — böylece yeni kurulumlarda
 * ve bu özellik yazılmadan önceki yapılandırmalarda ödeme akışı kırılmaz.
 */
class PaymentAccounts
{
    /** @return Collection<int, object{bank_name:string, account_name:string, iban:string, formatted_iban:string, note:?string}> */
    public static function active(): Collection
    {
        $rows = PaymentAccount::active()->get();

        if ($rows->isNotEmpty()) {
            return $rows;
        }

        return self::fromConfig();
    }

    /** Hiç hesap tanımlı değil mi? Admin panelinde uyarı basmak için. */
    public static function isEmpty(): bool
    {
        return self::active()->isEmpty();
    }

    /** Eski `.env` tek hesabı — yalnızca tablo boşken kullanılır. */
    private static function fromConfig(): Collection
    {
        $bank = (array) config('neva.payment.bank');
        $iban = PaymentAccount::normalizeIban((string) ($bank['iban'] ?? ''));

        // Örnek/boş IBAN gösterilmez: "TR00 0000..." müşteriye para
        // gönderilecek bir hesap gibi görünür, en kötü senaryo budur.
        if ($iban === '' || preg_match('/^TR0{24}$/', $iban)) {
            return collect();
        }

        return collect([(object) [
            'bank_name' => (string) ($bank['bank_name'] ?? ''),
            'account_name' => (string) ($bank['account_name'] ?? ''),
            'iban' => $iban,
            'formatted_iban' => trim(chunk_split($iban, 4, ' ')),
            'note' => null,
        ]]);
    }
}
