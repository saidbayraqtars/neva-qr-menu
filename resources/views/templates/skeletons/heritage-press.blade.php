{{-- Heritage Press — gazete / letterpress hissi; krem kağıt, çift kural çizgisi, sıkışık serif --}}
<div class="hp">
    <header class="hp-masthead">
        <div class="hp-rule"></div>
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="hp-logo" />
        <h1 class="hp-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="hp-tag">— {{ $restaurant->tagline }} —</p>@endif
        <div class="hp-rule hp-rule--thin"></div>
    </header>

    @forelse ($categories as $category)
        <section class="hp-cat">
            <h2 class="hp-cat-name tpl-h"><span>{{ $category->name }}</span></h2>
            @if ($category->description)<p class="hp-cat-desc">{{ $category->description }}</p>@endif
            <ul class="hp-list">
                @foreach ($category->products as $product)
                    <li class="hp-row" {!! flag_attrs($product) !!}>
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        <div class="hp-line">
                            <span class="hp-name tpl-h">{{ $product->name }}</span>
                            <span class="hp-dots"></span>
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="hp-price" />
                        </div>
                        @if ($showDesc && $product->description)<p class="hp-desc">{{ $product->description }}</p>@endif
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
