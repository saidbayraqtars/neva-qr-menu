{{-- Carbon Mono — teknik / İsviçre grid; koyu karbon zemin, monospace, köşeli parantezli başlıklar --}}
<div class="cm">
    <header class="cm-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="cm-logo" />
        <h1 class="cm-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="cm-tag">// {{ $restaurant->tagline }}</p>@endif
    </header>

    @forelse ($categories as $i => $category)
        <section class="cm-cat">
            <h2 class="cm-cat-name tpl-h"><i>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</i>[ {{ $category->name }} ]</h2>
            @if ($category->description)<p class="cm-cat-desc">{{ $category->description }}</p>@endif
            <div class="cm-rows">
                @foreach ($category->products as $product)
                    <div class="cm-row" {!! flag_attrs($product) !!}>
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        <div class="cm-line">
                            <span class="cm-name tpl-h">{{ $product->name }}</span>
                            <span class="cm-dots"></span>
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="cm-price" />
                        </div>
                        @if ($showDesc && $product->description)<p class="cm-desc">{{ $product->description }}</p>@endif
                        <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
