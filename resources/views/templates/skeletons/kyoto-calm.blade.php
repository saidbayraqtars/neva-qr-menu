{{-- Kyoto Calm — Japon minimalizmi; sıcak kağıt, tek saç teli çizgi, dikey ritim, indigo vurgu, bol boşluk --}}
<div class="ky">
    <header class="ky-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="ky-logo" />
        <h1 class="ky-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="ky-tag">{{ $restaurant->tagline }}</p>@endif
        <span class="ky-seal" aria-hidden="true"></span>
    </header>

    @forelse ($categories as $category)
        <section class="ky-cat">
            <h2 class="ky-cat-name tpl-h">{{ $category->name }}</h2>
            @if ($category->description)<p class="ky-cat-desc">{{ $category->description }}</p>@endif
            <ul class="ky-list">
                @foreach ($category->products as $product)
                    <li class="ky-row" {!! flag_attrs($product) !!}>
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        <div class="ky-line">
                            <span class="ky-name tpl-h">{{ $product->name }}</span>
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="ky-price" />
                        </div>
                        @if ($showDesc && $product->description)<p class="ky-desc">{{ $product->description }}</p>@endif
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
