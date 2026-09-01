{{-- Urban Chic — metropol; zigzag asimetrik satırlar (görsel bir sağda bir solda) --}}
<div class="uc">
    <header class="uc-head {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <div class="uc-head-inner">
            <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="uc-logo" />
            <h1 class="uc-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="uc-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="uc-cat">
            <h2 class="uc-cat-name tpl-h"><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span> {{ $category->name }}</h2>
            <div class="uc-rows">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="uc-row" data-flip="{{ $loop->index % 2 }}" {!! flag_attrs($product) !!}>
                        <div class="uc-media">
                            @if ($img)
                                <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">
                            @else
                                <span class="uc-media-ph">{{ mb_substr($product->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="uc-info">
                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                            <div class="uc-line">
                                <h3 class="uc-name tpl-h">{{ $product->name }}</h3>
                                <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="uc-price" />
                            </div>
                            @if ($showDesc && $product->description)<p class="uc-desc">{{ $product->description }}</p>@endif
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
