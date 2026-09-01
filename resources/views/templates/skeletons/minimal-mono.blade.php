{{-- Minimal Mono — saf siyah-beyaz, görselsiz, numara + tipografi hiyerarşisi --}}
<div class="mm">
    <header class="mm-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="mm-logo" />
        <h1 class="mm-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="mm-tag">{{ $restaurant->tagline }}</p>@endif
    </header>

    @forelse ($categories as $category)
        <section class="mm-cat">
            <div class="mm-cat-head">
                <span class="mm-cat-no">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <h2 class="mm-cat-name tpl-h">{{ $category->name }}</h2>
            </div>
            <ul class="mm-list">
                @foreach ($category->products as $product)
                    <li class="mm-row" {!! flag_attrs($product) !!}>
                        <div class="mm-row-main">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <span class="mm-name">{{ $product->name }}</span>
                            @if ($showDesc && $product->description)<span class="mm-desc">{{ $product->description }}</span>@endif
                        </div>
                        <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="mm-price" />
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
