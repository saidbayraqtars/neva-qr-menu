@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
    'jsonld' => null,
])

@php
    $brand = config('neva.brand.name');
    $seoTitle = $title ? $title.' · '.$brand : config('neva.seo.default_title');
    $seoDesc = \Illuminate\Support\Str::limit($description ?: config('neva.seo.default_description'), 160);
    $seoUrl = $canonical ?: url()->current();
    $seoImage = $image ?: asset(config('neva.seo.og_image'));
@endphp

<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDesc }}">
<link rel="canonical" href="{{ $seoUrl }}">

@if ($noindex)
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large">
@endif

{{-- Open Graph --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $brand }}">
<meta property="og:locale" content="tr_TR">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDesc }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:image" content="{{ $seoImage }}">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDesc }}">
<meta name="twitter:image" content="{{ $seoImage }}">
@if (config('neva.seo.twitter_site'))
    <meta name="twitter:site" content="{{ config('neva.seo.twitter_site') }}">
@endif

@if ($jsonld)
    <script type="application/ld+json">{!! json_encode($jsonld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
