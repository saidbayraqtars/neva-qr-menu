<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Şehir sayfaları — /samsun-qr-menu, /trabzon-qr-menu ...
 *
 * NEDEN AYRI SAYFA: "qr menü" ulusal ve doymuş bir terim. "samsun qr menü"
 * arayan kişi ise hem çok daha az rekabetle karşılaşır hem de arama niyeti
 * nettir — menüsünü dijitalleştirmek isteyen, o şehirdeki bir işletme sahibi.
 * Ulusal terimde aylarca sıra beklemek yerine, kazanılabilir sorgularda
 * görünüp aynı ziyaretçiyi yakalıyoruz.
 *
 * NEDEN İÇERİK CONFIG'TE: Bu sayfaların tek gerçek riski doorway page
 * sayılmaktır — aynı metnin şehir adı değiştirilmiş kopyaları. Metin
 * `config/neva.cities` içinde şehir başına elle yazılır; kod yalnızca
 * iskeleti kurar. Yeni şehir eklemek config'e bir blok yazmaktır, ama o
 * bloğu GERÇEKTEN o şehir için doldurmak gerekir.
 */
class CityController extends Controller
{
    public function show(string $city): View
    {
        $all = (array) config('neva.cities');

        abort_unless(isset($all[$city]), 404);

        $data = $all[$city];
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('marketing.city', [
            'slug' => $city,
            'city' => $data,
            'plans' => $plans,
            // Önerilen şablonlar config'te anahtar olarak duruyor; sayfada
            // gerçek şablon verisiyle gösterilir ki vitrine iç link düşsün.
            'templates' => $this->templates($data),
            // Diğer şehirler: sayfalar birbirine bağlansın, hiçbiri izole
            // kalmasın. İzole sayfa taranma sırasında en sona düşer.
            'others' => collect($all)->except($city),
        ]);
    }

    /** Önerilen şablon anahtarlarını config'teki gerçek şablon verisine çevirir. */
    private function templates(array $city): Collection
    {
        $all = (array) config('neva.templates');

        return collect($city['templates'] ?? [])
            ->filter(fn (string $key) => isset($all[$key]))
            ->mapWithKeys(fn (string $key) => [$key => $all[$key]]);
    }
}
