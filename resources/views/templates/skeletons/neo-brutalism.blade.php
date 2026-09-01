{{-- Neo Brutalism — kalın siyah çerçeveler, sert 6px gölge blokları, pop sarı --}}
<div class="nb">
    <header class="nb-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="nb-logo" />
        <h1 class="nb-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="nb-tag">{{ $restaurant->tagline }}</p>@endif
    </header>


    @forelse ($categories as $category)
        <section class="nb-cat">
            <h2 class="nb-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="nb-grid">
                @foreach ($category->products as $product)
                    <article class="nb-card" {!! flag_attrs($product) !!}>
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        <div class="nb-line">
                            <h3 class="nb-name tpl-h">{{ $product->name }}</h3>
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="nb-price" />
                        </div>
                        @if ($showDesc && $product->description)<p class="nb-desc">{{ $product->description }}</p>@endif
                        <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
