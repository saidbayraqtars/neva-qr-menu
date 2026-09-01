{{-- Split Card — solda büyük görsel, sağda başlık/açıklama/fiyat sütunu --}}
<div class="spl">
    <header class="spl-head {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <div class="spl-head-inner">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="spl-logo" />
            <h1 class="spl-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="spl-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="spl-cat">
            <h2 class="spl-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="spl-list">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="spl-card" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="spl-media"><img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy"></div>@endif
                        <div class="spl-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="spl-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="spl-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="spl-price" />
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
