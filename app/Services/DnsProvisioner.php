<?php

namespace App\Services;

use App\Models\Restaurant;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Alt domain için DNS kaydını açar.
 *
 * Sürücüler (config/neva.php › publish.dns.driver):
 *  - wildcard   : *.<kök alan adı> zaten tanımlı → yapılacak bir şey yok (varsayılan)
 *  - cloudflare : kiracı başına CNAME kaydı açılır (API token + zone id gerekir)
 *  - manual     : hiçbir şey yapılmaz, kayıt elle açılır (admin uyarılır)
 */
class DnsProvisioner
{
    public function driver(): string
    {
        return (string) config('neva.publish.dns.driver', 'wildcard');
    }

    /**
     * @return array{provisioned: bool, note: string}
     */
    public function provision(Restaurant $restaurant): array
    {
        $label = (string) $restaurant->subdomain;

        if ($label === '') {
            throw new RuntimeException('Alt domain etiketi boş; DNS kaydı açılamaz.');
        }

        return match ($this->driver()) {
            'cloudflare' => $this->cloudflare($label),
            'manual' => ['provisioned' => false, 'note' => 'DNS kaydı elle açılmalı (driver=manual).'],
            default => ['provisioned' => true, 'note' => 'Wildcard DNS kullanılıyor; ayrı kayıt gerekmiyor.'],
        };
    }

    /** Cloudflare DNS API — CNAME kaydı (varsa dokunmaz). */
    private function cloudflare(string $label): array
    {
        $cfg = (array) config('neva.publish.dns.cloudflare');
        $token = $cfg['token'] ?? null;
        $zone = $cfg['zone_id'] ?? null;
        $target = $cfg['target'] ?: config('neva.root_domain');

        if (! $token || ! $zone) {
            throw new RuntimeException('Cloudflare API bilgileri eksik (CLOUDFLARE_API_TOKEN / CLOUDFLARE_ZONE_ID).');
        }

        $name = $label.'.'.config('neva.root_domain');
        $api = "https://api.cloudflare.com/client/v4/zones/{$zone}/dns_records";

        $client = Http::withToken($token)->acceptJson()->timeout(15)->retry(2, 500);

        // Kayıt zaten var mı?
        $existing = $client->get($api, ['type' => 'CNAME', 'name' => $name]);

        if ($existing->successful() && ! empty($existing->json('result'))) {
            return ['provisioned' => true, 'note' => "CNAME zaten mevcut: {$name}"];
        }

        $created = $client->post($api, [
            'type' => 'CNAME',
            'name' => $name,
            'content' => $target,
            'ttl' => 1,
            'proxied' => (bool) ($cfg['proxied'] ?? true),
        ]);

        if (! $created->successful()) {
            $msg = $created->json('errors.0.message') ?? $created->body();

            throw new RuntimeException("Cloudflare DNS kaydı açılamadı: {$msg}");
        }

        return ['provisioned' => true, 'note' => "CNAME açıldı: {$name} → {$target}"];
    }
}
