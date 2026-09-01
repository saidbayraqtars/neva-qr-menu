{{-- Prime Steakhouse — koyu kömür + derin kırmızı, iri sıkışık display, yatay fotoğraf bantları --}}
<div class="ps">
    <header class="ps-head {{ $coverUrl ? 'has-cover' : '' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="ps-logo" />
        <h1 class="ps-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="ps-tag">{{ $restaurant->tagline }}</p>@endif
    </header>

    @forelse ($categories as $category)
        <section class="ps-cat">
            <h2 class="ps-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="ps-list">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="ps-row {{ $img ? 'has-media' : '' }}" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="ps-media"><img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy"></div>@endif
                        <div class="ps-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <div class="ps-line">
                                <h3 class="ps-name tpl-h">{{ $product->name }}</h3>
                                <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="ps-price" />
                            </div>
                            @if ($showDesc && $product->description)<p class="ps-desc">{{ $product->description }}</p>@endif
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
