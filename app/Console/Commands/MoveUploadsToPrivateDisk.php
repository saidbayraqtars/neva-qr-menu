<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Eski yüklemeleri herkese açık diskten ÖZEL diske taşır.
 *
 * Görseller `storage/app/public` altında ve `/storage/...` ile doğrudan
 * servis ediliyordu; artık `storage/app/uploads` altında duruyor ve
 * `/gorsel/...` ucundan yetki kontrolüyle veriliyor. Bu komut, sürüm
 * yükseltmesinde bir kez çalıştırılır.
 */
class MoveUploadsToPrivateDisk extends Command
{
    protected $signature = 'neva:uploads-tasi
                            {--from=public : Kaynak disk}
                            {--keep : Kaynaktaki dosyalar silinmesin}
                            {--dry-run : Sadece raporla, dosya taşıma}';

    protected $description = 'Restoran yüklemelerini herkese açık diskten özel diske taşır.';

    public function handle(): int
    {
        $from = Storage::disk((string) $this->option('from'));
        $toName = config('neva.uploads.disk');
        $to = Storage::disk($toName);

        if ($this->option('from') === $toName) {
            $this->error('Kaynak ve hedef disk aynı: '.$toName);

            return self::FAILURE;
        }

        $files = collect($from->allFiles('restaurants'));

        if ($files->isEmpty()) {
            $this->info('Taşınacak dosya yok.');

            return self::SUCCESS;
        }

        $moved = 0;
        $skipped = 0;

        foreach ($files as $path) {
            if ($to->exists($path)) {
                $skipped++;

                continue;
            }

            if ($this->option('dry-run')) {
                $this->line('taşınacak: '.$path);
                $moved++;

                continue;
            }

            $to->put($path, $from->get($path));

            if (! $this->option('keep')) {
                $from->delete($path);
            }

            $moved++;
        }

        $this->info(sprintf(
            '%d dosya %s → %s%s; %d dosya zaten hedefte.',
            $moved,
            $this->option('from'),
            $toName,
            $this->option('dry-run') ? ' (deneme)' : '',
            $skipped
        ));

        return self::SUCCESS;
    }
}
