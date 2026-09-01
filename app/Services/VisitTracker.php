<?php

namespace App\Services;

use App\Models\Restaurant;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Menü görüntülenmelerini toplar.
 *
 * NEDEN İSTEMCİDEN: canlı menü HTML'i menu_version anahtarlı önbellekten
 * servis ediliyor; sunucu render'ı çoğu istekte hiç çalışmıyor. Bu yüzden
 * sayaç, sayfa yüklendikten sonra atılan hafif bir istekle artırılır.
 *
 * Yazma yolu "önce UPDATE, olmadıysa INSERT" biçiminde kurgulandı:
 * eşzamanlı isteklerde satır çoğalmaz (tekil indeks korur) ve gün başına
 * tek satır büyür — tablo istek sayısıyla değil, gün sayısıyla artar.
 */
class VisitTracker
{
    /** Masa etiketi için izin verilen azami uzunluk (kolon genişliğiyle aynı). */
    public const LABEL_MAX = 24;

    public function record(Restaurant $restaurant, ?string $tableLabel = null, bool $newVisitor = false): void
    {
        $label = $this->normalizeLabel($tableLabel);
        $day = Carbon::today()->toDateString();

        $this->bumpDaily($restaurant->id, $day, $label, $newVisitor);

        if ($label !== '') {
            $this->bumpTable($restaurant, $label);
        }
    }

    /** Etiketi güvenli hale getirir: yalnızca harf/rakam/boşluk/nokta/tire. */
    public function normalizeLabel(?string $label): string
    {
        $clean = preg_replace('/[^\p{L}\p{N}\s.\-]/u', '', (string) $label) ?? '';

        return Str::limit(trim(preg_replace('/\s+/u', ' ', $clean) ?? ''), self::LABEL_MAX, '');
    }

    /**
     * Gün satırını artırır.
     *
     * Eloquent DEĞİL query builder kullanılır: `visited_on` bir DATE kolonu ve
     * model cast'i değeri "Y-m-d H:i:s" olarak yazıp WHERE eşleşmesini bozardı
     * (SQLite'ta değer birebir saklanır). Burada her zaman saf "Y-m-d" yazılır.
     */
    private function bumpDaily(int $restaurantId, string $day, string $label, bool $newVisitor): void
    {
        $keys = ['restaurant_id' => $restaurantId, 'visited_on' => $day, 'table_label' => $label];
        $visitor = $newVisitor ? 1 : 0;

        if ($this->increment($keys, $visitor) > 0) {
            return;
        }

        // Satır yoksa oluştur. Yarış durumunda ikinci istek tekil indekse
        // takılır; sessizce yutup UPDATE'i tekrarlıyoruz.
        try {
            DB::table('menu_visits')->insert($keys + [
                'views' => 1,
                'visitors' => $visitor,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException) {
            $this->increment($keys, $visitor);
        }
    }

    private function increment(array $keys, int $visitor): int
    {
        return DB::table('menu_visits')->where($keys)->update([
            'views' => DB::raw('views + 1'),
            'visitors' => DB::raw('visitors + '.$visitor),
            'updated_at' => now(),
        ]);
    }

    /** Adlandırılmış masa varsa onun tarama sayacı da artar (QR ekranında görünür). */
    private function bumpTable(Restaurant $restaurant, string $label): void
    {
        $restaurant->tables()
            ->whereRaw('LOWER(label) = ?', [Str::lower($label)])
            ->where('is_active', true)
            ->update([
                'scan_count' => DB::raw('scan_count + 1'),
                'last_scanned_at' => now(),
            ]);
    }
}
