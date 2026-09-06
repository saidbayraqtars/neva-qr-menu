<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Restaurant;
use App\Support\TemplatePresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Canlı menü (alt domain).
 *
 * ÖNBELLEK: sayfa HTML'i restaurants.menu_version ile anahtarlanır.
 *  - Panelde bir değişiklik olduğunda sürüm artar → anahtar değişir → ANINDA yeni içerik.
 *  - TTL (varsayılan 2 saat) yalnızca üst sınırdır; kimse dokunmasa bile menü
 *    en geç 2 saatte bir taze üretilir.
 *
 * Masa bilgisi (?masa=1 / #1) sayfada İSTEMCİ tarafında işlenir; bu sayede
 * masa parametresi önbelleği bölmez.
 */
class MenuController extends Controller
{
    public function show(): Response
    {
        return $this->render();
    }

    /**
     * DIKKAT: bu rota grubunun alan adinda `{tenant}` parametresi var ve rota
     * parametreleri metoda SIRAYLA gecirilir. Bu yuzden `$tenant` ilk argüman
     * olarak BILEREK bildirilir; aksi halde `$slug` degiskenine alt domain adi düşer.
     */
    public function category(string $tenant, string $slug): Response
    {
        /** @var Restaurant $tenant */
        $tenant = app('tenant');

        // Kiracinin KENDI kategorileri icinde ara — baska bir restoranin
        // kategorisi hicbir kosulda buradan acilamaz.
        $category = $tenant->categories()->active()->where('slug', $slug)->firstOrFail();

        return $this->render($category);
    }

    /**
     * Eski (masaya özel jetonlu) QR'lar için geriye dönük uyumluluk.
     * Yeni mimari: tek ana QR + `?masa=` parametresi / `#N` hash'i.
     */
    public function fromTable(string $tenant, string $token): RedirectResponse
    {
        /** @var Restaurant $tenant */
        $tenant = app('tenant');

        $table = $tenant->tables()->where('qr_token', $token)->where('is_active', true)->first();

        // Sayaç burada ARTIRILMAZ: yönlendirmenin ardından açılan menü sayfası
        // ölçüm isteğini (`/olcum?masa=`) zaten atıyor; iki kez sayılmasın.
        if ($table) {
            return redirect('/?masa='.rawurlencode($table->label));
        }

        return redirect('/');
    }

    private function render(?Category $activeCategory = null): Response
    {
        /** @var Restaurant $tenant */
        $tenant = app('tenant');

        $key = $tenant->menuCacheKey($activeCategory ? 'cat-'.$activeCategory->id : 'index');
        $ttl = (int) config('neva.cache.menu_ttl', 7200);

        $html = config('neva.cache.enabled', true)
            ? Cache::remember($key, $ttl, fn () => $this->renderHtml($tenant, $activeCategory))
            : $this->renderHtml($tenant, $activeCategory);

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age='.(int) config('neva.cache.http_max_age', 7200))
            ->setEtag(md5($key));
    }

    private function renderHtml(Restaurant $tenant, ?Category $activeCategory): string
    {
        // İlişkiler yalnız önbellek ıskalandığında yüklenir (yayın filtreleriyle).
        $tenant->load([
            'categories' => fn ($q) => $q->active(),
            'categories.products' => fn ($q) => $q->available(),
        ]);

        $categories = $activeCategory
            ? $tenant->categories->where('id', $activeCategory->id)->values()
            : $tenant->categories;

        return view('templates.show', [
            'presenter' => new TemplatePresenter($tenant),
            'restaurant' => $tenant,
            'categories' => $categories,
            'view' => 'auto',   // ekran genişliğine göre telefon/bilgisayar düzeni (istemcide seçilir)
            'embedded' => true,
            'tableLabel' => null, // masa rozeti istemcide doldurulur (önbellek bölünmesin)
            'track' => true,      // görüntülenme ölçümü yalnızca GERÇEK kiracı sayfasında
        ])->render();
    }
}
