<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['product', 'currency' => 'TRY', 'show' => true, 'class' => 'tpl-price']));

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

foreach (array_filter((['product', 'currency' => 'TRY', 'show' => true, 'class' => 'tpl-price']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($show): ?>
    <span class="<?php echo e($class); ?>">
        <?php if($product->discount_price): ?><span class="tpl-strike"><?php echo e(money($product->price, $currency)); ?></span><?php endif; ?><?php echo e(money($product->discount_price ?? $product->price, $currency)); ?>

    </span>
<?php endif; ?>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/components/tpl/price.blade.php ENDPATH**/ ?>