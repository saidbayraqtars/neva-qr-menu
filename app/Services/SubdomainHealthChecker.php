<?php

namespace App\Services;

use App\Models\Restaurant;
use Illuminate\Support\Facades\Http;

/**
 * Yayına alınan alt domainin GERÇEKTEN çalıştığını doğrular.
 *
 * Başarı ölçütü: 200 yanıt + sayfada işletme adının geçmesi.
 * (Yalnız 200 yeterli değil — yanlış kiracıya düşen bir wildcard yanıtı da 200 döner.)
 *
 * Arıza sebebi SINIFLANDIRILIR. "Adrese ulaşılamadı" demek yetmiyor: DNS eksikse
 * yapılacak iş ile TLS sertifikası wildcard'ı kapsamıyorsa yapılacak iş bambaşka.
 * Sınıf kodu admin paneline ve `neva:onkontrol` çıktısına aynen taşınır.
 */
class SubdomainHealthChecker
{
    /** reason_code değerleri. */
    public const OK = 'ok';
    public const DNS = 'dns';
    public const TLS = 'tls';
    public const TIMEOUT = 'timeout';
    public const REFUSED = 'refused';
    public const HTTP = 'http';
    public const WRONG_TENANT = 'wrong_tenant';
    public const UNKNOWN = 'unknown';

    /** Sınıf kodu → ne yapılacağı. Kullanıcıya/admine bu metin gösterilir. */
    public const REMEDIES = [
        self::DNS => 'DNS kaydı yok. Wildcard sürücüsünde `*.'
            .'{root}` A/CNAME kaydı bir kez tanımlanmalı; kiracı başına kayıt isteniyorsa NEVA_DNS_DRIVER=cloudflare.',
        self::TLS => 'TLS sertifikası bu adresi kapsamıyor. `*.{root}` için wildcard sertifika gerekir '
            .'(Let\'s Encrypt DNS-01 ya da Cloudflare proxy). Tek seferlik iştir.',
        self::TIMEOUT => 'Sunucu zamanında yanıt vermedi. Web sunucusu ayakta mı, güvenlik duvarı 443\'ü açıyor mu?',
        self::REFUSED => 'Bağlantı reddedildi. Web sunucusu bu host için dinlemiyor olabilir.',
        self::WRONG_TENANT => 'Sayfa açıldı ama başka bir kiracının içeriği geldi. Wildcard vhost yanlış kiracıya düşüyor.',
    ];

    /**
     * @return array{ok: bool, status: int|null, reason: string, reason_code: string}
     */
    public function check(Restaurant $restaurant): array
    {
        return $this->probe(tenant_domain($restaurant), trim((string) $restaurant->name));
    }

    /**
     * Bir adresi doğrular. $needle verilirse sayfa gövdesinde aranır.
     *
     * Restoran nesnesine bağlı DEĞİL: `neva:onkontrol` bunu daha hiç kiracı yokken
     * rastgele bir etiketle çağırıp wildcard DNS + sertifika hazır mı diye bakar.
     *
     * @return array{ok: bool, status: int|null, reason: string, reason_code: string}
     */
    public function probe(string $url, ?string $needle = null): array
    {
        $timeout = (int) config('neva.publish.verify.timeout', 10);

        try {
            $response = Http::timeout($timeout)
                ->withHeaders(['User-Agent' => 'NevaQR-HealthCheck/1.0'])
                ->get($url);
        } catch (\Throwable $e) {
            $code = $this->classify($e->getMessage());

            return [
                'ok' => false,
                'status' => null,
                'reason' => $this->explain($code).' ('.$this->firstLine($e->getMessage()).')',
                'reason_code' => $code,
            ];
        }

        if (! $response->successful()) {
            return [
                'ok' => false,
                'status' => $response->status(),
                'reason' => 'HTTP '.$response->status().' döndü.',
                'reason_code' => self::HTTP,
            ];
        }

        if ($needle !== null && $needle !== '') {
            $body = $response->body();

            if (! str_contains($body, e($needle)) && ! str_contains($body, $needle)) {
                return [
                    'ok' => false,
                    'status' => $response->status(),
                    'reason' => self::REMEDIES[self::WRONG_TENANT],
                    'reason_code' => self::WRONG_TENANT,
                ];
            }
        }

        return [
            'ok' => true,
            'status' => $response->status(),
            'reason' => 'Doğrulandı.',
            'reason_code' => self::OK,
        ];
    }

    /**
     * curl/Guzzle hata metnini sınıfa oturtur.
     * Metinler curl'ün kendi sabitleri — sürüm farkı olsa da bu parçalar değişmiyor.
     */
    private function classify(string $message): string
    {
        $m = mb_strtolower($message);

        return match (true) {
            str_contains($m, 'could not resolve host'),
            str_contains($m, 'name or service not known'),
            str_contains($m, 'getaddrinfo'),
            str_contains($m, 'nodename nor servname') => self::DNS,

            str_contains($m, 'ssl certificate problem'),
            str_contains($m, 'certificate verify failed'),
            str_contains($m, 'unable to get local issuer'),
            str_contains($m, 'subjectaltname'),
            str_contains($m, 'ssl: no alternative certificate'),
            str_contains($m, 'certificate is not valid'),
            str_contains($m, 'ssl_error'),
            str_contains($m, 'wrong version number') => self::TLS,

            str_contains($m, 'timed out'),
            str_contains($m, 'timeout') => self::TIMEOUT,

            str_contains($m, 'connection refused'),
            str_contains($m, 'failed to connect') => self::REFUSED,

            default => self::UNKNOWN,
        };
    }

    private function explain(string $code): string
    {
        $text = self::REMEDIES[$code] ?? 'Adrese ulaşılamadı.';

        return str_replace('{root}', (string) config('neva.root_domain'), $text);
    }

    /** Uzun curl yığınından tek satır — hata kolonu 500 karakterle sınırlı. */
    private function firstLine(string $message): string
    {
        return mb_substr(trim(strtok($message, "\n") ?: $message), 0, 160);
    }
}
