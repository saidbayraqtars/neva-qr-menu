{{-- Onyx Lux — sinematik tam-genişlik fotoğraflar; siyah oniks zemin, şampanya vurgu, üstüne binen serif başlıklar --}}
<div class="ox">
    <header class="ox-head {{ $coverUrl ? 'has-cover' : '' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="ox-logo" />
        <h1 class="ox-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="ox-tag">{{ $restaurant->tagline }}</p>@endif
    </header>

    @forelse ($categories as $category)
        <section class="ox-cat">
            <h2 class="ox-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="ox-list">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="ox-item {{ $img ? 'has-media' : '' }}" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="ox-media"><img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy"></div>@endif
                        <div class="ox-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <div class="ox-line">
                                <h3 class="ox-name tpl-h">{{ $product->name }}</h3>
                                <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="ox-price" />
                            </div>
                            @if ($showDesc && $product->description)<p class="ox-desc">{{ $product->description }}</p>@endif
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
