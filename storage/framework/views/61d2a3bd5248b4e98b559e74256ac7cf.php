<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['product', 'tpl' => '']));

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

foreach (array_filter((['product', 'tpl' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $pct = discount_pct($product);
    $featured = (bool) ($product->is_featured ?? false);
    $discounted = (bool) ($product->discount_price ?? false);
    // Lüks / prestij şablonlarda öne çıkan ürün "seçki" dilinde sunulur.
    $luxe = in_array($tpl, ['dark-prestige', 'velvet-noir', 'royal-blue', 'glassmorphism-luxury'], true);
    $featLabel = $luxe ? '★ Özel Seçim' : '★ Öne Çıkan';
    $discLabel = $pct > 0 ? '%'.$pct.' İndirim' : 'İndirim';
?>

<?php if($featured || $discounted): ?>
    <span class="tpl-flags">
        <?php if($featured): ?><span class="tpl-flag tpl-flag--feat"><?php echo e($featLabel); ?></span><?php endif; ?>
        <?php if($discounted): ?><span class="tpl-flag tpl-flag--disc"><?php echo e($discLabel); ?></span><?php endif; ?>
    </span>
<?php endif; ?>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/components/tpl/flags.blade.php ENDPATH**/ ?>