<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use App\Services\SubdomainService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wildcard alt domain çözümleyici.
 *
 * {tenant} segmentini canlı bir restorana eşler ve container'a "tenant" olarak bağlar.
 *
 * Menü içeriği (kategoriler/ürünler) burada YÜKLENMEZ: sayfa HTML'i önbellekten
 * geliyorsa yalnızca bu tek indeksli sorgu çalışır. İlişkiler MenuController'da,
 * sadece önbellek ıskalandığında yüklenir.
 *
 * Restoran kaydı bilerek önbelleğe ALINMAZ — menu_version'ı taze okumak
 * zorundayız; panelde yapılan değişikliğin anında yansıması buna bağlı.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = (string) $request->route('tenant');

        $restaurant = Restaurant::query()
            ->live()
            ->where('subdomain', $subdomain)
            ->first();

        if ($restaurant === null) {
            abort($this->claimResponse($request, $subdomain));
        }

        app()->instance('tenant', $restaurant);
        view()->share('tenant', $restaurant);

        return $next($request);
    }

    /**
     * Sahipsiz alt domain — boş 404 yerine "bu adres sizin olabilir" ekranı.
     *
     * DURUM KODU 404 KALIR. Wildcard yüzünden sonsuz sayıda alt domain var;
     * bunlara 200 dönmek Google için "soft 404" demektir ve site genelindeki
     * tarama güvenini düşürür. Ziyaretçi satış sayfası görür, arama motoru
     * sayfanın var olmadığını doğru okur (görünüm `noindex` de basıyor).
     *
     * Yalnızca menü sayfası (GET, HTML bekleyen) için gösterilir; görsel,
     * ölçüm ucu ve JSON istekleri düz 404 alır.
     */
    private function claimResponse(Request $request, string $subdomain): Response
    {
        if ($request->method() !== 'GET' || $request->expectsJson()) {
            abort(404, 'Bu adrese ait yayında bir menü bulunamadı.');
        }

        // Müsaitlik ölçümü ziyaretçiye "bu ad boşta mı" bilgisini verir.
        // Sızıntı riski yok: yayındaki menüler zaten herkese açık ve dizinli.
        $status = app(SubdomainService::class)->availability($subdomain);

        return response()->view('tenant.claim', [
            'label' => $subdomain,
            'available' => $status['available'],
            'reason' => $status['status'],
        ], 404);
    }
}
