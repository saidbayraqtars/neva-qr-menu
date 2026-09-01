{{-- Artisan Crafted — nostaljik retro, ortalanmış başlıklar, özel border & sert gölge --}}
<div class="ac">
    <header class="ac-head">
        <span class="ac-orn">❦</span>
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="ac-logo" />
        <h1 class="ac-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="ac-tag">{{ $restaurant->tagline }}</p>@endif
        <span class="ac-orn">❦</span>
    </header>


    @forelse ($categories as $category)
        <section class="ac-cat">
            <h2 class="ac-cat-name tpl-h">✦&nbsp; {{ $category->name }} &nbsp;✦</h2>
            @if ($category->description)<p class="ac-cat-desc">{{ $category->description }}</p>@endif
            <div class="ac-items">
                @foreach ($category->products as $product)
                    <div class="ac-item" {!! flag_attrs($product) !!}>
                        <div class="ac-item-top">
                            <span class="ac-name">{{ $product->name }}</span>
                            <span class="ac-dots"></span>
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="ac-price" />
                        </div>
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        @if ($showDesc && $product->description)<p class="ac-desc">{{ $product->description }}</p>@endif
                        <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                    </div>
                @endforeach
            </div>
            <div class="ac-divider">❧</div>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
