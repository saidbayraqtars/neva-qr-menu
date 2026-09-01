<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
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

        abort_if($restaurant === null, 404, 'Bu adrese ait yayında bir menü bulunamadı.');

        app()->instance('tenant', $restaurant);
        view()->share('tenant', $restaurant);

        return $next($request);
    }
}
