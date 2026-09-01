@props(['product', 'showCalories' => false])
@php $tags = is_array($product->tags) ? $product->tags : []; @endphp

@if (count($tags) || ($showCalories && $product->calories))
    <div {{ $attributes->merge(['class' => 'tpl-badges']) }}>
        @foreach ($tags as $tag)<span class="tpl-badge">{{ $tag }}</span>@endforeach
        @if ($showCalories && $product->calories)<span class="tpl-badge tpl-badge-kcal">{{ $product->calories }} kcal</span>@endif
    </div>
@endif
