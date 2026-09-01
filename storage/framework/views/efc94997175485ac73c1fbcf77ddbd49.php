<?php if (isset($component)) { $__componentOriginal4619374cef299e94fd7263111d0abc69 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4619374cef299e94fd7263111d0abc69 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-layout','data' => ['title' => $conversation->subject]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($conversation->subject)]); ?>
     <?php $__env->slot('header', null, []); ?> <?php echo e($conversation->subject); ?> <?php $__env->endSlot(); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e(route('panel.messages.index')); ?>" class="btn-ghost">← Tüm mesajlar</a>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-3xl">
        <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-ink-100/80 shadow-[0_1px_2px_rgba(23,23,15,.04),0_26px_50px_-30px_rgba(23,23,15,.22)]">
            <?php if (isset($component)) { $__componentOriginale7ef1ff05eb5f36e2a5a7a23eb22bc30 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale7ef1ff05eb5f36e2a5a7a23eb22bc30 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chat-thread','data' => ['conversation' => $conversation,'side' => 'user','replyAction' => route('panel.messages.reply', $conversation),'pollUrl' => route('panel.messages.poll', $conversation),'closed' => ! $conversation->isOpen()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('chat-thread'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['conversation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($conversation),'side' => 'user','reply-action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('panel.messages.reply', $conversation)),'poll-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('panel.messages.poll', $conversation)),'closed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(! $conversation->isOpen())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale7ef1ff05eb5f36e2a5a7a23eb22bc30)): ?>
<?php $attributes = $__attributesOriginale7ef1ff05eb5f36e2a5a7a23eb22bc30; ?>
<?php unset($__attributesOriginale7ef1ff05eb5f36e2a5a7a23eb22bc30); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale7ef1ff05eb5f36e2a5a7a23eb22bc30)): ?>
<?php $component = $__componentOriginale7ef1ff05eb5f36e2a5a7a23eb22bc30; ?>
<?php unset($__componentOriginale7ef1ff05eb5f36e2a5a7a23eb22bc30); ?>
<?php endif; ?>
        </div>
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
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/panel/messages/show.blade.php ENDPATH**/ ?>