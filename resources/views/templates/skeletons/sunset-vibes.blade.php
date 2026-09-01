{{-- Sunset Vibes — mor-pembe gradient zemin, parıltılı masonry kart akışı --}}
<div class="sv">
    <div class="sv-glow" aria-hidden="true"></div>

    <header class="sv-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="sv-logo" />
        <h1 class="sv-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="sv-tag">{{ $restaurant->tagline }}</p>@endif
    </header>


    @forelse ($categories as $category)
        <section class="sv-cat">
            <h2 class="sv-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="sv-masonry">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="sv-card" {!! flag_attrs($product) !!}>
                        @if ($img)<img class="sv-img" src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">@endif
                        <div class="sv-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="sv-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="sv-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="sv-price" />
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
