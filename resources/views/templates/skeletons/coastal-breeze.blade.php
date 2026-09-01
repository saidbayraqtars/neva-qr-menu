{{-- Coastal Breeze — Ege ferahlığı; sıcak beyaz, deniz mavisi vurgu, bol boşluk, dalga ayraç --}}
<div class="cbz">
    <header class="cbz-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="cbz-logo" />
        <h1 class="cbz-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="cbz-tag">{{ $restaurant->tagline }}</p>@endif
    </header>

    @forelse ($categories as $category)
        <section class="cbz-cat">
            <div class="cbz-wave" aria-hidden="true"></div>
            <h2 class="cbz-cat-name tpl-h">{{ $category->name }}</h2>
            @if ($category->description)<p class="cbz-cat-desc">{{ $category->description }}</p>@endif
            <ul class="cbz-list">
                @foreach ($category->products as $product)
                    <li class="cbz-row" {!! flag_attrs($product) !!}>
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        <div class="cbz-line">
                            <span class="cbz-name tpl-h">{{ $product->name }}</span>
                            <span class="cbz-dots"></span>
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="cbz-price" />
                        </div>
                        @if ($showDesc && $product->description)<p class="cbz-desc">{{ $product->description }}</p>@endif
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
