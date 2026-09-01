{{-- Royal Blue — kurumsal; sol kategori navigasyonu + "Öne Çıkanlar / Fırsatlar" hızlı bölümleri --}}
@php
    $rbFeat = $categories->flatMap->products->filter(fn ($x) => $x->is_featured)->values();
    $rbDisc = $categories->flatMap->products->filter(fn ($x) => (bool) $x->discount_price)->values();
@endphp
<div class="rb" x-data="{ active: {{ $categories->first()?->id ?? 'null' }} }">
    <header class="rb-hero {{ $coverUrl ? '' : 'no-cover' }}" data-tpl-cover data-has-cover="{{ $coverUrl ? '1' : '' }}"
            @if ($coverUrl) style="background-image:url('{{ $coverUrl }}')" @endif>
        <x-tpl.logo :url="$logoUrl" :name="$restaurant->name" class="rb-logo" />
        <h1 class="rb-title tpl-h">{{ $restaurant->name }}</h1>
        @if ($restaurant->tagline)<p class="rb-tag">{{ $restaurant->tagline }}</p>@endif
    </header>


    @if ($categories->isEmpty())
        <p class="tpl-empty">Menü yakında burada olacak.</p>
    @else
        <div class="rb-layout">
            <nav class="rb-nav">
                @if ($rbFeat->isNotEmpty())
                    <button type="button" class="rb-nav-item rb-nav-item--pin" :class="active === 'feat' && 'is-active'" @click="active = 'feat'">
                        <span>★ Öne Çıkanlar</span><em>{{ $rbFeat->count() }}</em>
                    </button>
                @endif
                @if ($rbDisc->isNotEmpty())
                    <button type="button" class="rb-nav-item rb-nav-item--pin" :class="active === 'disc' && 'is-active'" @click="active = 'disc'">
                        <span>% Fırsatlar</span><em>{{ $rbDisc->count() }}</em>
                    </button>
                @endif
                @foreach ($categories as $category)
                    <button type="button" class="rb-nav-item" :class="active === {{ $category->id }} && 'is-active'" @click="active = {{ $category->id }}">
                        <span>{{ $category->name }}</span>
                        <em>{{ $category->products->count() }}</em>
                    </button>
                @endforeach
            </nav>

            <div class="rb-content">
                @foreach (['feat' => $rbFeat, 'disc' => $rbDisc] as $mode => $bundle)
                    @if ($bundle->isNotEmpty())
                        <section class="rb-panel" x-show="active === '{{ $mode }}'" x-transition.opacity>
                            <h2 class="rb-cat-name tpl-h">{{ $mode === 'feat' ? 'Öne Çıkan Lezzetler' : 'Bu Haftanın Fırsatları' }}</h2>
                            <div class="rb-cards">
                                @foreach ($bundle as $product)
                                    @php $img = $p->productImage($product); @endphp
                                    <article class="rb-card" {!! flag_attrs($product) !!}>
                                        @if ($img)<img class="rb-img" src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">@endif
                                        <div class="rb-body">
                                            <x-tpl.flags :product="$product" :tpl="$p->key" />
                                            <div class="rb-line">
                                                <h3 class="rb-name tpl-h">{{ $product->name }}</h3>
                                                <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="rb-price" />
                                            </div>
                                            @if ($showDesc && $product->description)<p class="rb-desc">{{ $product->description }}</p>@endif
                                            <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach

                @foreach ($categories as $category)
                    <section class="rb-panel" x-show="active === {{ $category->id }}" x-transition.opacity>
                        <h2 class="rb-cat-name tpl-h">{{ $category->name }}</h2>
                        @if ($category->description)<p class="rb-cat-desc">{{ $category->description }}</p>@endif
                        <div class="rb-cards">
                            @foreach ($category->products as $product)
                                @php $img = $p->productImage($product); @endphp
                                <article class="rb-card" {!! flag_attrs($product) !!}>
                                    @if ($img)<img class="rb-img" src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">@endif
                                    <div class="rb-body">
                                        <x-tpl.flags :product="$product" :tpl="$p->key" />
                                        <div class="rb-line">
                                            <h3 class="rb-name tpl-h">{{ $product->name }}</h3>
                                            <x-tpl.price :product="$product" :currency="$currency" :show="$showPrices" class="rb-price" />
                                        </div>
                                        @if ($showDesc && $product->description)<p class="rb-desc">{{ $product->description }}</p>@endif
                                        <x-tpl.badges :product="$product" :show-calories="$showCalories" />
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    @endif

    @include('templates.partials.foot')
</div>
