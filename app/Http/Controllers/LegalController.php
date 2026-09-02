<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

/**
 * Hukuki metinler: gizlilik, KVKK aydınlatma, çerez politikası, kullanım koşulları.
 *
 * Metinler statiktir ama şirket künyesi ve saklama süreleri config'ten okunur —
 * süre değiştiğinde metin ile `neva:veri-temizle` komutunun davranışı ayrışmasın diye
 * ikisi de aynı kaynaktan (config/neva.php › legal) beslenir.
 */
class LegalController extends Controller
{
    /** Çerez bildiriminin kapatıldığını hatırlayan çerez. */
    public const COOKIE_NOTICE = 'neva_cerez_bildirimi';

    public function privacy(): View
    {
        return view('legal.gizlilik');
    }

    public function kvkk(): View
    {
        return view('legal.kvkk');
    }

    public function cookies(): View
    {
        return view('legal.cerez');
    }

    public function terms(): View
    {
        return view('legal.kosullar');
    }

    /**
     * Çerez bildirimini kapat.
     *
     * Rıza kaydı DEĞİLDİR: kullanılan çerezlerin tamamı zorunlu olduğu için
     * rıza aranmaz. Bu yalnızca "bildirimi gördüm, bir daha gösterme" tercihidir,
     * o yüzden kimlik bilgisi taşımaz ve sunucuda saklanmaz.
     */
    public function acceptCookies(Request $request): Response
    {
        $cookie = Cookie::make(
            name: self::COOKIE_NOTICE,
            value: '1',
            minutes: 60 * 24 * 365,
            secure: $request->isSecure(),
            httpOnly: false,
            sameSite: 'lax',
        );

        return response()->noContent()->withCookie($cookie);
    }
}
