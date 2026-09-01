<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'dark' => false,
    'description' => null,
    'canonical' => null,
    'jsonld' => null,
    'ogType' => 'website',
]));

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

foreach (array_filter(([
    'title' => null,
    'dark' => false,
    'description' => null,
    'canonical' => null,
    'jsonld' => null,
    'ogType' => 'website',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<!DOCTYPE html>
<html lang="tr" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <script>document.documentElement.classList.add('js');</script>
    <?php if (isset($component)) { $__componentOriginal42da61123f891e63201d7be28f403427 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42da61123f891e63201d7be28f403427 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.seo','data' => ['title' => $title,'description' => $description,'canonical' => $canonical,'type' => $ogType,'jsonld' => $jsonld]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('seo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($description),'canonical' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($canonical),'type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ogType),'jsonld' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($jsonld)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42da61123f891e63201d7be28f403427)): ?>
<?php $attributes = $__attributesOriginal42da61123f891e63201d7be28f403427; ?>
<?php unset($__attributesOriginal42da61123f891e63201d7be28f403427); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42da61123f891e63201d7be28f403427)): ?>
<?php $component = $__componentOriginal42da61123f891e63201d7be28f403427; ?>
<?php unset($__componentOriginal42da61123f891e63201d7be28f403427); ?>
<?php endif; ?>
    <link rel="icon" href="<?php echo e(asset('img/nevalogo.png')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-full bg-ink-50 antialiased" x-data="{ menu: false }">
    <header class="sticky top-0 z-40 border-b border-ink-100 bg-ink-50/85 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
            <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-2.5">
                <img src="<?php echo e(asset('img/nevalogo.png')); ?>" alt="Neva-QR Menü" class="h-8 w-auto">
            </a>

            <nav class="hidden items-center gap-1 text-sm md:flex">
                <a href="<?php echo e(route('about')); ?>" class="rounded-lg px-3 py-2 font-medium text-ink-600 transition hover:text-ink-900 <?php echo e(request()->routeIs('about') ? 'text-ink-900' : ''); ?>">Hakkımızda</a>
                <a href="<?php echo e(route('pricing')); ?>" class="rounded-lg px-3 py-2 font-medium text-ink-600 transition hover:text-ink-900 <?php echo e(request()->routeIs('pricing') ? 'text-ink-900' : ''); ?>">Fiyatlar</a>
                <a href="<?php echo e(route('contact')); ?>" class="rounded-lg px-3 py-2 font-medium text-ink-600 transition hover:text-ink-900 <?php echo e(request()->routeIs('contact') ? 'text-ink-900' : ''); ?>">İletişim</a>
                <span class="mx-2 h-5 w-px bg-ink-200"></span>
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(auth()->user()->isAdmin() ? route('admin.dashboard') : route('panel.dashboard')); ?>" class="btn-primary px-4">Panele git</a>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="rounded-lg px-3 py-2 font-medium text-ink-600 transition hover:text-ink-900">Giriş</a>
                    <a href="<?php echo e(route('register')); ?>" class="btn-primary px-4">Kayıt Ol</a>
                <?php endif; ?>
            </nav>

            <button @click="menu = !menu" class="md:hidden" aria-label="Menü">
                <svg class="h-6 w-6 text-ink-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <div x-show="menu" x-collapse class="border-t border-ink-100 bg-white px-6 py-4 md:hidden" style="display:none">
            <div class="flex flex-col gap-1 text-sm">
                <a href="<?php echo e(route('about')); ?>" class="rounded-lg px-3 py-2.5 font-medium text-ink-700 hover:bg-ink-50">Hakkımızda</a>
                <a href="<?php echo e(route('pricing')); ?>" class="rounded-lg px-3 py-2.5 font-medium text-ink-700 hover:bg-ink-50">Fiyatlar</a>
                <a href="<?php echo e(route('contact')); ?>" class="rounded-lg px-3 py-2.5 font-medium text-ink-700 hover:bg-ink-50">İletişim</a>
                <a href="<?php echo e(route('register')); ?>" class="btn-primary mt-2 px-4">Kayıt Ol</a>
            </div>
        </div>
    </header>

    <?php if(session('success')): ?>
        <div class="mx-auto mt-4 max-w-6xl px-6">
            <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200"><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>

    <main class="overflow-x-clip"><?php echo e($slot); ?></main>

    <footer class="mt-24 border-t border-ink-100 bg-white">
        <div class="mx-auto max-w-6xl px-6 py-14">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <img src="<?php echo e(asset('img/nevalogo.png')); ?>" alt="Neva-QR Menü" class="h-8 w-auto">
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-ink-500">
                        Restoran ve kafeler için QR ile açılan, markaya özel tasarlanan lüks dijital menü platformu.
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Ürün</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-ink-600">
                        <li><a href="<?php echo e(route('pricing')); ?>" class="hover:text-ink-900">Fiyatlandırma</a></li>
                        <li><a href="<?php echo e(route('about')); ?>" class="hover:text-ink-900">Hakkımızda</a></li>
                        <li><a href="<?php echo e(route('register')); ?>" class="hover:text-ink-900">Kayıt Ol</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">İletişim</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-ink-600">
                        <li><a href="<?php echo e(route('contact')); ?>" class="hover:text-ink-900">Bize yazın</a></li>
                        <li><a href="mailto:<?php echo e(config('neva.brand.support_email')); ?>" class="hover:text-ink-900"><?php echo e(config('neva.brand.support_email')); ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-ink-100 pt-6 text-xs text-ink-400 sm:flex-row">
                <p>&copy; <?php echo e(date('Y')); ?> <?php echo e(config('neva.brand.name')); ?>. Tüm hakları saklıdır.</p>
                <p>Türkiye'de tasarlandı</p>
            </div>
        </div>
    </footer>

    
    <script>
        (function () {
            var nodes = document.querySelectorAll('[data-reveal]');
            if (!nodes.length) return;

            var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce || !('IntersectionObserver' in window)) {
                nodes.forEach(function (n) { n.classList.add('is-visible'); });
                return;
            }

            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    // Görünüre giren VEYA sayfa zaten üzerinden kaydırılmış elemanları aç.
                    if (entry.isIntersecting || entry.boundingClientRect.top < 0) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

            nodes.forEach(function (n) { io.observe(n); });

            // Emniyet: JS çalıştı ama gözlemci bir şekilde tetiklenmediyse
            // (çok hızlı scroll, sekme), 2.5 sn sonra kalanları göster.
            window.setTimeout(function () {
                nodes.forEach(function (n) { n.classList.add('is-visible'); });
            }, 2500);
        })();
    </script>
</body>
</html>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/components/marketing-layout.blade.php ENDPATH**/ ?>