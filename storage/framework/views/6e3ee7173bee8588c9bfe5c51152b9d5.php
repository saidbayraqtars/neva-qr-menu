<?php
    $p = $presenter;
    $showPrices = (bool) $p->get('show_prices', true);
    $showCalories = (bool) ($restaurant->show_calories ?? false);
    $showDesc = $p->showsDescription();
    $currency = $p->get('currency', 'TRY');
    $logoUrl = $p->logoUrl();
    $coverUrl = $p->coverUrl();
    $view = $view ?? 'phone';
    $embedded = $embedded ?? false;
    $print = $print ?? false;
    $tableLabel = $tableLabel ?? null;
?>

<?php if (isset($component)) { $__componentOriginal8fff036cb47a5f052bc2922c7a886317 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8fff036cb47a5f052bc2922c7a886317 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.templates.shell','data' => ['presenter' => $p,'restaurant' => $restaurant,'view' => $view,'embedded' => $embedded,'print' => $print,'tableLabel' => $tableLabel,'categories' => $categories ?? collect()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('templates.shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['presenter' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p),'restaurant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($restaurant),'view' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($view),'embedded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($embedded),'print' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($print),'tableLabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tableLabel),'categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories ?? collect())]); ?>
    <?php echo $__env->make('templates.skeletons.'.$p->key, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8fff036cb47a5f052bc2922c7a886317)): ?>
<?php $attributes = $__attributesOriginal8fff036cb47a5f052bc2922c7a886317; ?>
<?php unset($__attributesOriginal8fff036cb47a5f052bc2922c7a886317); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8fff036cb47a5f052bc2922c7a886317)): ?>
<?php $component = $__componentOriginal8fff036cb47a5f052bc2922c7a886317; ?>
<?php unset($__componentOriginal8fff036cb47a5f052bc2922c7a886317); ?>
<?php endif; ?>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/templates/show.blade.php ENDPATH**/ ?>