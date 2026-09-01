{{-- Cyber Dark — terminal estetiği, glitch hover, neon vurgu, fiyat üstte --}}
<div class="cy">
    <header class="cy-head {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <div class="cy-head-inner">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="cy-logo" />
            <h1 class="cy-title tpl-h" data-text="{{ $restaurant->name }}">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="cy-tag">&gt; {{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="cy-cat">
            <h2 class="cy-cat-name tpl-h">[ {{ $category->name }} ]</h2>
            <div class="cy-grid">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="cy-card" {!! flag_attrs($product) !!}>
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="cy-price" />
                        <h3 class="cy-name tpl-h">{{ $product->name }}</h3>
                        @if ($img)<img class="cy-img" src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">@endif
                        @if ($showDesc && $product->description)<p class="cy-desc">{{ $product->description }}</p>@endif
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
