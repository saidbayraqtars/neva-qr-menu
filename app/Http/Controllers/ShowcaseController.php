<?php

namespace App\Http\Controllers;

use App\Support\ShowcaseMenu;
use App\Support\TemplatePresenter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Şablon vitrini — pazarlama sitesinin en büyük organik trafik yüzeyi.
 *
 * 40 şablonun her biri kendi adresinde tanıtılır (/qr-menu-sablonlari/{anahtar}).
 * Bunlar ekran görüntüsü değil: sayfadaki iframe, şablonun GERÇEK iskeletini
 * örnek bir menüyle render eder. Böylece hem ziyaretçi gerçek çıktıyı görür hem
 * de her tasarım değişikliği vitrine kendiliğinden yansır — bakımı olmayan
 * 40 sayfalık içerik.
 *
 * Önizleme HTML'i önbelleğe alınır: içerik yalnızca dağıtımla değişir, her
 * istekte 40 şablonu yeniden render etmenin anlamı yok.
 */
class ShowcaseController extends Controller
{
    /** Önizleme HTML'i önbellek süresi (saniye). İçerik yalnızca dağıtımla değişir. */
    private const PREVIEW_TTL = 86400;

    public function index(): View
    {
        return view('marketing/showcase/index', [
            'templates' => $this->ordered(),
            'groups' => $this->groups(),
        ]);
    }

    public function show(string $template): View
    {
        $all = $this->ordered();

        abort_unless(isset($all[$template]), 404);

        $keys = array_keys($all);
        $at = array_search($template, $keys, true);

        return view('marketing/showcase/show', [
            'key' => $template,
            'template' => $all[$template],
            // Önceki/sonraki: hem ziyaretçi gezinmesi hem de 40 sayfayı birbirine
            // bağlayan iç link ağı — izole sayfa kalmasın.
            'prev' => $at > 0 ? $keys[$at - 1] : null,
            'next' => $at < count($keys) - 1 ? $keys[$at + 1] : null,
            'related' => $this->related($template, $all),
            'all' => $all,
        ]);
    }

    /**
     * iframe içinde açılan ham şablon render'ı.
     *
     * `noindex`: bu adres bir ürün sayfası değil, gömülü bir görselin yerini
     * tutuyor. Dizine girerse 40 adet içerikçe aynı sayfa üretmiş oluruz.
     */
    public function preview(string $template): Response
    {
        abort_unless(array_key_exists($template, (array) config('neva.templates')), 404);

        $html = Cache::remember(
            'showcase:preview:'.$template,
            self::PREVIEW_TTL,
            function () use ($template) {
                $restaurant = ShowcaseMenu::restaurant($template);

                return view('templates.show', [
                    'presenter' => new TemplatePresenter($restaurant, ['template' => $template]),
                    'restaurant' => $restaurant,
                    'categories' => $restaurant->categories,
                    'view' => 'phone',
                    'embedded' => true,
                    'showcase' => true,
                    'tableLabel' => null,
                ])->render();
            }
        );

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function faq(): View
    {
        return view('marketing/faq', [
            'faq' => (array) config('neva.seo.faq', []),
        ]);
    }

    /** Galeri sırası: config'teki curated sıra önce, tanımsız kalan varsa arkada. */
    private function ordered(): array
    {
        $all = (array) config('neva.templates');

        return collect((array) config('neva.template_order'))
            ->merge(array_keys($all))
            ->unique()
            ->filter(fn ($k) => isset($all[$k]))
            ->mapWithKeys(fn ($k) => [$k => $all[$k]])
            ->all();
    }

    /**
     * Filtre grupları — hem kullanıcı için hem de sayfa içi başlık hiyerarşisi
     * için: her grup kendi <h2>'siyle indekslenebilir bir bölüm olur.
     */
    private function groups(): array
    {
        return [
            'all' => 'Tümü',
            'light' => 'Açık tema',
            'dark' => 'Koyu tema',
            'grid' => 'Görselli ızgara',
            'list' => 'Sade liste',
            'masonry' => 'Masonry akış',
        ];
    }

    /**
     * Benzer şablonlar: önce aynı aile + aynı tema, yetmezse aynı aile.
     * İç linkleme için — arama motoru vitrini tek tek sayfa olarak değil,
     * birbirine bağlı bir küme olarak görsün.
     */
    private function related(string $key, array $all, int $limit = 3): array
    {
        $self = $all[$key];

        $score = fn (array $t) => ($t['family'] === $self['family'] ? 2 : 0)
            + ($t['mood'] === $self['mood'] ? 1 : 0)
            + ($t['layout_type'] === $self['layout_type'] ? 1 : 0);

        return collect($all)
            ->except($key)
            ->sortByDesc($score)
            ->take($limit)
            ->all();
    }
}
