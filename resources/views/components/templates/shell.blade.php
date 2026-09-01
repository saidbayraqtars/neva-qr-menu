@props(['presenter', 'restaurant', 'view' => 'phone', 'embedded' => false, 'print' => false, 'tableLabel' => null, 'categories' => null])
<!DOCTYPE html>
<html lang="{{ $restaurant->locale ?? 'tr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{ $restaurant->name }}{{ $restaurant->tagline ? ' — '.$restaurant->tagline : ' — Menü' }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($restaurant->tagline ?: $restaurant->name.' dijital menüsü — güncel fiyatlar ve ürünler.', 160) }}">
@unless ($print)
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
@endunless
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
            if (!badge) return;

            function detect() {
                var val = new URLSearchParams(location.search).get('masa');
                if (!val && location.hash) {
                    var h = decodeURIComponent(location.hash.slice(1));
                    var m = h.match(/^(?:masa|table)?[\s=/_-]*([\p{L}\p{N}][\p{L}\p{N}\s.\-]{0,23})$/iu);
                    if (m) val = m[1];
                }
                if (!val) return;

                val = String(val).replace(/[^\p{L}\p{N}\s.\-]/gu, '').trim().slice(0, 24);
                if (!val) { badge.hidden = true; return; }

                badge.textContent = /^\d+$/.test(val) ? ('Masa ' + val) : val;
                badge.hidden = false;
            }

            detect();
            window.addEventListener('hashchange', detect);
        })();
        </script>
    @endunless

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
