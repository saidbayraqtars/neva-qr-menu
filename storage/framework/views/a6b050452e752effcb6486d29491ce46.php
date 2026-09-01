<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['url' => null, 'name' => '', 'class' => '']));

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

foreach (array_filter((['url' => null, 'name' => '', 'class' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($url): ?>
    <img data-tpl-logo src="<?php echo e($url); ?>" alt="<?php echo e($name); ?>" <?php echo e($attributes->merge(['class' => trim('tpl-logo '.$class)])); ?>>
<?php else: ?>
    <img data-tpl-logo alt="" hidden <?php echo e($attributes->merge(['class' => trim('tpl-logo '.$class)])); ?>>
<?php endif; ?>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/components/tpl/logo.blade.php ENDPATH**/ ?>