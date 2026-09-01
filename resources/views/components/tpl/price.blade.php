@props(['product', 'currency' => 'TRY', 'show' => true, 'class' => 'tpl-price'])

@if ($show)
    <span class="{{ $class }}">
        @if ($product->discount_price)<span class="tpl-strike">{{ money($product->price, $currency) }}</span>@endif{{ money($product->discount_price ?? $product->price, $currency) }}
    </span>
@endif
