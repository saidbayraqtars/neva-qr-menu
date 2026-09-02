<?php

namespace App\Console\Commands;

use App\Services\SubdomainHealthChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Yayın öncesi ön kontrol.
 *
 * İki soruyu tek seferde cevaplar:
 *  1) "Onayla" dediğimde alt domain gerçekten kendiliğinden açılacak mı?
 *     (wildcard DNS + wildcard TLS sertifikası + kuyruk işçisi hazır mı)
 *  2) Bu kurulum üretime uygun mu? (debug kapalı, gerçek SMTP, güvenli çerez...)
 *
 * Neden: bu iki eksik ancak İLK müşteri onaylandığında ortaya çıkıyordu ve o an
 * müşteri bekliyor oluyordu. Kontrol hiç kiracı olmadan, rastgele bir etiketle yapılır.
 *
 * Çıkış kodu hatalı kontrol varsa 1 — dağıtım betiği buna bakıp durabilir.
 */
class Preflight extends Command
{
    protected $signature = 'neva:onkontrol
        {--alt-domain : Yalnızca alt domain yayına alma hazırlığını kontrol et}
        {--uretim : Yalnızca üretim ayarlarını kontrol et}';

    protected $description = 'Alt domain otomasyonu ve üretim ayarları hazır mı diye kontrol eder';

    /** @var array<int, array{0:string,1:string,2:string,3:string}> */
    private array $rows = [];

    private int $fails = 0;
    private int $warns = 0;

    public function handle(SubdomainHealthChecker $checker): int
    {
        $only = $this->option('alt-domain') || $this->option('uretim');

        if (! $only || $this->option('alt-domain')) {
            $this->components->info('Alt domain yayına alma hazırlığı');
            $this->subdomainSection($checker);
            $this->flush();
        }

        if (! $only || $this->option('uretim')) {
            $this->components->info('Üretim ayarları');
            $this->productionSection();
            $this->flush();
        }

        if ($this->fails > 0) {
            $this->components->error("{$this->fails} kontrol başarısız, {$this->warns} uyarı. Yukarıdaki 'Yapılacak' sütununa bakın.");

            return self::FAILURE;
        }

        if ($this->warns > 0) {
            $this->components->warn("Kritik hata yok, {$this->warns} uyarı var.");

            return self::SUCCESS;
        }

        $this->components->info('Hepsi tamam.');

        return self::SUCCESS;
    }

    // ------------------------------------------------------------------
    // Alt domain
    // ------------------------------------------------------------------

    private function subdomainSection(SubdomainHealthChecker $checker): void
    {
        $root = (string) config('neva.root_domain');
        $driver = (string) config('neva.publish.dns.driver', 'wildcard');
        $local = in_array($root, ['localhost', 'nevaqr.test'], true) || str_ends_with($root, '.test');

        $this->row('Kök alan adı', $root !== '', $root ?: '(boş)',
            'NEVA_ROOT_DOMAIN tanımlayın.');

        if ($local) {
            $this->warn_('Ortam', "Kök alan adı '{$root}' — yerel geliştirme.",
                'DNS/TLS kontrolleri atlandı. Üretimde NEVA_ROOT_DOMAIN=gerçek alan adı olmalı.');

            return;
        }

        // --- DNS sürücüsü ---
        if ($driver === 'cloudflare') {
            $token = config('neva.publish.dns.cloudflare.token');
            $zone = config('neva.publish.dns.cloudflare.zone_id');

            $this->row('DNS sürücüsü (cloudflare)', $token && $zone,
                $token && $zone ? 'token + zone id tanımlı' : 'eksik bilgi',
                'CLOUDFLARE_API_TOKEN ve CLOUDFLARE_ZONE_ID girin.');
        } elseif ($driver === 'manual') {
            $this->warn_('DNS sürücüsü', 'manual',
                'Bu sürücüde HER kiracı için DNS kaydını elle açmanız gerekir. '
                .'Elle iş istemiyorsanız NEVA_DNS_DRIVER=wildcard (veya cloudflare) yapın.');
        } else {
            $this->row('DNS sürücüsü', true, 'wildcard (kiracı başına işlem yok)', '');
        }

        // --- Wildcard DNS gerçekten çözülüyor mu? ---
        $probeLabel = 'onkontrol-'.Str::lower(Str::random(8));
        $probeHost = $probeLabel.'.'.$root;

        $resolved = $this->resolves($probeHost);

        $this->row('Wildcard DNS (*.'.$root.')', $resolved,
            $resolved ? "{$probeHost} çözülüyor" : "{$probeHost} çözülmüyor",
            "DNS'te `*.{$root}` için A (veya CNAME) kaydı bir kez tanımlanmalı. "
            .'Tanımlandıktan sonra yeni kiracı için elle işlem gerekmez.');

        // --- TLS + HTTP: rastgele etiket uygulamaya ulaşıyor mu? ---
        if ($resolved) {
            $scheme = str_starts_with((string) config('app.url'), 'https') ? 'https' : 'http';
            $result = $checker->probe("{$scheme}://{$probeHost}");

            // 404/500 SORUN DEĞİL: olmayan bir kiracı zaten bulunamaz.
            // Önemli olan DNS + TLS + vhost katmanının aşılmış olması.
            $reachable = ! in_array($result['reason_code'], [
                SubdomainHealthChecker::DNS,
                SubdomainHealthChecker::TLS,
                SubdomainHealthChecker::TIMEOUT,
                SubdomainHealthChecker::REFUSED,
                SubdomainHealthChecker::UNKNOWN,
            ], true);

            $this->row(
                $scheme === 'https' ? 'Wildcard TLS sertifikası' : 'Alt domain erişimi',
                $reachable,
                $reachable ? 'uygulamaya ulaşıyor (HTTP '.($result['status'] ?? '—').')' : $result['reason'],
                $scheme === 'https'
                    ? "`*.{$root}` kapsayan wildcard sertifika kurun. Let's Encrypt DNS-01 tek seferlik: "
                      ."certbot certonly --dns-<saglayici> -d '{$root}' -d '*.{$root}'  "
                      .'(Cloudflare proxy açıksa Universal SSL bunu zaten karşılar.)'
                    : 'Web sunucusunda wildcard vhost tanımlı olmalı.'
            );
        }

        // --- Kuyruk ---
        $queue = (string) config('queue.default');

        $this->row('Kuyruk sürücüsü', $queue !== 'sync', $queue,
            'QUEUE_CONNECTION=database yapın ve `php artisan queue:work` servisini çalıştırın. '
            .'sync kalırsa yayına alma + doğrulama admin isteğini bekletir, tarayıcı zaman aşımına düşer.');

        // --- Doğrulama açık mı ---
        $verify = (bool) config('neva.publish.verify.enabled', true);

        if (! $verify) {
            $this->warn_('Otomatik doğrulama', 'kapalı',
                'NEVA_PUBLISH_VERIFY=true önerilir — aksi halde açılmayan adres "yayında" görünür.');
        } else {
            $this->row('Otomatik doğrulama', true, 'açık', '');
        }
    }

    /** Host A/AAAA/CNAME kayıtlarından herhangi biriyle çözülüyor mu? */
    private function resolves(string $host): bool
    {
        $records = @dns_get_record($host, DNS_A | DNS_AAAA | DNS_CNAME);

        if (is_array($records) && $records !== []) {
            return true;
        }

        // Bazı sistem çözücülerinde dns_get_record boş döner; işletim sistemine sor.
        $ip = @gethostbyname($host);

        return $ip !== '' && $ip !== $host;
    }

    // ------------------------------------------------------------------
    // Üretim
    // ------------------------------------------------------------------

    private function productionSection(): void
    {
        $env = (string) config('app.env');
        $url = (string) config('app.url');
        $https = str_starts_with($url, 'https');

        $this->row('APP_KEY', filled(config('app.key')), filled(config('app.key')) ? 'tanımlı' : 'boş',
            'php artisan key:generate');

        $this->row('APP_DEBUG', ! config('app.debug'), config('app.debug') ? 'açık' : 'kapalı',
            'APP_DEBUG=false — açık kalırsa hata sayfaları .env değerlerini gösterir.');

        if ($env !== 'production') {
            $this->warn_('APP_ENV', $env, 'Üretimde APP_ENV=production olmalı.');
        } else {
            $this->row('APP_ENV', true, $env, '');
        }

        $this->row('APP_URL', $https, $url, 'HTTPS adresi girin — QR kodları ve e-posta linkleri buradan üretilir.');

        $mailer = (string) config('mail.default');
        $this->row('E-posta sürücüsü', ! in_array($mailer, ['log', 'array'], true), $mailer,
            'Gerçek SMTP girin. Hesap açılış linki bu kanaldan gider; log kalırsa kullanıcı giriş yapamaz.');

        $queue = (string) config('queue.default');
        $this->row('Kuyruk sürücüsü', $queue !== 'sync', $queue,
            'QUEUE_CONNECTION=database + `php artisan queue:work` servisi.');

        if ($https) {
            $this->row('Güvenli çerez', (bool) config('session.secure'),
                config('session.secure') ? 'açık' : 'kapalı',
                'SESSION_SECURE_COOKIE=true — HTTPS\'te çerez düz bağlantıya sızmasın.');
        }

        // Oturum çerezi alt domainlere yayılmamalı: kiracı sayfası kullanıcı içeriği render eder.
        $sessionDomain = config('session.domain');
        $this->row('Oturum çerezi kapsamı', blank($sessionDomain) || ! str_starts_with((string) $sessionDomain, '.'),
            $sessionDomain ?: '(host\'a bağlı)',
            'SESSION_DOMAIN ".'.config('neva.root_domain').'" OLMAMALI — kiracıdaki bir XSS panel oturumunu çalar.');

        $db = (string) config('database.default');
        if ($db === 'sqlite') {
            $this->warn_('Veritabanı', 'sqlite',
                'Tek yazar kilidi var; eşzamanlı kullanıcıda darboğaz olur. Üretimde DB_CONNECTION=pgsql önerilir.');
        } else {
            $this->row('Veritabanı', true, $db, '');
        }

        $cache = (string) config('cache.default');
        if (in_array($cache, ['array', 'null'], true)) {
            $this->row('Önbellek sürücüsü', false, $cache, 'CACHE_STORE=file (ya da redis) — menü önbelleği buna dayanıyor.');
        } else {
            $this->row('Önbellek sürücüsü', true, $cache, '');
        }

        // Derlenmiş varlıklar
        $manifest = public_path('build/manifest.json');
        $this->row('Derlenmiş varlıklar', is_file($manifest), is_file($manifest) ? 'build/manifest.json var' : 'yok',
            'npm run build');

        // Yazılabilir dizinler
        $writable = is_writable(storage_path('framework')) && is_writable(storage_path('logs'));
        $this->row('storage/ yazılabilir', $writable, $writable ? 'evet' : 'hayır',
            'storage/ ve bootstrap/cache dizinlerine web sunucusu yazabilmeli.');
    }

    // ------------------------------------------------------------------
    // Çıktı
    // ------------------------------------------------------------------

    private function row(string $label, bool $ok, string $value, string $remedy): void
    {
        if (! $ok) {
            $this->fails++;
        }

        $this->rows[] = [$ok ? '<fg=green>✓</>' : '<fg=red>✗</>', $label, $value, $ok ? '' : $remedy];
    }

    private function warn_(string $label, string $value, string $remedy): void
    {
        $this->warns++;
        $this->rows[] = ['<fg=yellow>!</>', $label, $value, $remedy];
    }

    private function flush(): void
    {
        if ($this->rows !== []) {
            $this->table(['', 'Kontrol', 'Durum', 'Yapılacak'], $this->rows);
            $this->rows = [];
        }
    }
}
