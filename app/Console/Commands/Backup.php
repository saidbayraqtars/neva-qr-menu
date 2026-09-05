<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Yedekleme.
 *
 * Kaybı telafi edilemeyen üç şey yedeklenir:
 *   1) veritabanı        — kiracılar, menüler, üyelikler, mesajlar
 *   2) storage/app/uploads — logolar, kapaklar, ürün görselleri, QR PNG'leri
 *   3) .env              — APP_KEY burada; kaybolursa şifrelenmiş oturum
 *                          çerezleri ve imzalı URL'ler geçersiz olur
 *
 * `storage/framework/cache` BİLEREK yedeklenmez: menü önbelleği menu_version
 * ile kendini yeniden üretir, yedeği taşımanın anlamı yok.
 *
 * Zamanlanmış çalışır (routes/console.php) ve saklama süresi dolan eski
 * arşivleri kendisi siler.
 */
class Backup extends Command
{
    protected $signature = 'neva:yedek
        {--dizin= : Arşivin yazılacağı dizin (varsayılan: storage/app/backups)}
        {--adet=7 : En fazla kaç arşiv saklansın (0 = adet sınırı yok)}
        {--tut=30 : Kaç günden eski arşivler silinsin (0 = yaş sınırı yok)}
        {--azami=8G : Arşivlerin toplam disk bütçesi (ör. 500M, 8G · 0 = sınırsız)}
        {--gorsel-yok : Yüklenen görselleri arşive ekleme (yalnız veritabanı + .env)}';

    protected $description = 'Veritabanı, yüklenen görseller ve .env dosyasını tek arşive yedekler';

    public function handle(): int
    {
        $dir = rtrim($this->option('dizin') ?: storage_path('app/backups'), '/\\');
        File::ensureDirectoryExists($dir, 0750);

        $stamp = Carbon::now()->format('Y-m-d_His');
        $archive = $dir.DIRECTORY_SEPARATOR."nevaqr-{$stamp}.zip";

        $zip = new ZipArchive;

        if ($zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->components->error("Arşiv oluşturulamadı: {$archive}");

            return self::FAILURE;
        }

        try {
            $this->addDatabase($zip);
            $this->addEnv($zip);

            if (! $this->option('gorsel-yok')) {
                $this->addUploads($zip);
            }
        } catch (\Throwable $e) {
            $zip->close();
            @unlink($archive);
            $this->components->error('Yedekleme başarısız: '.$e->getMessage());
            report($e);

            return self::FAILURE;
        }

        $zip->close();

        // Arşiv .env içeriyor: sadece sahibi okuyabilsin.
        @chmod($archive, 0600);

        $this->components->info(sprintf(
            '%s  (%s)',
            basename($archive),
            $this->humanSize((int) filesize($archive))
        ));

        $this->prune($dir);

        return self::SUCCESS;
    }

    /* ------------------------------------------------------------------ */

    private function addDatabase(ZipArchive $zip): void
    {
        $driver = (string) config('database.default');
        $db = (array) config("database.connections.$driver");

        // Bellekteki veritabanının yedeği diye bir şey yok. Sessizce atlamak
        // yerine AÇIKÇA söylüyoruz: "yedek aldım" sanıp veri kaybetmek,
        // yedeğin hiç alınmamasından kötüdür.
        if ($driver === 'sqlite' && ($db['database'] ?? null) === ':memory:') {
            $this->line('  <fg=yellow>!</> veritabanı bellekte (:memory:) — arşive EKLENMEDİ');

            return;
        }

        match ($driver) {
            'sqlite' => $this->addSqlite($zip, $db),
            'pgsql' => $this->addDump($zip, $this->pgDumpProcess($db), 'veritabani.sql'),
            'mysql', 'mariadb' => $this->addDump($zip, $this->mysqlDumpProcess($db), 'veritabani.sql'),
            default => throw new \RuntimeException("Desteklenmeyen veritabanı sürücüsü: {$driver}"),
        };

        $this->line("  <fg=green>✓</> veritabanı ({$driver})");
    }

    /**
     * SQLite dosyası KOPYALANMAZ, `.backup` ile alınır.
     *
     * Neden: WAL modunda çalışan bir SQLite dosyasını yazma sırasında düz
     * kopyalamak bozuk yedek üretir. PDO'nun sqliteBackup'ı tutarlı bir
     * anlık görüntü verir.
     */
    private function addSqlite(ZipArchive $zip, array $db): void
    {
        $source = $db['database'] ?? '';

        if (! is_file($source)) {
            throw new \RuntimeException("SQLite dosyası bulunamadı: {$source}");
        }

        $temp = tempnam(sys_get_temp_dir(), 'neva-db-');

        $pdo = new \PDO('sqlite:'.$source);
        // VACUUM INTO tutarlı ve sıkışık bir kopya üretir (SQLite 3.27+).
        $pdo->exec("VACUUM INTO ".$pdo->quote($temp.'.sqlite'));
        unset($pdo);

        @unlink($temp);
        $snapshot = $temp.'.sqlite';

        $zip->addFile($snapshot, 'veritabani.sqlite');
        // Arşiv kapanana kadar dosya diskte durmalı; kapanışta silinsin.
        $this->cleanup[] = $snapshot;
    }

    private function addDump(ZipArchive $zip, Process $process, string $name): void
    {
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('Döküm alınamadı: '.trim($process->getErrorOutput()));
        }

        $zip->addFromString($name, $process->getOutput());
    }

    private function pgDumpProcess(array $db): Process
    {
        $process = new Process([
            'pg_dump',
            '--host='.($db['host'] ?? '127.0.0.1'),
            '--port='.($db['port'] ?? 5432),
            '--username='.($db['username'] ?? ''),
            '--no-owner',
            '--no-privileges',
            $db['database'] ?? '',
        ]);

        // Şifre komut satırına YAZILMAZ — `ps` çıktısında görünürdü.
        $process->setEnv(['PGPASSWORD' => (string) ($db['password'] ?? '')]);

        return $process;
    }

    private function mysqlDumpProcess(array $db): Process
    {
        $process = new Process([
            'mysqldump',
            '--host='.($db['host'] ?? '127.0.0.1'),
            '--port='.($db['port'] ?? 3306),
            '--user='.($db['username'] ?? ''),
            '--single-transaction',
            '--quick',
            '--no-tablespaces',
            $db['database'] ?? '',
        ]);

        $process->setEnv(['MYSQL_PWD' => (string) ($db['password'] ?? '')]);

        return $process;
    }

    private function addEnv(ZipArchive $zip): void
    {
        if (is_file(base_path('.env'))) {
            $zip->addFile(base_path('.env'), 'env.txt');
            $this->line('  <fg=green>✓</> .env');
        }
    }

    private function addUploads(ZipArchive $zip): void
    {
        $root = storage_path('app/'.trim((string) config('filesystems.disks.'.config('neva.uploads.disk').'.root'), '/'));

        // Disk kökü mutlak yol olarak tanımlıysa yukarıdaki birleştirme bozulur.
        $configured = (string) config('filesystems.disks.'.config('neva.uploads.disk').'.root');
        if ($configured !== '' && is_dir($configured)) {
            $root = $configured;
        }

        if (! is_dir($root)) {
            $this->line('  <fg=yellow>!</> yükleme dizini yok, atlandı');

            return;
        }

        $count = 0;
        $bytes = 0;

        foreach (File::allFiles($root) as $file) {
            $zip->addFile($file->getPathname(), 'gorseller/'.str_replace('\\', '/', $file->getRelativePathname()));
            $count++;
            $bytes += $file->getSize();
        }

        $this->line("  <fg=green>✓</> görseller ({$count} dosya, {$this->humanSize($bytes)})");
    }

    /**
     * Eski arşivleri temizler — üç sınır birlikte uygulanır.
     *
     * NEDEN SADECE YAŞ YETMİYOR: her arşiv yüklenen görsellerin TAMAMINI
     * içeriyor. 100 restoranda bir arşiv ~1,5 GB eder; 14 günlük saklama
     * 21 GB demek ve diski doldurur. Bu yüzden asıl koruma **adet** ve
     * **toplam boyut bütçesi**; yaş sınırı sadece üstüne binen bir tavan.
     *
     * En yeni arşiv HER ZAMAN korunur — bütçe aşılsa bile. Yedeksiz kalmak,
     * disk dolmasından kötüdür.
     */
    private function prune(string $dir): void
    {
        $files = glob($dir.DIRECTORY_SEPARATOR.'nevaqr-*.zip') ?: [];

        if (count($files) <= 1) {
            return;
        }

        // Yeniden eskiye sırala; silme her zaman sondan (en eskiden) yapılır.
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        $keepCount = (int) $this->option('adet');
        $days = (int) $this->option('tut');
        $budget = $this->parseSize((string) $this->option('azami'));

        $cutoff = $days > 0 ? Carbon::now()->subDays($days)->getTimestamp() : null;

        $removed = 0;
        $reasons = [];
        $running = 0;

        foreach ($files as $index => $file) {
            $size = (int) filesize($file);
            $running += $size;

            // İlk (en yeni) arşive dokunulmaz.
            if ($index === 0) {
                continue;
            }

            $why = match (true) {
                $keepCount > 0 && ($index + 1) > $keepCount => 'adet',
                $budget > 0 && $running > $budget => 'bütçe',
                $cutoff !== null && filemtime($file) < $cutoff => 'yaş',
                default => null,
            };

            if ($why === null) {
                continue;
            }

            @unlink($file);
            $removed++;
            $reasons[$why] = ($reasons[$why] ?? 0) + 1;
            $running -= $size;
        }

        if ($removed > 0) {
            $detail = implode(', ', array_map(fn ($k, $v) => "{$v} {$k}", array_keys($reasons), $reasons));
            $this->line("  <fg=gray>{$removed} eski arşiv silindi ({$detail})</>");
        }

        $total = array_sum(array_map(fn ($f) => is_file($f) ? filesize($f) : 0, $files));
        $kept = count(array_filter($files, 'is_file'));
        $this->line("  <fg=gray>{$kept} arşiv tutuluyor · toplam {$this->humanSize((int) $total)}</>");
    }

    /** "500M" / "8G" / "1500000" → bayt. Tanınmayan değer sınırsız sayılır. */
    private function parseSize(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '0') {
            return 0;
        }

        if (! preg_match('/^(\d+(?:[.,]\d+)?)\s*([KMGT]?)B?$/i', $value, $m)) {
            return 0;
        }

        $multiplier = match (strtoupper($m[2])) {
            'K' => 1024,
            'M' => 1024 ** 2,
            'G' => 1024 ** 3,
            'T' => 1024 ** 4,
            default => 1,
        };

        return (int) round((float) str_replace(',', '.', $m[1]) * $multiplier);
    }

    private function humanSize(int $bytes): string
    {
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 1).' '.$unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 1).' TB';
    }

    /** Arşive eklenen geçici dosyalar — komut biterken silinir. */
    private array $cleanup = [];

    public function __destruct()
    {
        foreach ($this->cleanup as $path) {
            @unlink($path);
        }
    }
}
