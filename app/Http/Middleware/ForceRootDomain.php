<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pazarlama sayfaları YALNIZCA kök alan adında yaşar.
 *
 * SORUN: Kiracı rotaları (routes/tenant.php) alt domainde önce kaydedilir ama
 * orada eşleşmeyen her yol routes/web.php'ye düşer. Sonuç: lumina.nevaqr.com
 * /fiyatlandirma pazarlama sayfasını 200 ile servis eder ve kanonik etiketi
 * kendini gösterir. Yani her pazarlama sayfası, KİRACI SAYISI KADAR kopyalanır.
 * 150 restoranla bu binlerce çoğaltılmış adres demek: tarama bütçesi orada
 * yanar, hangi kopyanın asıl olduğu belirsizleşir, sıralama dağılır.
 *
 * ÇÖZÜM: Alt domainde açılan bir pazarlama adresi 301 ile kök alan adına
 * gönderilir. 404 yerine 301 seçildi çünkü bu adreslere verilmiş bağlantılar
 * varsa değerleri asıl sayfada toplansın.
 *
 * Rotanın kendisine `->domain()` kısıtı koymak daha doğrudan görünür ama
 * yerelde çalışmaz: NEVA_ROOT_DOMAIN=localhost iken geliştirme 127.0.0.1
 * üzerinden yapılıyor ve kısıt her şeyi 404'e düşürürdü.
 */
class ForceRootDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $root = config('neva.root_domain');
        $host = $request->getHost();

        // Kök alan adının kendisi ya da onunla ilgisi olmayan bir host
        // (yerelde 127.0.0.1 gibi): dokunma.
        if (blank($root) || $host === $root || ! str_ends_with($host, '.'.$root)) {
            return $next($request);
        }

        // POST/PUT gibi isteklerde 301 gövdeyi düşürür ve sessiz veri kaybı
        // yaratır. Form alt domaine gönderilmez; geldiyse hata vardır, 404 doğrusu.
        if (! $request->isMethodSafe()) {
            abort(404);
        }

        $port = $request->getPort();
        $authority = $root.(in_array($port, [80, 443, null], true) ? '' : ':'.$port);

        return redirect()->away(
            $request->getScheme().'://'.$authority.$request->getRequestUri(),
            301
        );
    }
}
