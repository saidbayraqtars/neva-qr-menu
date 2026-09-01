{{-- Floating Image — ürün görseli kartın üstünden taşar, 3D gölge --}}
<div class="fli">
    <header class="fli-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="fli-logo" />
        <h1 class="fli-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="fli-tag">{{ $restaurant->tagline }}</p>@endif
    </header>


    @forelse ($categories as $category)
        <section class="fli-cat">
            <h2 class="fli-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="fli-grid">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="fli-card" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="fli-float"><img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy"></div>@endif
                        <div class="fli-body {{ $img ? 'has-float' : '' }}">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="fli-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="fli-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="fli-price" />
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
