{{-- Compact Fast — sıkıştırılmış dikey liste, devasa net fiyatlar, hız odaklı --}}
<div class="cf">
    <header class="cf-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="cf-logo" />
        <div>
            <h1 class="cf-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="cf-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <div class="cf-cat-bar">{{ $category->name }}</div>
        <ul class="cf-list">
            @foreach ($category->products as $product)
                <li class="cf-row {{ $product->is_available ? '' : 'is-out' }}" {!! flag_attrs($product) !!}>
                    <div class="cf-main">
                        <span class="cf-name">{{ $product->name }}</span>
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        @if ($showDesc && $product->description)<span class="cf-desc">{{ $product->description }}</span>@endif
                    </div>
                    @if ($showPrices)
                        <span class="cf-price">
                            @if ($product->discount_price)<span class="cf-old">{{ money($product->price, $currency) }}</span>@endif
                            {{ money($product->discount_price ?? $product->price, $currency) }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
