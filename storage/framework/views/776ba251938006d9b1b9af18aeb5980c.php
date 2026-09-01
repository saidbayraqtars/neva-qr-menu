
<div class="gh">
    <header class="gh-head">
        <?php if (isset($component)) { $__componentOriginal6966d1b248e115f52208004255e3317a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6966d1b248e115f52208004255e3317a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tpl.logo','data' => ['url' => $logoUrl,'name' => $restaurant->name,'class' => 'gh-logo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tpl.logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($logoUrl),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($restaurant->name),'class' => 'gh-logo']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6966d1b248e115f52208004255e3317a)): ?>
<?php $attributes = $__attributesOriginal6966d1b248e115f52208004255e3317a; ?>
<?php unset($__attributesOriginal6966d1b248e115f52208004255e3317a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6966d1b248e115f52208004255e3317a)): ?>
<?php $component = $__componentOriginal6966d1b248e115f52208004255e3317a; ?>
<?php unset($__componentOriginal6966d1b248e115f52208004255e3317a); ?>
<?php endif; ?>
        <h1 class="gh-title tpl-h"><?php echo e($restaurant->name); ?></h1>
        <?php if($restaurant->tagline): ?><p class="gh-tag"><?php echo e($restaurant->tagline); ?></p><?php endif; ?>
        <span class="gh-sun" aria-hidden="true"></span>
    </header>


    <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <section class="gh-cat">
            <h2 class="gh-cat-name tpl-h"><?php echo e($category->name); ?></h2>
            <?php if($category->description): ?><p class="gh-cat-desc"><?php echo e($category->description); ?></p><?php endif; ?>
            <div class="gh-list">
                <?php $__currentLoopData = $category->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="gh-item" <?php echo flag_attrs($product); ?>>
                        <?php if (isset($component)) { $__componentOriginalb5e72bb8066afec5a1cc6459381806da = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5e72bb8066afec5a1cc6459381806da = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tpl.flags','data' => ['product' => $product,'tpl' => $p->key]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tpl.flags'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product),'tpl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p->key)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb5e72bb8066afec5a1cc6459381806da)): ?>
<?php $attributes = $__attributesOriginalb5e72bb8066afec5a1cc6459381806da; ?>
<?php unset($__attributesOriginalb5e72bb8066afec5a1cc6459381806da); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb5e72bb8066afec5a1cc6459381806da)): ?>
<?php $component = $__componentOriginalb5e72bb8066afec5a1cc6459381806da; ?>
<?php unset($__componentOriginalb5e72bb8066afec5a1cc6459381806da); ?>
<?php endif; ?>
                        <h3 class="gh-name tpl-h"><?php echo e($product->name); ?></h3>
                        <?php if($showDesc && $product->description): ?><p class="gh-desc"><?php echo e($product->description); ?></p><?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal2bfc487cc4e2a419be94b9713cdfbc36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2bfc487cc4e2a419be94b9713cdfbc36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tpl.badges','data' => ['product' => $product,'showCalories' => $showCalories,'class' => 'gh-badges']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tpl.badges'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product),'show-calories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($showCalories),'class' => 'gh-badges']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2bfc487cc4e2a419be94b9713cdfbc36)): ?>
<?php $attributes = $__attributesOriginal2bfc487cc4e2a419be94b9713cdfbc36; ?>
<?php unset($__attributesOriginal2bfc487cc4e2a419be94b9713cdfbc36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2bfc487cc4e2a419be94b9713cdfbc36)): ?>
<?php $component = $__componentOriginal2bfc487cc4e2a419be94b9713cdfbc36; ?>
<?php unset($__componentOriginal2bfc487cc4e2a419be94b9713cdfbc36); ?>
<?php endif; ?>
                        <?php if($showPrices): ?>
                            <span class="gh-price">
                                <?php if($product->discount_price): ?><span class="tpl-strike"><?php echo e(money($product->price, $currency)); ?></span><?php endif; ?><?php echo e(money($product->discount_price ?? $product->price, $currency)); ?>

                            </span>
                        <?php endif; ?>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    <?php endif; ?>

    <?php echo $__env->make('templates.partials.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/templates/skeletons/golden-hour.blade.php ENDPATH**/ ?>