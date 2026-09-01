{{-- Minimalist Kaffe — sol hizalı kompakt liste, minik yuvarlak logo, kartsız satırlar --}}
<div class="mk">
    <header class="mk-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="mk-logo" />
        <div>
            <h1 class="mk-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="mk-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="mk-cat">
            <h2 class="mk-cat-name">{{ $category->name }}</h2>
            @if ($category->description)<p class="mk-cat-desc">{{ $category->description }}</p>@endif
            <ul class="mk-list">
                @foreach ($category->products as $product)
                    <li class="mk-row" {!! flag_attrs($product) !!}>
                        <div class="mk-row-main">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <span class="mk-name">{{ $product->name }}</span>
                            @if ($showDesc && $product->description)<span class="mk-desc">{{ $product->description }}</span>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                        </div>
                        <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="mk-price" />
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
