{{-- Magazine Grid — editoryal; ilk ürün manşet (tam genişlik), gerisi masonry --}}
<div class="mag">
    <header class="mag-head {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <div class="mag-head-inner">
            <span class="mag-kicker">MENÜ · {{ now()->year }}</span>
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="mag-logo" />
            <h1 class="mag-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="mag-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="mag-cat">
            <h2 class="mag-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="mag-masonry">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="mag-card {{ $loop->first ? 'is-lead' : '' }}" {!! flag_attrs($product) !!}>
                        @if ($img)<img class="mag-img" src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">@endif
                        <div class="mag-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="mag-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="mag-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="mag-price" />
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
