{{-- Glass Hero — hi-res ürün fotoğrafı üzerinde buzlu cam bilgi katmanı --}}
<div class="ghr">
    <div class="ghr-orbs" aria-hidden="true"><span></span><span></span></div>

    <header class="ghr-head {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <div class="ghr-glass ghr-head-inner">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="ghr-logo" />
            <h1 class="ghr-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="ghr-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="ghr-cat">
            <h2 class="ghr-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="ghr-grid">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="ghr-card" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="ghr-photo" style="background-image:url('{{ $img }}')"></div>@endif
                        <div class="ghr-glass ghr-panel">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="ghr-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="ghr-desc">{{ $product->description }}</p>@endif
                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="ghr-price" />
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
