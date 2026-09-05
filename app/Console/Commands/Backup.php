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
        {--tut=14 : Kaç günlük yedek saklansın (0 = hiç silme)}
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

    /** Saklama süresi dolan arşivleri siler. */
    private function prune(string $dir): void
    {
        $days = (int) $this->option('tut');

        if ($days <= 0) {
            return;
        }

        $cutoff = Carbon::now()->subDays($days)->getTimestamp();
        $removed = 0;

        foreach (glob($dir.DIRECTORY_SEPARATOR.'nevaqr-*.zip') ?: [] as $old) {
            if (filemtime($old) < $cutoff) {
                @unlink($old);
                $removed++;
            }
        }

        if ($removed > 0) {
            $this->line("  <fg=gray>{$removed} eski yedek silindi ({$days} günden eski)</>");
        }
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
