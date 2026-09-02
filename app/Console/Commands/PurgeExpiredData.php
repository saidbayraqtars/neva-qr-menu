<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\MembershipRequest;
use App\Models\MenuVisit;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

/**
 * KVKK saklama süresi dolan kayıtları siler.
 *
 * "Sınırlı süreyle saklama" ilkesi (KVKK m.4/2-d) bir politika metni değil,
 * çalışan bir iştir: süresi dolan veri fiilen silinmelidir. Süreler
 * config/neva.php › legal.retention altında, gün cinsinden. Süre 0 verilirse
 * o tür hiç silinmez.
 *
 * İki kademeli davranış — IP önce MASKELENİR, kayıt sonra SİLİNİR:
 * destek talebinin içeriği bir süre daha lazım olabilir, ama ziyaretçinin
 * IP'sini o kadar uzun tutmanın meşru bir gerekçesi yok.
 *
 * Zamanlama: routes/console.php (günlük).
 */
class PurgeExpiredData extends Command
{
    protected $signature = 'neva:veri-temizle {--dry-run : Hiçbir şey silme, yalnızca raporla}';

    protected $description = 'KVKK saklama süresi dolan kayıtları siler';

    private bool $dry = false;

    /** @var array<int, array{0:string,1:string,2:int}> */
    private array $rows = [];

    public function handle(): int
    {
        $this->dry = (bool) $this->option('dry-run');
        $r = (array) config('neva.legal.retention');

        // 1) İletişim formu — IP maskeleme. Kayıt kalır, iz sürülemez olur.
        $this->anonymizeContactIps((int) ($r['contact_ip_anonymize_days'] ?? 0));

        // 2) İletişim formu kaydının tamamı
        $this->purge('İletişim formu kaydı',
            fn () => ContactMessage::query(),
            (int) ($r['contact_messages_days'] ?? 0));

        // 3) Denetim kaydı
        $this->purge('Denetim kaydı (audit log)',
            fn () => AuditLog::query(),
            (int) ($r['audit_logs_days'] ?? 0));

        // 4) Görüntülenme sayaçları — kişisel veri değil, tablo şişmesin diye
        $this->purge('Görüntülenme sayacı',
            fn () => MenuVisit::query(),
            (int) ($r['menu_visits_days'] ?? 0),
            'visited_on');

        // 5) Reddedilmiş üyelik talepleri.
        //    ONAYLANANLARA DOKUNULMAZ — onlar sözleşme ilişkisinin kaydı.
        $this->purge('Reddedilmiş üyelik talebi',
            fn () => MembershipRequest::where('status', MembershipRequest::STATUS_REJECTED),
            (int) ($r['rejected_membership_days'] ?? 0));

        $this->table(['Veri türü', 'Saklama', $this->dry ? 'Silinecek' : 'Silindi'], $this->rows);

        if ($this->dry) {
            $this->components->warn('--dry-run: hiçbir kayıt silinmedi.');
        }

        return self::SUCCESS;
    }

    private function anonymizeContactIps(int $days): void
    {
        if ($days <= 0) {
            $this->rows[] = ['İletişim formu · IP maskeleme', 'kapalı', 0];

            return;
        }

        $cutoff = now()->subDays($days);
        $count = ContactMessage::whereNotNull('ip')->where('created_at', '<', $cutoff)->count();

        if (! $this->dry && $count > 0) {
            ContactMessage::whereNotNull('ip')->where('created_at', '<', $cutoff)->update(['ip' => null]);
        }

        $this->rows[] = ['İletişim formu · IP maskeleme', $days.' gün', $count];
    }

    /**
     * @param  callable(): Builder<*>  $fresh  Her çağrıda TEMİZ sorgu döndürür.
     */
    private function purge(string $label, callable $fresh, int $days, string $column = 'created_at'): void
    {
        if ($days <= 0) {
            $this->rows[] = [$label, 'süresiz (silinmez)', 0];

            return;
        }

        $cutoff = now()->subDays($days);
        $count = $fresh()->where($column, '<', $cutoff)->count();

        if (! $this->dry && $count > 0) {
            // Parça parça sil: tek seferde yüz binlerce satır silmek kilidi uzun tutar.
            $fresh()->where($column, '<', $cutoff)
                ->chunkById(500, fn ($chunk) => $chunk->each->delete());
        }

        $this->rows[] = [$label, $days.' gün', $count];
    }
}
