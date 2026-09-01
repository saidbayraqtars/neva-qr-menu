<?php

namespace App\Services;

use App\Models\Restaurant;
use Illuminate\Support\Facades\Http;

/**
 * Yayına alınan alt domainin GERÇEKTEN çalıştığını doğrular.
 *
 * Başarı ölçütü: 200 yanıt + sayfada işletme adının geçmesi.
 * (Yalnız 200 yeterli değil — yanlış kiracıya düşen bir wildcard yanıtı da 200 döner.)
 */
class SubdomainHealthChecker
{
    /**
     * @return array{ok: bool, status: int|null, reason: string}
     */
    public function check(Restaurant $restaurant): array
    {
        $url = tenant_domain($restaurant);
        $timeout = (int) config('neva.publish.verify.timeout', 10);

        try {
            $response = Http::timeout($timeout)
                ->withHeaders(['User-Agent' => 'NevaQR-HealthCheck/1.0'])
                ->get($url);
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => null, 'reason' => 'Adrese ulaşılamadı: '.$e->getMessage()];
        }

        if (! $response->successful()) {
            return ['ok' => false, 'status' => $response->status(), 'reason' => 'HTTP '.$response->status().' döndü.'];
        }

        $body = $response->body();
        $needle = trim((string) $restaurant->name);

        if ($needle !== '' && ! str_contains($body, e($needle)) && ! str_contains($body, $needle)) {
            return [
                'ok' => false,
                'status' => $response->status(),
                'reason' => 'Sayfa açıldı ama işletme adı görünmüyor — yanlış kiracıya düşmüş olabilir.',
            ];
        }

        return ['ok' => true, 'status' => $response->status(), 'reason' => 'Doğrulandı.'];
    }
}
