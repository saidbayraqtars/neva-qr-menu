<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['product', 'showCalories' => false]));

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

foreach (array_filter((['product', 'showCalories' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php $tags = is_array($product->tags) ? $product->tags : []; ?>

<?php if(count($tags) || ($showCalories && $product->calories)): ?>
    <div <?php echo e($attributes->merge(['class' => 'tpl-badges'])); ?>>
        <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span class="tpl-badge"><?php echo e($tag); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if($showCalories && $product->calories): ?><span class="tpl-badge tpl-badge-kcal"><?php echo e($product->calories); ?> kcal</span><?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/components/tpl/badges.blade.php ENDPATH**/ ?>