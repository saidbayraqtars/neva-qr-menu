{{-- Golden Hour — merkezî hizalı; ad üstte, açıklama ortada, fiyat altta rozet içinde --}}
<div class="gh">
    <header class="gh-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="gh-logo" />
        <h1 class="gh-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="gh-tag">{{ $restaurant->tagline }}</p>@endif
        <span class="gh-sun" aria-hidden="true"></span>
    </header>


    @forelse ($categories as $category)
        <section class="gh-cat">
            <h2 class="gh-cat-name tpl-h">{{ $category->name }}</h2>
            @if ($category->description)<p class="gh-cat-desc">{{ $category->description }}</p>@endif
            <div class="gh-list">
                @foreach ($category->products as $product)
                    <article class="gh-item" {!! flag_attrs($product) !!}>
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        <h3 class="gh-name tpl-h">{{ $product->name }}</h3>
                        @if ($showDesc && $product->description)<p class="gh-desc">{{ $product->description }}</p>@endif
                        <x-tpl.badges :product="$product" :show-calories="$showCalories" class="gh-badges" />
                        @if ($showPrices)
                            <span class="gh-price">
                                @if ($product->discount_price)<span class="tpl-strike">{{ money($product->price, $currency) }}</span>@endif{{ money($product->discount_price ?? $product->price, $currency) }}
                            </span>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
