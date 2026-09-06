{{--
    $showcase: pazarlama sitesindeki şablon vitrini (iframe içinde) render ediliyor.
    Bu durumda kanonik adres, kiracı OG etiketleri ve menü JSON-LD'si BASILMAZ:
    demo menü gerçek bir işletme değil; dizine girerse hem sahte bir Restaurant
    işaretlemesi yayınlamış oluruz hem de 40 kopya sayfa üretiriz.
--}}
@props(['presenter', 'restaurant', 'view' => 'phone', 'embedded' => false, 'print' => false, 'tableLabel' => null, 'categories' => null, 'track' => false, 'showcase' => false])
<!DOCTYPE html>
<html lang="{{ $restaurant->locale ?? 'tr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{ $restaurant->name }}{{ $restaurant->tagline ? ' — '.$restaurant->tagline : ' — Menü' }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($restaurant->tagline ?: $restaurant->name.' dijital menüsü — güncel fiyatlar ve ürünler.', 160) }}">
@if ($showcase)
    <meta name="robots" content="noindex, nofollow">
@elseif (! $print)
    <link rel="canonical" href="{{ tenant_domain($restaurant) }}">
    <meta name="robots" content="{{ config('neva.seo.index_tenants', true) ? 'index, follow, max-image-preview:large' : 'noindex, nofollow' }}">
    <meta property="og:type" content="restaurant.menu">
    <meta property="og:title" content="{{ $restaurant->name }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($restaurant->tagline ?: $restaurant->name.' dijital menüsü', 160) }}">
    <meta property="og:url" content="{{ tenant_domain($restaurant) }}">
    <meta property="og:locale" content="tr_TR">
    @if ($presenter->logoUrl())
        <meta property="og:image" content="{{ $presenter->logoUrl() }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <script type="application/ld+json">{!! json_encode(\App\Support\MenuSchema::forRestaurant($restaurant, $categories ?: collect()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
    <link rel="icon" href="{{ $presenter->logoUrl() ?? asset('img/nevalogo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $presenter->fontsHref() }}" rel="stylesheet">
    @if ($print)
        {{-- PDF/Yazdır: CANLI şablonun BİREBİR aynı CSS'i inline gömülür (tek kaynak). JS yok. --}}
        @php
            $__m = json_decode(@file_get_contents(public_path('build/manifest.json')) ?: '{}', true);
            $__css = $__m['resources/css/app.css']['file'] ?? null;
            $__cssPath = $__css ? public_path('build/'.$__css) : null;
        @endphp
        @if ($__cssPath && is_file($__cssPath))
            <style>{!! file_get_contents($__cssPath) !!}</style>
        @endif
        <style>
            @page { size: A4; margin: 11mm 9mm; }
            html, body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        </style>
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        html, body { height: 100%; margin: 0; background: {{ $presenter->cssVars()['--t-bg'] }}; }
        #tpl-root { {!! $presenter->cssVarString() !!} }
    </style>
</head>
<body @class(['tpl-print' => $print])>
    <div id="tpl-root" class="tpl" {!! $presenter->dataAttrString($view) !!}>
    @unless ($print)
        <script>
        // Geniş ekranda şablonun bilgisayar düzenini aç. Kök öğe zaten ayrıştırıldığı
        // için bu betik ilk boyamadan ÖNCE çalışır — telefon düzeni görünüp zıplamaz.
        (function () {
            var root = document.getElementById('tpl-root');
            if (!root || root.dataset.autoView !== '1') return;

            var mq = window.matchMedia('(min-width: 1024px)');
            var apply = function () { root.dataset.view = mq.matches ? 'desktop' : 'phone'; };

            apply();

            if (mq.addEventListener) mq.addEventListener('change', apply);
            else if (mq.addListener) mq.addListener(apply); // eski Safari

            // Yedek: bazı ortamlarda (gömülü görüntüleyiciler, geliştirici aracı cihaz
            // emülasyonu) matchMedia 'change' hiç tetiklenmiyor.
            var frame = 0;
            window.addEventListener('resize', function () {
                if (frame) return;
                frame = requestAnimationFrame(function () { frame = 0; apply(); });
            });
        })();
        </script>
    @endunless

        {{-- Dinamik masa rozeti — sağ üst köşe. `?masa=` / `#1` varsa dolar, yoksa gizli. --}}
        <div id="table-badge" class="table-badge" @unless ($tableLabel ?? null) hidden @endunless>{{ $tableLabel ?? '' }}</div>

        <div class="tpl-wrap">
            {{ $slot }}
        </div>
    </div>

    @unless ($print)
        <script>
        // URL'den masa bilgisini yakala: ?masa=1  ·  #1  ·  #masa=1  ·  #table=1
        (function () {
            var badge = document.getElementById('table-badge');

            function read() {
                var val = new URLSearchParams(location.search).get('masa');
                if (!val && location.hash) {
                    var h = decodeURIComponent(location.hash.slice(1));
                    var m = h.match(/^(?:masa|table)?[\s=/_-]*([\p{L}\p{N}][\p{L}\p{N}\s.\-]{0,23})$/iu);
                    if (m) val = m[1];
                }
                if (!val) return '';

                return String(val).replace(/[^\p{L}\p{N}\s.\-]/gu, '').trim().slice(0, 24);
            }

            // Diğer scriptler (ölçüm) de aynı ayrıştırmayı kullansın diye dışarı verilir.
            window.nevaTableLabel = read;

            function detect() {
                if (!badge) return;
                var val = read();
                if (!val) return;

                badge.textContent = /^\d+$/.test(val) ? ('Masa ' + val) : val;
                badge.hidden = false;
            }

            detect();
            window.addEventListener('hashchange', detect);
        })();
        </script>
    @endunless

    @unless ($print)
        <script>
        // Yatay kategori çubukları mobil için tasarlandı: parmakla kayıyor ama farede
        // ne scrollbar var ne de yatay tekerlek. Fare tekerleğini ve sürüklemeyi bağla.
        (function () {
            var bars = document.querySelectorAll('.rb-nav, .bgn-tabs, [data-hscroll]');

            bars.forEach(function (bar) {
                var overflows = function () { return bar.scrollWidth - bar.clientWidth > 1; };

                bar.addEventListener('wheel', function (e) {
                    if (e.ctrlKey || !overflows()) return;
                    if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return; // zaten yatay jest

                    var before = bar.scrollLeft;
                    bar.scrollLeft += e.deltaY;
                    if (bar.scrollLeft !== before) e.preventDefault(); // uçta kalırsa sayfa kaysın
                }, { passive: false });

                var dragging = false, moved = false, startX = 0, startLeft = 0;

                var stop = function (e) {
                    if (!dragging) return;
                    dragging = false;
                    bar.style.cursor = '';
                    if (e && bar.hasPointerCapture && bar.hasPointerCapture(e.pointerId)) {
                        bar.releasePointerCapture(e.pointerId);
                    }
                };

                bar.addEventListener('pointerdown', function (e) {
                    if (e.pointerType !== 'mouse' || e.button !== 0 || !overflows()) return;
                    dragging = true;
                    moved = false;
                    startX = e.clientX;
                    startLeft = bar.scrollLeft;
                });

                bar.addEventListener('pointermove', function (e) {
                    if (!dragging) return;
                    var dx = e.clientX - startX;
                    if (!moved && Math.abs(dx) < 4) return; // küçük titremeyi tıklama say

                    if (!moved) {
                        moved = true;
                        bar.style.cursor = 'grabbing';
                        try { bar.setPointerCapture(e.pointerId); } catch (err) { /* yakalama zorunlu değil */ }
                    }

                    bar.scrollLeft = startLeft - dx;
                });

                bar.addEventListener('pointerup', stop);
                bar.addEventListener('pointercancel', stop);

                // Sürükleme bittiğinde altındaki kategori düğmesi tetiklenmesin.
                bar.addEventListener('click', function (e) {
                    if (!moved) return;
                    moved = false;
                    e.preventDefault();
                    e.stopPropagation();
                }, true);
            });

            // Açılışta seçili kategori görünür olsun (uzun listede sağda kalabiliyor).
            document.addEventListener('alpine:initialized', function () {
                bars.forEach(function (bar) {
                    var active = bar.querySelector('.is-active');
                    if (active) active.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                });
            });
        })();
        </script>
    @endunless

    @if ($track)
        <script>
        // Görüntülenme ölçümü. Sayfa HTML'i önbellekten geldiği için sayaç
        // sunucuda artırılamaz; yükleme bittikten sonra tek bir hafif istek atılır.
        // Kişisel veri gönderilmez: yalnızca masa etiketi + "bugün ilk kez mi" bilgisi.
        (function () {
            var key = 'neva-visit-' + new Date().toISOString().slice(0, 10);
            var fresh = false;

            try {
                // Dünden kalan işaretleri temizle, sonra bugünkünü koy.
                for (var i = localStorage.length - 1; i >= 0; i--) {
                    var k = localStorage.key(i);
                    if (k && k.indexOf('neva-visit-') === 0 && k !== key) localStorage.removeItem(k);
                }
                if (!localStorage.getItem(key)) {
                    localStorage.setItem(key, '1');
                    fresh = true;
                }
            } catch (e) { /* özel sekme / kapalı depolama: sadece görüntülenme sayılır */ }

            function ping() {
                var label = window.nevaTableLabel ? window.nevaTableLabel() : '';
                var url = '/olcum?yeni=' + (fresh ? '1' : '0') + (label ? '&masa=' + encodeURIComponent(label) : '');

                try {
                    fetch(url, { method: 'GET', keepalive: true, credentials: 'omit', cache: 'no-store' })
                        .catch(function () {});
                } catch (e) { /* ölçüm hiçbir koşulda menüyü etkilemez */ }
            }

            if (document.readyState === 'complete') ping();
            else window.addEventListener('load', ping);
        })();
        </script>
    @endif

    @unless ($embedded)
        <script>
        // Panel canlı önizlemesi: iframe içindeyken postMessage ile anlık güncelleme.
        // State (logo/kapak) iframe yeniden yüklendiğinde ana pencere tekrar gönderir.
        window.addEventListener('message', (e) => {
            const d = e.data || {};
            if (d.type !== 'neva-preview') return;
            const root = document.getElementById('tpl-root');
            if (!root) return;
            if (d.vars) for (const [k, v] of Object.entries(d.vars)) {
                root.style.setProperty(k, v);
                // Sayfa zemini (html/body) ayrı boyanıyor — özel arka plan rengini oraya da yansıt.
                if (k === '--t-bg') { document.documentElement.style.background = v; document.body.style.background = v; }
            }
            if (d.attrs) for (const [k, v] of Object.entries(d.attrs)) root.setAttribute(k, v);
            if ('logo' in d) document.querySelectorAll('[data-tpl-logo]').forEach(el => {
                if (d.logo) { el.src = d.logo; el.hidden = false; el.style.display = ''; }
                else { el.removeAttribute('src'); el.hidden = true; }
            });
            if ('cover' in d) document.querySelectorAll('[data-tpl-cover]').forEach(el => {
                if (d.cover) { el.style.backgroundImage = `url("${d.cover}")`; el.classList.remove('no-cover'); el.dataset.hasCover = '1'; }
                else { el.style.backgroundImage = ''; el.classList.add('no-cover'); el.dataset.hasCover = ''; }
            });
        });
        // hazır olduğunu bildir -> ana pencere pending state'i geri gönderir
        window.parent?.postMessage({ type: 'neva-preview-ready' }, '*');
        </script>
    @endunless
</body>
</html>
