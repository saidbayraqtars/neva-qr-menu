{{-- Polaroid Vibe — ürün fotoğrafları polaroid çerçevesinde, hafif eğik, el yazısı --}}
<div class="pol">
    <header class="pol-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="pol-logo" />
        <h1 class="pol-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="pol-tag">{{ $restaurant->tagline }}</p>@endif
    </header>


    @forelse ($categories as $category)
        <section class="pol-cat">
            <h2 class="pol-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="pol-masonry">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="pol-card" data-tilt="{{ $loop->index % 3 }}" {!! flag_attrs($product) !!}>
                        <div class="pol-frame">
                            @if ($img)<img class="pol-img" src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">@else<div class="pol-img pol-img--empty"></div>@endif
                        </div>
                        <div class="pol-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="pol-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="pol-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="pol-price" />
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
