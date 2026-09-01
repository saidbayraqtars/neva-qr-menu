{{-- Botanical Green — yumuşak rounded-3xl kartlar, yatay kaydırmalı sekmeler, organik --}}
<div class="bgn" x-data="{ active: {{ $categories->first()?->id ?? 'null' }},
        go(id){ this.active = id; document.getElementById('bgn-'+id)?.scrollIntoView({behavior:'smooth',block:'start'}); } }">
    <header class="bgn-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="bgn-logo" />
        <h1 class="bgn-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="bgn-tag">{{ $restaurant->tagline }}</p>@endif
    </header>


    @if ($categories->isNotEmpty())
        <nav class="bgn-tabs">
            @foreach ($categories as $category)
                <button type="button" class="bgn-tab" :class="active === {{ $category->id }} && 'is-active'" @click="go({{ $category->id }})">{{ $category->name }}</button>
            @endforeach
        </nav>
    @endif

    @forelse ($categories as $category)
        <section id="bgn-{{ $category->id }}" class="bgn-cat" x-intersect.margin.-45%="active = {{ $category->id }}">
            <h2 class="bgn-cat-name tpl-h">{{ $category->name }}</h2>
            @if ($category->description)<p class="bgn-cat-desc">{{ $category->description }}</p>@endif
            <div class="bgn-grid">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="bgn-card" {!! flag_attrs($product) !!}>
                        @if ($img)<img class="bgn-img" src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">@endif
                        <div class="bgn-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="bgn-name">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="bgn-desc">{{ $product->description }}</p>@endif
                            <div class="bgn-foot">
                                <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="bgn-price" />
                            </div>
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
