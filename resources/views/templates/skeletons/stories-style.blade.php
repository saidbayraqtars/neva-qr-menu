{{-- Stories Style — Instagram/WhatsApp hikaye kartları; tam ekran dikey görseller --}}
<div class="sty">
    <header class="sty-head {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <div class="sty-head-inner">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="sty-logo" />
            <h1 class="sty-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="sty-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="sty-cat">
            <div class="sty-cat-bar"><span class="sty-cat-name tpl-h">{{ $category->name }}</span></div>
            <div class="sty-list">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="sty-card" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="sty-media" style="background-image:url('{{ $img }}')"></div>@endif
                        <div class="sty-grad"></div>
                        <div class="sty-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <h3 class="sty-name tpl-h">{{ $product->name }}</h3>
                            @if ($showDesc && $product->description)<p class="sty-desc">{{ $product->description }}</p>@endif
                            <div class="sty-foot">
                                <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="sty-price" />
                                <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                            </div>
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
