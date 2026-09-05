<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Sunucu ve uygulama sağlık göstergeleri — admin panelinde gösterilir.
 *
 * KABUK KOMUTU ÇALIŞTIRMAZ. `shell_exec`/`exec` bir web uygulamasında
 * gereksiz saldırı yüzeyidir; buradaki her şey PHP'nin kendi araçlarıyla
 * (`/proc` okuma, `disk_free_space`, `sys_getloadavg`) alınır. Bu yüzden
 * `open_basedir`/kısıtlı ortamlarda da güvenle çalışır, olmayan veri
 * `null` döner ve arayüz onu "—" olarak gösterir.
 *
 * Barındırma panelinin göremediği şeyler burada: başarısız kuyruk işleri,
 * son yedeğin yaşı, menü önbelleğinin durumu.
 */
class ServerHealth
{
    /** Pahalı ölçümler (dizin taraması) bu kadar saniye önbelleklenir. */
    private const CACHE_TTL = 300;

    /** @return array<string, mixed> */
    public static function all(): array
    {
        return [
            'memory' => self::memory(),
            'disk' => self::disk(),
            'load' => self::load(),
            'database' => self::database(),
            'uploads' => self::uploads(),
            'queue' => self::queue(),
            'backup' => self::backup(),
            'php' => self::php(),
        ];
    }

    /**
     * RAM — `/proc/meminfo`'dan.
     *
     * MemFree DEĞİL **MemAvailable** kullanılır: Linux boş RAM'i disk
     * önbelleğinde tutar, MemFree bu yüzden her zaman düşük görünür ve
     * yanlış alarma yol açar. MemAvailable "yeni bir uygulama başlatsam
     * ne kadar bulurum" sorusunun cevabıdır.
     */
    public static function memory(): ?array
    {
        $raw = @file_get_contents('/proc/meminfo');

        if ($raw === false) {
            return null;   // Linux değil (yerel Windows geliştirme)
        }

        $read = function (string $key) use ($raw): ?int {
            return preg_match("/^{$key}:\s+(\d+) kB/m", $raw, $m) ? (int) $m[1] * 1024 : null;
        };

        $total = $read('MemTotal');
        $available = $read('MemAvailable');

        if (! $total || $available === null) {
            return null;
        }

        return [
            'total' => $total,
            'available' => $available,
            'used' => $total - $available,
            'percent' => (int) round(($total - $available) / $total * 100),
        ];
    }

    public static function disk(): ?array
    {
        $path = base_path();
        $free = @disk_free_space($path);
        $total = @disk_total_space($path);

        if ($free === false || $total === false || ! $total) {
            return null;
        }

        return [
            'total' => (int) $total,
            'free' => (int) $free,
            'used' => (int) ($total - $free),
            'percent' => (int) round(($total - $free) / $total * 100),
        ];
    }

    /** Yük ortalaması — çekirdek sayısına bölünmüş hali anlamlı olan. */
    public static function load(): ?array
    {
        if (! function_exists('sys_getloadavg')) {
            return null;
        }

        $load = sys_getloadavg();

        if ($load === false) {
            return null;
        }

        $cores = self::cores();

        return [
            'avg' => array_map(fn ($v) => round($v, 2), $load),
            'cores' => $cores,
            // 1.0 = çekirdekler tam dolu. Sürekli 1'in üstündeyse sıra oluşuyor.
            'per_core' => $cores ? round($load[0] / $cores, 2) : null,
        ];
    }

    private static function cores(): ?int
    {
        $raw = @file_get_contents('/proc/cpuinfo');

        return $raw === false ? null : (substr_count($raw, 'processor') ?: null);
    }

    public static function database(): array
    {
        $driver = (string) config('database.default');
        $size = null;

        if ($driver === 'sqlite') {
            $file = (string) config('database.connections.sqlite.database');
            $size = is_file($file) ? (int) filesize($file) : null;
        }

        // Kiracı büyüdükçe en hızlı büyüyen tablo bu — göz önünde dursun.
        $visits = null;
        try {
            $visits = (int) DB::table('menu_visits')->count();
        } catch (\Throwable) {
            // tablo yoksa sessiz geç
        }

        return [
            'driver' => $driver,
            'size' => $size,
            'visit_rows' => $visits,
            // WAL yalnızca SQLite'ta anlamlı; kapalıysa yazmalar okuyucuları kilitler.
            'journal' => $driver === 'sqlite' ? self::sqliteJournalMode() : null,
        ];
    }

    private static function sqliteJournalMode(): ?string
    {
        try {
            return (string) DB::connection()->getPdo()->query('PRAGMA journal_mode')->fetchColumn();
        } catch (\Throwable) {
            return null;
        }
    }

    /** Yüklenen görsellerin toplam boyutu — dizin taraması, önbelleklenir. */
    public static function uploads(): ?array
    {
        return Cache::remember('health:uploads', self::CACHE_TTL, function () {
            $root = (string) config('filesystems.disks.'.config('neva.uploads.disk').'.root');

            if (! is_dir($root)) {
                return null;
            }

            $bytes = 0;
            $count = 0;

            foreach (File::allFiles($root) as $file) {
                $bytes += $file->getSize();
                $count++;
            }

            return ['bytes' => $bytes, 'files' => $count];
        });
    }

    /**
     * Kuyruk sağlığı.
     *
     * `systemctl` sorgulanmaz (kabuk yok). Bunun yerine işçinin YAPTIĞI işe
     * bakılır: bekleyen iş sayısı sürekli artıyorsa ya da eskimiş bir iş
     * varsa işçi çalışmıyor demektir — daha doğru bir sinyal.
     */
    public static function queue(): array
    {
        $pending = null;
        $oldest = null;
        $failed = null;

        try {
            $pending = (int) DB::table('jobs')->count();

            $ts = DB::table('jobs')->min('available_at');
            $oldest = $ts ? Carbon::createFromTimestamp((int) $ts) : null;
        } catch (\Throwable) {
        }

        try {
            $failed = (int) DB::table('failed_jobs')->count();
        } catch (\Throwable) {
        }

        return [
            'connection' => (string) config('queue.default'),
            'pending' => $pending,
            'oldest_pending' => $oldest,
            'failed' => $failed,
            // Bekleyen en eski iş 5 dakikadan yaşlıysa işçi muhtemelen ölü.
            'stalled' => $oldest !== null && $oldest->lt(now()->subMinutes(5)),
        ];
    }

    /** Son yedek — yaşı, dosya adı, boyutu. */
    public static function backup(): ?array
    {
        $dir = storage_path('app/backups');

        if (! is_dir($dir)) {
            return null;
        }

        $files = glob($dir.DIRECTORY_SEPARATOR.'nevaqr-*.zip') ?: [];

        if ($files === []) {
            return ['exists' => false];
        }

        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
        $latest = $files[0];
        $at = Carbon::createFromTimestamp((int) filemtime($latest));

        return [
            'exists' => true,
            'name' => basename($latest),
            'size' => (int) filesize($latest),
            'at' => $at,
            'count' => count($files),
            // Günlük alınıyor; 36 saati geçtiyse zamanlanmış iş çalışmıyor.
            'stale' => $at->lt(now()->subHours(36)),
        ];
    }

    public static function php(): array
    {
        $opcache = function_exists('opcache_get_status') ? @opcache_get_status(false) : null;

        return [
            'version' => PHP_VERSION,
            'opcache' => is_array($opcache) && ($opcache['opcache_enabled'] ?? false)
                ? [
                    'used' => (int) ($opcache['memory_usage']['used_memory'] ?? 0),
                    'free' => (int) ($opcache['memory_usage']['free_memory'] ?? 0),
                    'hit_rate' => round((float) ($opcache['opcache_statistics']['opcache_hit_rate'] ?? 0), 1),
                ]
                : null,
        ];
    }

    /** Baytı okunabilir hale getirir. */
    public static function bytes(?int $bytes): string
    {
        if ($bytes === null) {
            return '—';
        }

        foreach (['B', 'KB', 'MB', 'GB', 'TB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, $bytes < 10 && $unit !== 'B' ? 1 : 0).' '.$unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 1).' PB';
    }
}
