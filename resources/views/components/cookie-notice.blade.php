{{--
    Çerez bildirimi.

    Rıza toplama ekranı DEĞİL: kullanılan çerezlerin tamamı (oturum, CSRF, bu
    bildirimin kendisi) zorunlu çerezlerdir ve rıza gerektirmez. Bu kutu yalnızca
    bilgilendirir. "Kabul et / reddet" düğmeleriyle sahte bir seçim sunmak, hem
    yanıltıcı olur hem de reddedildiğinde kapatılamayacak bir siteyle sonuçlanırdı.

    QR ile açılan MENÜ sayfalarında gösterilmez — orada hiç çerez oluşturulmuyor.
--}}
@if (config('neva.legal.cookies.banner') && ! request()->cookie(\App\Http\Controllers\LegalController::COOKIE_NOTICE))
    <div class="nv-cookie" id="nv-cookie" role="region" aria-label="Çerez bildirimi">
        <p class="grow">
            Bu sitede yalnızca oturumun sürmesi ve güvenlik için <strong>zorunlu çerezler</strong>
            kullanılır; reklam veya takip çerezi yoktur. QR ile açılan menü sayfalarında ise hiç
            çerez oluşturulmaz. Ayrıntı:
            <a href="{{ route('legal.cookies') }}">Çerez Politikası</a>.
        </p>

        <div class="nv-cookie__actions">
            <button type="button" class="btn-primary px-4" data-cookie-dismiss>Anladım</button>
        </div>
    </div>

    <script>
        (function () {
            var box = document.getElementById('nv-cookie');
            if (!box) return;

            box.querySelector('[data-cookie-dismiss]').addEventListener('click', function () {
                box.remove();

                // Tercih sunucuda çereze yazılır; başarısız olsa bile kutu kapanmış olur.
                fetch(@json(route('legal.cookies.accept')), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' },
                    credentials: 'same-origin',
                }).catch(function () {});
            });
        })();
    </script>
@endif
