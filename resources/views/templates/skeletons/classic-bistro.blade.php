{{-- Classic Bistro — menü kağıdı hissi, ince çizgi çerçeve, serif, simetrik 2'li grid --}}
<div class="cb">
    <header class="cb-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="cb-logo" />
        <h1 class="cb-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="cb-tag">{{ $restaurant->tagline }}</p>@endif
        <p class="cb-menu-word">— Menü —</p>
    </header>


    <div class="cb-paper">
        @forelse ($categories as $category)
            <section class="cb-cat">
                <h2 class="cb-cat-name tpl-h">{{ $category->name }}</h2>
                <div class="cb-grid">
                    @foreach ($category->products as $product)
                        <div class="cb-item" {!! flag_attrs($product) !!}>
                            <div class="cb-item-head">
                                <span class="cb-name">{{ $product->name }}</span>
                                <span class="cb-leader"></span>
                                <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="cb-price" />
                            </div>
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            @if ($showDesc && $product->description)<p class="cb-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <p class="tpl-empty">Menü yakında burada olacak.</p>
        @endforelse
    </div>

    @include('templates.partials.foot')
</div>
