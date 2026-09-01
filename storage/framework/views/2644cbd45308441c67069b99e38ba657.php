<?php
    $ig = $restaurant->instagram ? ltrim($restaurant->instagram, '@') : null;
    $wa = $restaurant->whatsapp ? preg_replace('/\D/', '', $restaurant->whatsapp) : null;
?>
<footer class="tpl-foot">
    <?php if($ig || $wa): ?>
        <div class="tpl-social">
            <?php if($ig): ?>
                <a href="https://instagram.com/<?php echo e($ig); ?>" target="_blank" rel="noopener" aria-label="Instagram">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg>
                </a>
            <?php endif; ?>
            <?php if($wa): ?>
                <a href="https://wa.me/<?php echo e($wa); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 10-8.13-5.13L3 21l5.13-.87A8.96 8.96 0 0012 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.6 9.4c0 3.4 2.5 5.9 5.9 5.9.8 0 1.2-.9.7-1.5l-1-1.1a1 1 0 00-1.2-.2l-.5.3c-1-.5-1.7-1.2-2.2-2.2l.3-.6a1 1 0 00-.2-1.2l-1.1-1c-.6-.5-1.5-.1-1.5.7z"/></svg>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if($restaurant->address): ?>
        <p class="tpl-address"><?php echo e($restaurant->address); ?></p>
    <?php endif; ?>
    <a class="tpl-credit" href="<?php echo e('https://'.parse_url(config('app.url'), PHP_URL_HOST)); ?>"><?php echo e(config('neva.brand.name')); ?> ile hazırlandı</a>
</footer>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/templates/partials/foot.blade.php ENDPATH**/ ?>