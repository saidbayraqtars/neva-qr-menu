{{-- Modern Grid — Instagram tarzı kare fotoğraflı 2 sütun grid, fotoğraf odaklı --}}
<div class="mg">
    @if ($coverUrl)
        <div class="mg-cover" data-tpl-cover data-has-cover="1" style="background-image: url('{{ $coverUrl }}')"></div>
    @else
        <div class="mg-cover no-cover" data-tpl-cover data-has-cover=""></div>
    @endif

    <header class="mg-head">
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="mg-logo" />
        <div class="mg-head-txt">
            <h1 class="mg-title tpl-h">{{ $restaurant->name }}</h1>
            @if ($restaurant->tagline)<p class="mg-tag">{{ $restaurant->tagline }}</p>@endif
        </div>
    </header>


    @forelse ($categories as $category)
        <section class="mg-cat">
            <h2 class="mg-cat-name tpl-h">{{ $category->name }}</h2>
            <div class="mg-grid">
                @foreach ($category->products as $product)
                    @php $img = $p->productImage($product); @endphp
                    <article class="mg-card" {!! flag_attrs($product) !!}>
                        <div class="mg-photo" @if ($img) style="background-image:url('{{ $img }}')" @endif>
                            @unless ($img)<span class="mg-initial">{{ \Illuminate\Support\Str::of($product->name)->substr(0, 1)->upper() }}</span>@endunless
                        </div>
                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                        <div class="mg-cap">
                            <span class="mg-name">{{ $product->name }}</span>
                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="mg-price" />
                        </div>
                        @if ($showDesc && $product->description)<p class="mg-desc">{{ $product->description }}</p>@endif
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @endforelse

    @include('templates.partials.foot')
</div>
