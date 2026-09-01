<?php

namespace App\Jobs;

use App\Models\Restaurant;
use App\Services\DnsProvisioner;
use App\Services\QrService;
use App\Services\SubdomainHealthChecker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Admin onayından sonra alt domaini OTOMATİK canlıya alır:
 *   1) DNS kaydını açar (wildcard ise no-op)
 *   2) Ana işletme QR kodunu üretir
 *   3) Adresi HTTP ile doğrular (200 + işletme adı sayfada geçiyor mu)
 *   4) Başarılıysa verified_at damgalar; değilse publish_status=failed + hata mesajı
 *
 * Doğrulama başarısız olursa iş yeniden denenir (backoff config'ten).
 * Menü yine de yayında kalır — kullanıcıyı cezalandırmayız, admin uyarılır.
 */
class PublishSubdomain implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $restaurantId) {}

    /** Denemeler arası bekleme (saniye) — config'ten. */
    public function backoff(): array
    {
        return (array) config('neva.publish.verify.backoff', [30, 120, 300]);
    }

    public function handle(DnsProvisioner $dns, SubdomainHealthChecker $checker, QrService $qr): void
    {
        $restaurant = Restaurant::find($this->restaurantId);

        if (! $restaurant || ! $restaurant->isLive()) {
            return; // onay geri alınmış olabilir
        }

        // 1) DNS
        $restaurant->forceFill(['publish_status' => Restaurant::PUBLISH_DNS])->save();

        try {
            $result = $dns->provision($restaurant);
            $restaurant->forceFill([
                'dns_provisioned_at' => $result['provisioned'] ? now() : null,
                'publish_error' => $result['provisioned'] ? null : $result['note'],
            ])->save();
        } catch (\Throwable $e) {
            $this->markFailed($restaurant, 'DNS: '.$e->getMessage());

            throw $e;
        }

        // 2) Ana QR (menü yayınını engellememeli)
        try {
            $qr->storeMainForRestaurant($restaurant->fresh());
        } catch (\Throwable $e) {
            Log::warning('Ana QR üretilemedi', ['restaurant' => $restaurant->id, 'error' => $e->getMessage()]);
        }

        // 3) Doğrulama
        if (! config('neva.publish.verify.enabled', true)) {
            $restaurant->forceFill(['publish_status' => Restaurant::PUBLISH_LIVE])->save();

            return;
        }

        $restaurant->forceFill(['publish_status' => Restaurant::PUBLISH_VERIFYING])->save();

        $check = $checker->check($restaurant->fresh());

        $restaurant->forceFill(['last_health_check_at' => now()])->save();

        if ($check['ok']) {
            $restaurant->forceFill([
                'publish_status' => Restaurant::PUBLISH_LIVE,
                'publish_error' => null,
                'verified_at' => $restaurant->verified_at ?? now(),
            ])->save();

            return;
        }

        $this->markFailed($restaurant, $check['reason']);

        // Son deneme değilse tekrar kuyruğa girsin.
        if ($this->attempts() < $this->tries) {
            throw new \RuntimeException('Alt domain doğrulaması başarısız: '.$check['reason']);
        }
    }

    /** Not: adi 'fail' OLAMAZ — InteractsWithQueue::fail() ile cakisir. */
    private function markFailed(Restaurant $restaurant, string $reason): void
    {
        $restaurant->forceFill([
            'publish_status' => Restaurant::PUBLISH_FAILED,
            'publish_error' => mb_substr($reason, 0, 500),
        ])->save();

        Log::error('Alt domain yayına alınamadı', [
            'restaurant' => $restaurant->id,
            'subdomain' => $restaurant->subdomain,
            'reason' => $reason,
        ]);
    }
}
