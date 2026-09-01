{{-- Saffron Table — sıcak baharat paleti, süslü çift kural ayraç, yuvarlak fotoğraf thumb + metin sütunu --}}
<div class="sf">
    <header class="sf-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="sf-logo" />
        <h1 class="sf-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="sf-tag">{{ $restaurant->tagline }}</p>@endif
        <div class="sf-orn" aria-hidden="true">✦</div>
    </header>

    @forelse ($categories as $category)
        <section class="sf-cat">
            <h2 class="sf-cat-name tpl-h"><span>{{ $category->name }}</span></h2>
            @if ($category->description)<p class="sf-cat-desc">{{ $category->description }}</p>@endif
            <div class="sf-list">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="sf-card" {!! flag_attrs($product) !!}>
                        @if ($img)
                            <div class="sf-thumb"><img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy"></div>
                        @endif
                        <div class="sf-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="sf-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="sf-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                        </div>
                        <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="sf-price" />
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
