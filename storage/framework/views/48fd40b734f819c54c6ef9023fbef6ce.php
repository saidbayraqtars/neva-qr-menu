<?php if (isset($component)) { $__componentOriginal4619374cef299e94fd7263111d0abc69 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4619374cef299e94fd7263111d0abc69 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-layout','data' => ['title' => 'Genel Bakış']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Genel Bakış']); ?>
     <?php $__env->slot('header', null, []); ?> Merhaba, <?php echo e(Str::of(auth()->user()->name)->before(' ')); ?> 👋 <?php $__env->endSlot(); ?>

    <?php
        $statusBadge = match ($restaurant->status) {
            'approved' => ['Yayında', 'bg-emerald-100 text-emerald-700'],
            'pending' => ['Onayda bekliyor', 'bg-amber-100 text-amber-700'],
            'rejected' => ['Reddedildi', 'bg-red-100 text-red-700'],
            default => ['Taslak', 'bg-ink-100 text-ink-600'],
        };
        $card = 'rounded-3xl bg-white p-7 ring-1 ring-ink-100/80 shadow-[0_1px_2px_rgba(23,23,15,.04),0_26px_50px_-30px_rgba(23,23,15,.22)]';
    ?>

    <div class="mx-auto max-w-4xl space-y-6">

        
        <div class="relative overflow-hidden <?php echo e($card); ?>">
            <div class="pointer-events-none absolute -right-16 -top-20 h-52 w-52 rounded-full bg-gold-500/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($statusBadge[1]); ?>"><?php echo e($statusBadge[0]); ?></span>
                    <h2 class="mt-2 truncate font-display text-2xl text-ink-900"><?php echo e($restaurant->name); ?></h2>
                    <?php if($restaurant->isLive()): ?>
                        <a href="<?php echo e(tenant_domain($restaurant)); ?>" target="_blank" rel="noopener" class="mt-1 inline-block text-sm font-medium text-gold-700 hover:underline">
                            <?php echo e($restaurant->subdomain); ?>.<?php echo e(config('neva.root_domain')); ?> ↗
                        </a>
                    <?php elseif($selfHosted && filled($restaurant->external_menu_url)): ?>
                        <a href="<?php echo e($restaurant->external_menu_url); ?>" target="_blank" rel="noopener" class="mt-1 inline-block truncate text-sm font-medium text-gold-700 hover:underline">
                            <?php echo e($restaurant->external_menu_url); ?> ↗
                        </a>
                    <?php endif; ?>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="<?php echo e(route('panel.design.edit', ['mode' => 'edit'])); ?>" class="btn-ghost">Tasarımı düzenle</a>
                    <a href="<?php echo e(route('panel.qr.index')); ?>" class="btn-primary px-4">QR kodu</a>
                </div>
            </div>

            <div class="relative mt-7 grid grid-cols-3 gap-3">
                <?php $__currentLoopData = [
                    ['Kategori', $restaurant->categories_count, route('panel.categories.index')],
                    ['Ürün', $restaurant->products_count, route('panel.products.index')],
                    [$selfHosted ? 'Şablon' : 'Masa', $selfHosted ? '1' : $restaurant->tables_count, $selfHosted ? route('panel.design.edit') : route('panel.qr.index')],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $href]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($href); ?>" class="group rounded-2xl bg-ink-50 p-4 ring-1 ring-ink-100 transition hover:-translate-y-0.5 hover:bg-white hover:shadow-luxe hover:ring-gold-400/60">
                        <p class="font-display text-3xl text-ink-900"><?php echo e($value); ?></p>
                        <p class="mt-0.5 text-xs font-medium uppercase tracking-wide text-ink-400 group-hover:text-ink-500"><?php echo e($label); ?></p>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <?php if($canSubdomain): ?>
            <?php echo $__env->make('panel.publish-card', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($selfHosted): ?>
            <div class="<?php echo e($card); ?>">
                <h3 class="font-display text-lg text-ink-900">Menü QR kodunuz</h3>
                <p class="mt-1.5 text-sm leading-relaxed text-ink-500">
                    Paketiniz <strong>Hosting Hariç</strong> — menünüzü kendiniz barındırırsınız.
                    QR bölümünden menünüzün web adresini girin, sisteme özel statik QR kodunuzu
                    indirip masalarınıza bastırın.
                </p>
                <a href="<?php echo e(route('panel.qr.index')); ?>" class="btn-gold mt-5">QR kodunu oluştur →</a>

                <div class="mt-5 border-t border-ink-100 pt-5">
                    <p class="text-sm text-ink-500">
                        Kendi alan adınızda (<span class="font-mono">isletmeniz.<?php echo e(config('neva.root_domain')); ?></span>)
                        yayınlanan, panelden anında güncellenen bir menü ister misiniz?
                    </p>
                    <a href="<?php echo e(route('panel.messages.index')); ?>" class="btn-ghost mt-3">Paket yükseltme talebi →</a>
                </div>
            </div>
        <?php else: ?>
            
            <div class="<?php echo e($card); ?>">
                <h3 class="font-display text-lg text-ink-900">Paketiniz henüz tanımlı değil</h3>
                <p class="mt-1.5 text-sm leading-relaxed text-ink-500">
                    Menünüzü hazırlamaya şimdiden başlayabilirsiniz. Yayına alma ve QR özellikleri
                    paketiniz tanımlandığında açılır.
                </p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="<?php echo e(route('pricing')); ?>" class="btn-gold">Paketleri incele</a>
                    <a href="<?php echo e(route('panel.messages.index')); ?>" class="btn-ghost">Ekibe mesaj yaz</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $attributes = $__attributesOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__attributesOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $component = $__componentOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__componentOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/panel/dashboard.blade.php ENDPATH**/ ?>