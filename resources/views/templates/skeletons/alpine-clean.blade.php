{{-- Alpine Clean — İskandinav minimalizm; devasa boşluk, ince gri hatlar, hairline ayraç --}}
<div class="al">
    <header class="al-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="al-logo" />
        <h1 class="al-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="al-tag">{{ $restaurant->tagline }}</p>@endif
    </header>


    @forelse ($categories as $category)
        <section class="al-cat">
            <h2 class="al-cat-name tpl-h">{{ $category->name }}</h2>
            @if ($category->description)<p class="al-cat-desc">{{ $category->description }}</p>@endif
            <ul class="al-list">
                @foreach ($category->products as $product)
                    <li class="al-row" {!! flag_attrs($product) !!}>
                        <div class="al-row-main">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <span class="al-name">{{ $product->name }}</span>
                            @if ($showDesc && $product->description)<span class="al-desc">{{ $product->description }}</span>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                        </div>
                        <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="al-price" />
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
