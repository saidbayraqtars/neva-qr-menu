{{-- Dark Prestige — koyu, merkezî hizalı, büyük görseller, gold çerçeve, fade-in --}}
<div class="dp">
    <header class="dp-head {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image: url('{{ $coverUrl }}')" @endif>
        <div class="dp-head-inner">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="dp-logo" />
            <h1 class="dp-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="dp-tag">{{ $restaurant->tagline }}</p>@endif
            <span class="dp-divider"></span>
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="dp-cat">
            <h2 class="dp-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="dp-list">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="dp-card" style="--i: {{ $loop->index }}" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="dp-img"><img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy"></div>@endif
                        <div class="dp-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="dp-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="dp-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="dp-price" />
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
