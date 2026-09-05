<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Yüklenen görsellerin tek servis noktası (`/gorsel/...`).
 *
 * NEDEN: dosyalar artık ÖZEL diskte (`storage/app/uploads`) duruyor ve web
 * sunucusu tarafından doğrudan servis edilmiyor. Böylece yayına girmemiş
 * (taslak/askıda/silinmiş) bir restoranın logosu ya da ürün fotoğrafı
 * herkese açık olmuyor — yalnızca sahibi ve admin görebiliyor.
 *
 * Rota hem ana domainde hem de kiracı alt domainlerinde kayıtlı; adres
 * host'suz üretildiği için (bkz. media_url) her iki origin'de de çözülür.
 */
class MediaController extends Controller
{
    /**
     * İzin verilen yol biçimi — dizin çıkışı (../) ve rastgele dosya okuma engellenir.
     *
     * SVG BİLEREK YOK: SVG bir belgedir, içine <script> gömülebilir. Bu uçtan
     * doğrudan açıldığında tarayıcı onu kendi origin'inde çalıştırır — panel
     * oturumunun bulunduğu ana domainde saklı XSS demektir. Laravel'in `image`
     * doğrulaması da SVG'yi kabul etmiyor (allow_svg verilmedikçe); bu satır
     * ikinci savunma hattı: ileride bir yükleme yolu gevşerse dosya yine servis edilmez.
     */
    private const PATH_PATTERN = '#^restaurants/(\d+)/(brand|products|qr)/[A-Za-z0-9._-]+\.(?:png|jpe?g|webp|gif)$#i';

    public function __invoke(Request $request): Response
    {
        $path = (string) $request->route('path');

        abort_unless(preg_match(self::PATH_PATTERN, $path, $m) === 1, 404);

        $restaurant = Restaurant::withTrashed()->find((int) $m[1]);

        abort_if($restaurant === null, 404);

        $public = $restaurant->deleted_at === null && $restaurant->isLive();

        abort_unless($public || $this->canManage($request->user(), $restaurant), 404);

        $disk = Storage::disk(config('neva.uploads.disk'));

        abort_unless($disk->exists($path), 404);

        // Dosya adları rastgele karma olduğu için içerik değişince ad da değişir:
        // yayındaki görseller uzun süre önbelleklenebilir. Sahibe özel (henüz
        // yayında olmayan) görseller ise ara sunucularda saklanmamalı.
        return $disk->response($path, null, [
            'Cache-Control' => $public
                ? 'public, max-age=31536000, immutable'
                : 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function canManage(?User $user, Restaurant $restaurant): bool
    {
        return $user !== null && ($user->isAdmin() || $user->id === $restaurant->user_id);
    }
}
