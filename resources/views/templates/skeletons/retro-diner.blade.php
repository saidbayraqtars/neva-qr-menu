{{-- Retro Diner — 1950'ler lokanta; çizgili zemin, kalın kırmızı-lacivert, dolgu başlık şeridi --}}
<div class="rd">
    <header class="rd-head">
        <div class="rd-marquee">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="rd-logo" />
            <h1 class="rd-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="rd-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
        <span class="rd-checker" aria-hidden="true"></span>
    </header>


    @forelse ($categories as $category)
        <section class="rd-cat">
            <h2 class="rd-cat-name tpl-h">{{ $category->name }}</h2>
            <ul class="rd-list">
                @foreach ($category->products as $product)
                    <li class="rd-row" {!! flag_attrs($product) !!}>
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        <div class="rd-row-top">
                            <span class="rd-name">{{ $product->name }}</span>
                            <span class="rd-leader"></span>
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="rd-price" />
                        </div>
                        @if ($showDesc && $product->description)<p class="rd-desc">{{ $product->description }}</p>@endif
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
