<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MenuVisit;
use App\Models\Restaurant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Menü istatistikleri — günlük görüntülenme, ziyaretçi ve masa kırılımı.
 * Veri `menu_visits` tablosundan gelir; ham istek/IP saklanmadığı için
 * tüm rakamlar toplam sayaçtır.
 */
class StatsController extends Controller
{
    private const RANGE_DAYS = 30;

    public function __invoke(): View
    {
        /** @var Restaurant $restaurant */
        $restaurant = app('restaurant');

        $since = Carbon::today()->subDays(self::RANGE_DAYS - 1);

        $rows = MenuVisit::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('visited_on', '>=', $since->toDateString())
            ->selectRaw('visited_on, SUM(views) as views, SUM(visitors) as visitors')
            ->groupBy('visited_on')
            ->pluck('views', 'visited_on')
            // Sürücüye göre değer "Y-m-d" ya da "Y-m-d 00:00:00" dönebilir; güne indir.
            ->mapWithKeys(fn ($views, $day) => [substr((string) $day, 0, 10) => (int) $views]);

        $visitorsByDay = MenuVisit::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('visited_on', '>=', $since->toDateString())
            ->selectRaw('visited_on, SUM(visitors) as visitors')
            ->groupBy('visited_on')
            ->pluck('visitors', 'visited_on')
            ->mapWithKeys(fn ($visitors, $day) => [substr((string) $day, 0, 10) => (int) $visitors]);

        // Boş günler de seride yer alsın; grafik kesintisiz görünsün.
        $series = [];
        for ($day = $since->copy(); $day->lte(Carbon::today()); $day->addDay()) {
            $key = $day->toDateString();
            $series[] = [
                'date' => $day->copy(),
                'views' => (int) ($rows[$key] ?? 0),
                'visitors' => (int) ($visitorsByDay[$key] ?? 0),
            ];
        }

        $tables = MenuVisit::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('visited_on', '>=', $since->toDateString())
            ->where('table_label', '!=', '')
            ->selectRaw('table_label, SUM(views) as views')
            ->groupBy('table_label')
            ->orderByDesc(DB::raw('SUM(views)'))
            ->limit(15)
            ->get();

        return view('panel.stats.index', [
            'restaurant' => $restaurant,
            'series' => $series,
            'rangeDays' => self::RANGE_DAYS,
            'today' => (int) ($rows[Carbon::today()->toDateString()] ?? 0),
            'last7' => collect($series)->slice(-7)->sum('views'),
            'last30' => collect($series)->sum('views'),
            'visitors30' => collect($series)->sum('visitors'),
            'allTime' => (int) MenuVisit::where('restaurant_id', $restaurant->id)->sum('views'),
            'tables' => $tables,
        ]);
    }
}
