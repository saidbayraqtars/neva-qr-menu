<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Canlı menü önbelleğini tazeler.
 *
 * Önbellek zaten menu_version ile anahtarlandığı için panelde yapılan değişiklik
 * ANINDA yansır. Bu komut, hiç ziyaretçi gelmese bile önbelleğin en geç 2 saatte
 * bir yenilenmesini ve ilk ziyaretçinin soğuk sayfa beklememesini sağlar.
 *
 * Zamanlama: routes/console.php (2 saatte bir).
 */
class WarmMenuCache extends Command
{
    protected $signature = 'neva:warm-menus';

    protected $description = 'Yayındaki menülerin önbelleğini ısıtır (en geç 2 saatte bir tazelenir)';

    public function handle(): int
    {
        $restaurants = Restaurant::live()->get();

        foreach ($restaurants as $restaurant) {
            $url = tenant_domain($restaurant);

            try {
                $response = Http::timeout(15)
                    ->withHeaders(['User-Agent' => 'NevaQR-CacheWarmer/1.0'])
                    ->get($url);

                $this->line(sprintf('  %s %s (%d)', $response->successful() ? '✓' : '✗', $restaurant->subdomain, $response->status()));
            } catch (\Throwable $e) {
                $this->error("  ✗ {$restaurant->subdomain} — {$e->getMessage()}");
            }
        }

        $this->info($restaurants->count().' menü ısıtıldı.');

        return self::SUCCESS;
    }
}
