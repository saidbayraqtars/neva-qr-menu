{{-- Velvet Noir — kadife siyahı; ters yerleşim: fiyat solda sabit sütun, ad + açıklama sağda --}}
<div class="vn">
    <header class="vn-head">
        <span class="vn-rule" aria-hidden="true"></span>
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="vn-logo" />
        <h1 class="vn-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="vn-tag">{{ $restaurant->tagline }}</p>@endif
        <span class="vn-rule" aria-hidden="true"></span>
    </header>


    @forelse ($categories as $category)
        <section class="vn-cat">
            <h2 class="vn-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="vn-list">
                @foreach ($category->products as $product)
                    <article class="vn-row" {!! flag_attrs($product) !!}>
                        <div class="vn-price-col">
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="vn-price" />
                        </div>
                        <div class="vn-text-col">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="vn-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="vn-desc">{{ $product->description }}</p>@endif
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
