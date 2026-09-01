<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['presenter', 'restaurant', 'view' => 'phone', 'embedded' => false, 'print' => false, 'tableLabel' => null, 'categories' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['presenter', 'restaurant', 'view' => 'phone', 'embedded' => false, 'print' => false, 'tableLabel' => null, 'categories' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<!DOCTYPE html>
<html lang="<?php echo e($restaurant->locale ?? 'tr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo e($restaurant->name); ?><?php echo e($restaurant->tagline ? ' — '.$restaurant->tagline : ' — Menü'); ?></title>
    <meta name="description" content="<?php echo e(\Illuminate\Support\Str::limit($restaurant->tagline ?: $restaurant->name.' dijital menüsü — güncel fiyatlar ve ürünler.', 160)); ?>">
<?php if (! ($print)): ?>
    <link rel="canonical" href="<?php echo e(tenant_domain($restaurant)); ?>">
    <meta name="robots" content="<?php echo e(config('neva.seo.index_tenants', true) ? 'index, follow, max-image-preview:large' : 'noindex, nofollow'); ?>">
    <meta property="og:type" content="restaurant.menu">
    <meta property="og:title" content="<?php echo e($restaurant->name); ?>">
    <meta property="og:description" content="<?php echo e(\Illuminate\Support\Str::limit($restaurant->tagline ?: $restaurant->name.' dijital menüsü', 160)); ?>">
    <meta property="og:url" content="<?php echo e(tenant_domain($restaurant)); ?>">
    <meta property="og:locale" content="tr_TR">
    <?php if($presenter->logoUrl()): ?>
        <meta property="og:image" content="<?php echo e($presenter->logoUrl()); ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <script type="application/ld+json"><?php echo json_encode(\App\Support\MenuSchema::forRestaurant($restaurant, $categories ?: collect()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<?php endif; ?>
    <link rel="icon" href="<?php echo e($presenter->logoUrl() ?? asset('img/nevalogo.png')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="<?php echo e($presenter->fontsHref()); ?>" rel="stylesheet">
    <?php if($print): ?>
        
        <?php
            $__m = json_decode(@file_get_contents(public_path('build/manifest.json')) ?: '{}', true);
            $__css = $__m['resources/css/app.css']['file'] ?? null;
            $__cssPath = $__css ? public_path('build/'.$__css) : null;
        ?>
        <?php if($__cssPath && is_file($__cssPath)): ?>
            <style><?php echo file_get_contents($__cssPath); ?></style>
        <?php endif; ?>
        <style>
            @page { size: A4; margin: 11mm 9mm; }
            html, body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        </style>
    <?php else: ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php endif; ?>
    <style>
        html, body { height: 100%; margin: 0; background: <?php echo e($presenter->cssVars()['--t-bg']); ?>; }
        #tpl-root { <?php echo $presenter->cssVarString(); ?> }
    </style>
</head>
<body class="<?php echo \Illuminate\Support\Arr::toCssClasses(['tpl-print' => $print]); ?>">
    <div id="tpl-root" class="tpl" <?php echo $presenter->dataAttrString($view); ?>>
        
        <div id="table-badge" class="table-badge" <?php if (! ($tableLabel ?? null)): ?> hidden <?php endif; ?>><?php echo e($tableLabel ?? ''); ?></div>

        <div class="tpl-wrap">
            <?php echo e($slot); ?>

        </div>
    </div>

    <?php if (! ($print)): ?>
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
    <?php endif; ?>

    <?php if (! ($embedded)): ?>
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
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/components/templates/shell.blade.php ENDPATH**/ ?>