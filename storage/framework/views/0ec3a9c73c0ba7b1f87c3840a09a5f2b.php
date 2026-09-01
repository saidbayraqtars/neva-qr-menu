<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'conversation',
    'side' => 'user',      // bu ekranı kim görüyor: user | admin
    'replyAction',
    'pollUrl',
    'closed' => false,
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
    'conversation',
    'side' => 'user',      // bu ekranı kim görüyor: user | admin
    'replyAction',
    'pollUrl',
    'closed' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div x-data="chatThread({
        pollUrl: <?php echo \Illuminate\Support\Js::from($pollUrl)->toHtml() ?>,
        lastId: <?php echo e((int) $conversation->messages->max('id')); ?>,
        side: <?php echo \Illuminate\Support\Js::from($side)->toHtml() ?>,
     })"
     x-init="start()">

    <div class="max-h-[60vh] space-y-4 overflow-y-auto p-6" x-ref="stream">
        <?php $__currentLoopData = $conversation->messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $mine = $message->sender_role === $side; ?>
            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['flex', 'justify-end' => $mine]); ?>">
                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'max-w-[80%] rounded-2xl px-4 py-3 text-sm leading-relaxed',
                    'bg-ink-900 text-white' => $mine,
                    'bg-ink-50 text-ink-800 ring-1 ring-ink-100' => ! $mine,
                ]); ?>">
                    <p class="whitespace-pre-line"><?php echo e($message->body); ?></p>
                    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'mt-1.5 text-[11px]',
                        'text-white/50' => $mine,
                        'text-ink-400' => ! $mine,
                    ]); ?>">
                        <?php echo e($message->sender_role === 'admin' ? 'Destek ekibi' : ($message->sender?->name ?? 'İşletme')); ?>

                        · <?php echo e($message->created_at->format('d.m.Y H:i')); ?>

                    </p>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <template x-for="m in incoming" :key="m.id">
            <div :class="m.role === '<?php echo e($side); ?>' ? 'flex justify-end' : 'flex'">
                <div :class="m.role === '<?php echo e($side); ?>'
                        ? 'max-w-[80%] rounded-2xl bg-ink-900 px-4 py-3 text-sm leading-relaxed text-white'
                        : 'max-w-[80%] rounded-2xl bg-ink-50 px-4 py-3 text-sm leading-relaxed text-ink-800 ring-1 ring-ink-100'">
                    <p class="whitespace-pre-line" x-text="m.body"></p>
                    <p class="mt-1.5 text-[11px] opacity-60" x-text="(m.role === 'admin' ? 'Destek ekibi' : 'İşletme') + ' · ' + m.at"></p>
                </div>
            </div>
        </template>
    </div>

    <?php if($closed): ?>
        <div class="border-t border-ink-100 bg-ink-50 px-6 py-5 text-center text-sm text-ink-500">
            Bu görüşme kapatıldı. Yeni bir konu açabilirsiniz.
        </div>
    <?php else: ?>
        <form method="POST" action="<?php echo e($replyAction); ?>" class="border-t border-ink-100 p-6">
            <?php echo csrf_field(); ?>
            <textarea name="body" rows="3" class="field" required maxlength="5000"
                      placeholder="Mesajınızı yazın…"
                      @keydown.meta.enter="$el.form.requestSubmit()"
                      @keydown.ctrl.enter="$el.form.requestSubmit()"></textarea>
            <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('body'),'class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('body')),'class' => 'mt-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
            <div class="mt-3 flex items-center justify-between gap-3">
                <p class="text-xs text-ink-400">Ctrl/⌘ + Enter ile gönderebilirsiniz.</p>
                <button class="btn-primary">Gönder</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php if (! $__env->hasRenderedOnce('322114eb-2e77-43f1-b9ea-59f34bc0cd6a')): $__env->markAsRenderedOnce('322114eb-2e77-43f1-b9ea-59f34bc0cd6a'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            // Basit polling tabanlı canlı akış. (Faz-2: Laravel Reverb / WebSocket)
            function chatThread({ pollUrl, lastId, side }) {
                return {
                    incoming: [],
                    lastId: lastId,
                    timer: null,
                    start() {
                        this.scrollDown();
                        this.timer = setInterval(() => this.poll(), 5000);
                        document.addEventListener('visibilitychange', () => {
                            if (document.visibilityState === 'visible') this.poll();
                        });
                    },
                    async poll() {
                        try {
                            const res = await fetch(pollUrl + '?after=' + this.lastId, {
                                headers: { 'Accept': 'application/json' },
                            });
                            if (!res.ok) return;

                            const data = await res.json();
                            if (!data.messages || !data.messages.length) return;

                            for (const m of data.messages) {
                                this.incoming.push(m);
                                if (m.id > this.lastId) this.lastId = m.id;
                            }
                            this.$nextTick(() => this.scrollDown());
                        } catch (e) { /* sessizce yut — bir sonraki turda tekrar dener */ }
                    },
                    scrollDown() {
                        const el = this.$refs.stream;
                        if (el) el.scrollTop = el.scrollHeight;
                    },
                };
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/components/chat-thread.blade.php ENDPATH**/ ?>