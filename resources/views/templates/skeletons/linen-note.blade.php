{{-- Linen Note — sıcak keten kağıt, el yazısı kategori başlıkları, kesik çizgi ayraç, kafe defteri havası --}}
<div class="ln">
    <header class="ln-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="ln-logo" />
        <h1 class="ln-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="ln-tag">{{ $restaurant->tagline }}</p>@endif
    </header>

    @forelse ($categories as $category)
        <section class="ln-cat">
            <h2 class="ln-cat-name">{{ $category->name }}</h2>
            @if ($category->description)<p class="ln-cat-desc">{{ $category->description }}</p>@endif
            <ul class="ln-list">
                @foreach ($category->products as $product)
                    <li class="ln-row" {!! flag_attrs($product) !!}>
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        <div class="ln-line">
                            <span class="ln-name tpl-h">{{ $product->name }}</span>
                            <span class="ln-dots"></span>
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="ln-price" />
                        </div>
                        @if ($showDesc && $product->description)<p class="ln-desc">{{ $product->description }}</p>@endif
                        <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
