<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wildcard alt domain çözümleyici.
 *
 * routes/tenant.php içindeki tüm rotalar Route::domain('{tenant}.'.parse_url(config('app.url'))...)
 * grubunda tanımlıdır. Bu middleware {tenant} segmentini alır, canlı bir restorana
 * eşler ve container'a "tenant" olarak bağlar. Eşleşme yoksa 404.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('tenant');

        $restaurant = Restaurant::query()
            ->live()
            ->where('subdomain', $subdomain)
            ->with(['categories' => fn ($q) => $q->active(), 'categories.products' => fn ($q) => $q->available()])
            ->first();

        abort_if($restaurant === null, 404, 'Bu adrese ait yayında bir menü bulunamadı.');

        app()->instance('tenant', $restaurant);
        view()->share('tenant', $restaurant);

        return $next($request);
    }
}
