{{-- Gourmet Masonry — Pinterest tarzı akışkan yükseklikli görsel kartlar --}}
<div class="gmm">
    <header class="gmm-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="gmm-logo" />
        <h1 class="gmm-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="gmm-tag">{{ $restaurant->tagline }}</p>@endif
    </header>


    @forelse ($categories as $category)
        <section class="gmm-cat">
            <h2 class="gmm-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="gmm-masonry">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="gmm-card" {!! flag_attrs($product) !!}>
                        @if ($img)<img class="gmm-img" src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">@endif
                        <div class="gmm-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="gmm-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="gmm-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="gmm-price" />
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
