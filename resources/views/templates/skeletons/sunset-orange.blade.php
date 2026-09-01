{{-- Sunset Orange — büyük hero banner + üstte "Öne Çıkanlar / Fırsatlar" hızlı filtre --}}
@php
    $soFeat = $categories->contains(fn ($c) => $c->products->contains('is_featured', true));
    $soDisc = $categories->contains(fn ($c) => $c->products->contains(fn ($x) => (bool) $x->discount_price));
@endphp
<div class="so" x-data="{ qf: 'all' }">
    <header class="so-hero {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <div class="so-hero-inner">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="so-logo" />
            <h1 class="so-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="so-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @if ($soFeat || $soDisc)
        <nav class="tpl-quickfilter">
            <button type="button" class="tpl-qf-btn" :class="qf === 'all' && 'is-active'" @click="qf = 'all'">Tüm Menü</button>
            @if ($soFeat)<button type="button" class="tpl-qf-btn" :class="qf === 'feat' && 'is-active'" @click="qf = 'feat'">★ Öne Çıkanlar</button>@endif
            @if ($soDisc)<button type="button" class="tpl-qf-btn" :class="qf === 'disc' && 'is-active'" @click="qf = 'disc'">% Fırsatlar</button>@endif
        </nav>
    @endif

    @forelse ($categories as $category)
        @php
            $catFeat = $category->products->contains('is_featured', true);
            $catDisc = $category->products->contains(fn ($x) => (bool) $x->discount_price);
        @endphp
        <section class="so-cat" x-show="qf === 'all' || (qf === 'feat' && {{ $catFeat ? 'true' : 'false' }}) || (qf === 'disc' && {{ $catDisc ? 'true' : 'false' }})">
            <h2 class="so-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="so-list">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="so-row" {!! flag_attrs($product) !!}
                             x-show="qf === 'all' || (qf === 'feat' && {{ $product->is_featured ? 'true' : 'false' }}) || (qf === 'disc' && {{ $product->discount_price ? 'true' : 'false' }})">
                        <div class="so-info">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="so-name">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="so-desc">{{ $product->description }}</p>@endif
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="so-price" />
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                        </div>
                        @if ($img)<img class="so-thumb" src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">@endif
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
