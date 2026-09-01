@props(['product', 'tpl' => ''])
@php
    $pct = discount_pct($product);
    $featured = (bool) ($product->is_featured ?? false);
    $discounted = (bool) ($product->discount_price ?? false);
    // Lüks / prestij şablonlarda öne çıkan ürün "seçki" dilinde sunulur.
    $luxe = in_array($tpl, ['dark-prestige', 'velvet-noir', 'royal-blue', 'glassmorphism-luxury'], true);
    $featLabel = $luxe ? '★ Özel Seçim' : '★ Öne Çıkan';
    $discLabel = $pct > 0 ? '%'.$pct.' İndirim' : 'İndirim';
@endphp

@if ($featured || $discounted)
    <span class="tpl-flags">
        @if ($featured)<span class="tpl-flag tpl-flag--feat">{{ $featLabel }}</span>@endif
        @if ($discounted)<span class="tpl-flag tpl-flag--disc">{{ $discLabel }}</span>@endif
    </span>
@endif
