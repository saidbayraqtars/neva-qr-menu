<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
    'jsonld' => null,
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
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
    'jsonld' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $brand = config('neva.brand.name');
    $seoTitle = $title ? $title.' · '.$brand : config('neva.seo.default_title');
    $seoDesc = \Illuminate\Support\Str::limit($description ?: config('neva.seo.default_description'), 160);
    $seoUrl = $canonical ?: url()->current();
    $seoImage = $image ?: asset(config('neva.seo.og_image'));
?>

<title><?php echo e($seoTitle); ?></title>
<meta name="description" content="<?php echo e($seoDesc); ?>">
<link rel="canonical" href="<?php echo e($seoUrl); ?>">

<?php if($noindex): ?>
    <meta name="robots" content="noindex, nofollow">
<?php else: ?>
    <meta name="robots" content="index, follow, max-image-preview:large">
<?php endif; ?>


<meta property="og:type" content="<?php echo e($type); ?>">
<meta property="og:site_name" content="<?php echo e($brand); ?>">
<meta property="og:locale" content="tr_TR">
<meta property="og:title" content="<?php echo e($seoTitle); ?>">
<meta property="og:description" content="<?php echo e($seoDesc); ?>">
<meta property="og:url" content="<?php echo e($seoUrl); ?>">
<meta property="og:image" content="<?php echo e($seoImage); ?>">


<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo e($seoTitle); ?>">
<meta name="twitter:description" content="<?php echo e($seoDesc); ?>">
<meta name="twitter:image" content="<?php echo e($seoImage); ?>">
<?php if(config('neva.seo.twitter_site')): ?>
    <meta name="twitter:site" content="<?php echo e(config('neva.seo.twitter_site')); ?>">
<?php endif; ?>

<?php if($jsonld): ?>
    <script type="application/ld+json"><?php echo json_encode($jsonld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<?php endif; ?>
<?php /**PATH C:\Users\saidb\Desktop\projeler\nevaqrmenü\resources\views/components/seo.blade.php ENDPATH**/ ?>