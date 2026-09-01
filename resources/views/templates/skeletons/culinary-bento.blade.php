{{-- Culinary Bento — Apple tarzı bento grid; ilk ürün geniş, diğerleri kare --}}
<div class="cbn">
    <header class="cbn-head {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <div class="cbn-head-inner">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="cbn-logo" />
            <h1 class="cbn-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="cbn-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="cbn-cat">
            <h2 class="cbn-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="cbn-grid">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="cbn-card {{ $loop->first ? 'is-wide' : '' }}" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="cbn-media"><img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy"></div>@endif
                        <div class="cbn-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="cbn-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="cbn-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="cbn-price" />
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
