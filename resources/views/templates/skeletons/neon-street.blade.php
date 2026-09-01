{{-- Neon Street — kalın tipografi, asimetrik kartlar, köşeli, hover scale --}}
<div class="ns">
    <header class="ns-head {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image: url('{{ $coverUrl }}')" @endif>
        <div class="ns-head-inner">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="ns-logo" />
            <h1 class="ns-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="ns-tag">// {{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="ns-cat-wrap">
            <h2 class="ns-cat tpl-h">{{ $category->name }} <span>//</span></h2>
            <div class="ns-stack">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="ns-card" data-odd="{{ $loop->odd ? '1' : '0' }}" {!! flag_attrs($product) !!}>
                        @if ($img)<img class="ns-img" src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">@endif
                        <div class="ns-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <div class="ns-line">
                                <span class="ns-name tpl-h">{{ $product->name }}</span>
                                <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="ns-price" />
                            </div>
                            @if ($showDesc && $product->description)<p class="ns-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
