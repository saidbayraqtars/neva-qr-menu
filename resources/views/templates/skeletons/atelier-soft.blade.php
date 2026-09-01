{{-- Atelier Soft — yumuşak pastel stüdyo; rounded-3xl beyaz kartlar, üstte fotoğraf, dusty-rose vurgu --}}
<div class="at">
    <header class="at-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="at-logo" />
        <h1 class="at-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="at-tag">{{ $restaurant->tagline }}</p>@endif
    </header>

    @forelse ($categories as $category)
        <section class="at-cat">
            <h2 class="at-cat-name tpl-h">{{ $category->name }}</h2>
            @if ($category->description)<p class="at-cat-desc">{{ $category->description }}</p>@endif
            <div class="at-stack">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="at-card {{ $img ? 'has-media' : '' }}" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="at-media"><img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy"></div>@endif
                        <div class="at-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <div class="at-line">
                                <h3 class="at-name tpl-h">{{ $product->name }}</h3>
                                <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="at-price" />
                            </div>
                            @if ($showDesc && $product->description)<p class="at-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
