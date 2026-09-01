{{-- Glassmorphism Luxury — animasyonlu blur zemin + backdrop-blur cam kartlar grid'i --}}
<div class="gl">
    <div class="gl-orbs" aria-hidden="true"><span></span><span></span><span></span></div>

    <header class="gl-head {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <div class="gl-glass gl-head-inner">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="gl-logo" />
            <h1 class="gl-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="gl-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="gl-cat">
            <h2 class="gl-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="gl-grid">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="gl-glass gl-card" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="gl-img"><img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy"></div>@endif
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        <h3 class="gl-name tpl-h">{{ $product->name }}</h3>
                        @if ($showDesc && $product->description)<p class="gl-desc">{{ $product->description }}</p>@endif
                        <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                        <div class="gl-price-row">
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="gl-price" />
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
