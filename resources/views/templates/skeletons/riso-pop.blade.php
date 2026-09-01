{{-- Riso Pop — risograph / zine estetiği; sıcak kağıt, punchy 2-renk, renkli offset gölge kartlar --}}
<div class="rp">
    <header class="rp-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="rp-logo" />
        <h1 class="rp-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="rp-tag">{{ $restaurant->tagline }}</p>@endif
    </header>

    @forelse ($categories as $category)
        <section class="rp-cat">
            <h2 class="rp-cat-name tpl-h">{{ $category->name }}</h2>
            @if ($category->description)<p class="rp-cat-desc">{{ $category->description }}</p>@endif
            <div class="rp-grid">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="rp-card {{ $img ? 'has-media' : '' }}" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="rp-media"><img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy"></div>@endif
                        <div class="rp-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="rp-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="rp-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="rp-price" />
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
