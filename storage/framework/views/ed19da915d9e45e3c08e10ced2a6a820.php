<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => null]));

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

foreach (array_filter((['title' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $r = $restaurant ?? null;
    $isAdminArea = request()->routeIs('admin.*');
    $nav = $isAdminArea
        ? [
            ['route' => 'admin.dashboard', 'label' => 'Genel Bakış', 'icon' => 'M4 6h16M4 12h16M4 18h10'],
            ['route' => 'admin.memberships.index', 'label' => 'Üyelik Talepleri', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM6 21v-1a6 6 0 0112 0v1M19 8v6m3-3h-6'],
            ['route' => 'admin.requests.index', 'label' => 'Alt Domain Kuyruğu', 'icon' => 'M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['route' => 'admin.messages.index', 'label' => 'Mesajlar', 'icon' => 'M8 10h8M8 14h5m8-2a9 9 0 11-3.6-7.2L21 4l-1.4 4.8A8.96 8.96 0 0121 12z', 'badge' => true],
            ['route' => 'admin.contact.index', 'label' => 'İletişim Formu', 'icon' => 'M3 8l9 6 9-6M3 8v8a2 2 0 002 2h14a2 2 0 002-2V8M3 8l2-2h14l2 2'],
            ['route' => 'admin.users.index', 'label' => 'Kullanıcılar', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z'],
        ]
        : [
            ['route' => 'panel.dashboard', 'label' => 'Genel Bakış', 'icon' => 'M4 6h16M4 12h16M4 18h10'],
            ['route' => 'panel.business.edit', 'label' => 'İşletme Profili', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM6 21v-1a6 6 0 0112 0v1'],
            ['route' => 'panel.design.edit', 'label' => 'Tasarım & Şablon', 'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4l2 3h8a2 2 0 012 2v9a4 4 0 01-4 4H7z'],
            ['route' => 'panel.categories.index', 'label' => 'Kategoriler', 'icon' => 'M4 6h16M4 10h16M4 14h10M4 18h10'],
            ['route' => 'panel.products.index', 'label' => 'Ürünler', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['route' => 'panel.qr.index', 'label' => 'QR & PDF', 'icon' => 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h2v2h-2zM18 14h2v2h-2zM14 18h2v2h-2zM18 18h2v2h-2z'],
            ['route' => 'panel.messages.index', 'label' => 'Mesajlar', 'icon' => 'M8 10h8M8 14h5m8-2a9 9 0 11-3.6-7.2L21 4l-1.4 4.8A8.96 8.96 0 0121 12z', 'badge' => true],
        ];
    $unread = (int) ($unreadMessages ?? 0);
?>

<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ? $title.' · ' : ''); ?><?php echo e(config('neva.brand.name')); ?></title>
    <link rel="icon" href="<?php echo e(asset('img/nevalogo.png')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="h-full" x-data="{ sidebar: false }">
<div class="flex min-h-full bg-ink-50">

    
    <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-ink-100 bg-white transition-transform lg:translate-x-0"
           :class="sidebar && 'translate-x-0!'">
        <div class="flex h-full flex-col overflow-hidden p-5">
            <a href="<?php echo e(url('/')); ?>" class="mb-8 flex items-center gap-2.5 px-1">
                <img src="<?php echo e(asset('img/nevalogo.png')); ?>" alt="Neva" class="h-8 w-auto">
                <?php if($isAdminArea): ?>
                    <span class="rounded-md bg-gold-500/15 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gold-700">Admin</span>
                <?php endif; ?>
            </a>

            <nav class="-mr-2 flex-1 space-y-1 overflow-y-auto pr-2">
                <?php $__currentLoopData = $nav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $active = request()->routeIs($item['route']); ?>
                    <a href="<?php echo e(route($item['route'])); ?>"
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition <?php echo e($active ? 'bg-ink-900 font-semibold text-white' : 'font-medium text-ink-500 hover:bg-ink-100 hover:text-ink-900'); ?>">
                        <svg class="h-[18px] w-[18px] <?php echo e($active ? 'text-gold-400' : 'text-ink-400'); ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($item['icon']); ?>"/>
                        </svg>
                        <span class="flex-1"><?php echo e($item['label']); ?></span>
                        <?php if(! empty($item['badge']) && $unread > 0): ?>
                            <span class="ml-auto grid h-5 min-w-[20px] place-items-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold text-white"><?php echo e($unread > 99 ? '99+' : $unread); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>

            <?php if(! $isAdminArea && $r): ?>
                <div class="mt-4 shrink-0 rounded-2xl bg-ink-50 p-4 ring-1 ring-ink-100">
                    <p class="text-xs font-semibold text-ink-500">Yayın durumu</p>
                    <?php
                        $badge = match ($r->status) {
                            'approved' => ['Yayında', 'bg-emerald-100 text-emerald-700'],
                            'pending' => ['Onayda', 'bg-amber-100 text-amber-700'],
                            'rejected' => ['Reddedildi', 'bg-red-100 text-red-700'],
                            default => ['Taslak', 'bg-ink-200 text-ink-700'],
                        };
                    ?>
                    <span class="mt-1.5 inline-block rounded-lg px-2 py-1 text-xs font-semibold <?php echo e($badge[1]); ?>"><?php echo e($badge[0]); ?></span>
                    <?php if($r->isLive()): ?>
                        <a href="<?php echo e(tenant_domain($r)); ?>" target="_blank" class="mt-2 block truncate text-xs font-medium text-gold-700 hover:underline">
                            <?php echo e($r->subdomain); ?>.<?php echo e(config('neva.root_domain')); ?> ↗
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="mt-3 shrink-0 border-t border-ink-100 pt-3">
                <div class="flex items-center gap-3 px-2 py-1.5">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-ink-900 text-sm font-semibold text-white">
                        <?php echo e(Str::of(auth()->user()->name)->substr(0, 1)->upper()); ?>

                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-ink-900"><?php echo e(auth()->user()->name); ?></span>
                        <span class="block truncate text-xs text-ink-400"><?php echo e(auth()->user()->email); ?></span>
                    </span>
                </div>

                <form method="POST" action="<?php echo e(route('logout')); ?>" class="mt-1">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-600 hover:text-white hover:border-red-600 active:scale-[.98]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m0 0l4-4m-4 4l4 4m6-11V5a2 2 0 012-2h4a2 2 0 012 2v14a2 2 0 01-2 2h-4a2 2 0 01-2-2v-1"/>
                        </svg>
                        Çıkış Yap
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div x-show="sidebar" @click="sidebar = false" class="fixed inset-0 z-30 bg-ink-950/40 lg:hidden" style="display:none"></div>

    
    <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-ink-100 bg-ink-50/80 px-6 backdrop-blur">
            <button @click="sidebar = true" class="lg:hidden" aria-label="Menü">
                <svg class="h-6 w-6 text-ink-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="min-w-0 flex-1">
                <?php if(isset($header)): ?>
                    <h1 class="truncate font-display text-lg text-ink-900"><?php echo e($header); ?></h1>
                <?php endif; ?>
            </div>
            <?php if(isset($actions)): ?>
                <div class="flex items-center gap-2"><?php echo e($actions); ?></div>
            <?php endif; ?>

            <?php if (isset($component)) { $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown','data' => ['align' => 'right','width' => 'w-56']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right','width' => 'w-56']); ?>
                 <?php $__env->slot('trigger', null, []); ?> 
                    <button class="grid h-9 w-9 place-items-center rounded-full bg-ink-900 text-sm font-semibold text-white ring-2 ring-transparent transition hover:ring-gold-400" aria-label="Hesap">
                        <?php echo e(Str::of(auth()->user()->name)->substr(0, 1)->upper()); ?>

                    </button>
                 <?php $__env->endSlot(); ?>
                 <?php $__env->slot('content', null, []); ?> 
                    <div class="border-b border-ink-100 px-4 py-2.5">
                        <p class="truncate text-sm font-semibold text-ink-900"><?php echo e(auth()->user()->name); ?></p>
                        <p class="truncate text-xs text-ink-400"><?php echo e(auth()->user()->email); ?></p>
                    </div>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m0 0l4-4m-4 4l4 4m6-11V5a2 2 0 012-2h4a2 2 0 012 2v14a2 2 0 01-2 2h-4a2 2 0 01-2-2v-1"/>
                            </svg>
                            Çıkış Yap
                        </button>
                    </form>
                 <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $attributes = $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $component = $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
        </header>

        <?php if(session('success') || session('status')): ?>
            <div class="mx-6 mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
                <?php echo e(session('success') ?? session('status')); ?>

            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mx-6 mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700 ring-1 ring-red-200">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <main class="flex-1 p-6">
            <?php echo e($slot); ?>

        </main>
    </div>
</div>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/components/app-layout.blade.php ENDPATH**/ ?>