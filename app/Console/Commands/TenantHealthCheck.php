<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use App\Services\SubdomainHealthChecker;
use Illuminate\Console\Command;

/**
 * Yayındaki tüm alt domainleri periyodik olarak doğrular.
 * Bozulan bir adres olursa publish_status = failed olur ve admin panelinde görünür.
 *
 * Zamanlama: routes/console.php (saatte bir).
 */
class TenantHealthCheck extends Command
{
    protected $signature = 'neva:health-check {--subdomain= : Yalnızca bu alt domaini kontrol et}';

    protected $description = 'Yayındaki alt domainlerin gerçekten çalıştığını doğrular';

    public function handle(SubdomainHealthChecker $checker): int
    {
        $query = Restaurant::live();

        if ($label = $this->option('subdomain')) {
            $query->where('subdomain', $label);
        }

        $restaurants = $query->get();

        if ($restaurants->isEmpty()) {
            $this->info('Kontrol edilecek yayında menü yok.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($restaurants as $restaurant) {
            $result = $checker->check($restaurant);

            $restaurant->forceFill([
                'last_health_check_at' => now(),
                'publish_status' => $result['ok'] ? Restaurant::PUBLISH_LIVE : Restaurant::PUBLISH_FAILED,
                'publish_error' => $result['ok'] ? null : mb_substr($result['reason'], 0, 500),
                'verified_at' => $result['ok'] ? ($restaurant->verified_at ?? now()) : $restaurant->verified_at,
            ])->save();

            if ($result['ok']) {
                $this->line("  ✓ {$restaurant->subdomain}");
            } else {
                $failed++;
                $this->error("  ✗ {$restaurant->subdomain} — {$result['reason']}");
            }
        }

        $this->newLine();
        $this->info(sprintf('%d menü kontrol edildi, %d sorunlu.', $restaurants->count(), $failed));

        return self::SUCCESS;
    }
}
