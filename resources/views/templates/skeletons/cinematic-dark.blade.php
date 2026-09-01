{{-- Cinematic Dark — koyu; her kartın arkasında blur'lu ürün görseli, neon fiyat --}}
<div class="cnd">
    <header class="cnd-head {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <div class="cnd-head-inner">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="cnd-logo" />
            <h1 class="cnd-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="cnd-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="cnd-cat">
            <h2 class="cnd-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="cnd-list">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="cnd-card" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="cnd-bg" style="background-image:url('{{ $img }}')"></div>@endif
                        <div class="cnd-scrim"></div>
                        <div class="cnd-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="cnd-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="cnd-desc">{{ $product->description }}</p>@endif
                            <div class="cnd-foot">
                                <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="cnd-price" />
                                <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                            </div>
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
