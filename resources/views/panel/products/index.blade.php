<x-app-layout title="Ürünler">
    <x-slot name="header">Ürünler</x-slot>
    <x-slot name="actions">
        <a href="{{ route('panel.products.create') }}" class="btn-primary px-4">+ Ürün</a>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-8">
        @php $total = $categories->sum(fn ($c) => $c->products->count()); @endphp

        @if ($categories->isEmpty())
            <div class="card grid place-items-center p-16 text-center">
                <p class="font-display text-xl text-ink-900">Önce bir kategori gerekli</p>
                <a href="{{ route('panel.categories.create') }}" class="btn-gold mt-4 px-6">Kategori ekle</a>
            </div>
        @elseif ($total === 0)
            <div class="card grid place-items-center p-16 text-center">
                <p class="font-display text-xl text-ink-900">Henüz ürün yok</p>
                <p class="mt-1 text-sm text-ink-500">İlk ürününüzü ekleyin, önizlemede anında görün.</p>
                <a href="{{ route('panel.products.create') }}" class="btn-gold mt-5 px-6">Ürün ekle</a>
            </div>
        @else
            @foreach ($categories as $category)
                <section>
                    <h2 class="mb-1 font-display text-lg text-ink-900">
                        {{ $category->name }}
                        <span class="text-sm font-normal text-ink-400">· {{ $category->products->count() }}</span>
                    </h2>

                    @if ($category->products->count() > 1)
                        <p class="mb-2 text-xs text-ink-400">Ürünleri sürükleyerek ya da ▲▼ ile sıralayın.</p>
                    @endif

                    @if ($category->products->isEmpty())
                        <p class="rounded-2xl border border-dashed border-ink-200 p-4 text-sm text-ink-400">Bu kategoride ürün yok.</p>
                    @else
                        <ul class="space-y-2" data-sortable
                            data-sort-url="{{ route('panel.products.reorder') }}"
                            data-sort-extra='@json(['category_id' => $category->id])'>
                            @foreach ($category->products as $product)
                                <li class="card flex items-center gap-4 p-3" data-id="{{ $product->id }}" draggable="true">
                                    <span class="flex shrink-0 flex-col items-center gap-0.5">
                                        <button type="button" data-sort-up aria-label="Yukarı taşı"
                                                class="rounded px-1 text-ink-400 hover:bg-ink-100 hover:text-ink-700">▲</button>
                                        <span class="cursor-grab select-none text-ink-300" title="Sürükleyin">⠿</span>
                                        <button type="button" data-sort-down aria-label="Aşağı taşı"
                                                class="rounded px-1 text-ink-400 hover:bg-ink-100 hover:text-ink-700">▼</button>
                                    </span>
                                    <div class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-ink-100">
                                        @if ($product->image_path)
                                            <img src="{{ media_url($product->image_path) }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-[10px] text-ink-400">görsel yok</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-semibold text-ink-900">
                                            {{ $product->name }}
                                            @if ($product->is_featured)<span class="ml-1 text-xs text-gold-600">★</span>@endif
                                            @unless ($product->is_available)<span class="ml-1 rounded bg-ink-100 px-1.5 py-0.5 text-[10px] text-ink-500">tükendi</span>@endunless
                                        </p>
                                        <p class="truncate text-xs text-ink-500">{{ $product->description ?: '—' }}</p>
                                    </div>
                                    <span class="shrink-0 font-semibold text-ink-900">{{ money($product->discount_price ?? $product->price, $restaurant->currency) }}</span>
                                    <a href="{{ route('panel.products.edit', $product) }}" class="btn-ghost px-3 py-1.5 text-xs">Düzenle</a>
                                    <form method="POST" action="{{ route('panel.products.destroy', $product) }}" onsubmit="return confirm('Ürün silinsin mi?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg p-2 text-ink-400 hover:bg-red-50 hover:text-red-600" aria-label="Sil">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 7h12M9 7V5h6v2m-8 0l1 12h8l1-12"/></svg>
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach
        @endif
    </div>
</x-app-layout>
