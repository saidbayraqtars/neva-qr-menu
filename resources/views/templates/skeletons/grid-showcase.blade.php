{{-- Grid Showcase — temiz simetrik 2 sütun, büyük ürün görselleri --}}
<div class="gsh">
    <header class="gsh-head {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <div class="gsh-head-inner">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="gsh-logo" />
            <h1 class="gsh-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="gsh-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="gsh-cat">
            <h2 class="gsh-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="gsh-grid">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="gsh-card" {!! flag_attrs($product) !!}>
                        @if ($img)<div class="gsh-media"><img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy"></div>@endif
                        <div class="gsh-body">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <div class="gsh-line">
                                <h3 class="gsh-name tpl-h">{{ $product->name }}</h3>
                                <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="gsh-price" />
                            </div>
                            @if ($showDesc && $product->description)<p class="gsh-desc">{{ $product->description }}</p>@endif
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
