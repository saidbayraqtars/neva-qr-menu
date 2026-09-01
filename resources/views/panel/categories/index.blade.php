<x-app-layout title="Kategoriler">
    <x-slot name="header">Kategoriler</x-slot>
    <x-slot name="actions">
        <a href="{{ route('panel.categories.create') }}" class="btn-primary px-4">+ Kategori</a>
    </x-slot>

    <div class="mx-auto max-w-3xl">
        @if ($categories->isEmpty())
            <div class="card grid place-items-center p-16 text-center">
                <p class="font-display text-xl text-ink-900">Henüz kategori yok</p>
                <p class="mt-1 text-sm text-ink-500">Menünüzü “Kahveler”, “Tatlılar” gibi bölümlere ayırın.</p>
                <a href="{{ route('panel.categories.create') }}" class="btn-gold mt-5 px-6">İlk kategoriyi ekle</a>
            </div>
        @else
            <p class="mb-3 text-xs text-ink-400">Sıralamak için kartları sürükleyin ya da ▲▼ düğmelerini kullanın; menüde bu sırayla görünür.</p>

            <ul class="space-y-3" data-sortable data-sort-url="{{ route('panel.categories.reorder') }}">
                @foreach ($categories as $category)
                    <li class="card flex items-center gap-4 p-4" data-id="{{ $category->id }}" draggable="true">
                        <span class="flex flex-col items-center gap-0.5">
                            <button type="button" data-sort-up aria-label="Yukarı taşı"
                                    class="rounded px-1 text-ink-400 hover:bg-ink-100 hover:text-ink-700">▲</button>
                            <span class="cursor-grab select-none text-ink-300" title="Sürükleyin">⠿</span>
                            <button type="button" data-sort-down aria-label="Aşağı taşı"
                                    class="rounded px-1 text-ink-400 hover:bg-ink-100 hover:text-ink-700">▼</button>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-ink-900">
                                {{ $category->name }}
                                @unless ($category->is_active)
                                    <span class="ml-1 rounded bg-ink-100 px-1.5 py-0.5 text-[10px] font-medium text-ink-500">gizli</span>
                                @endunless
                            </p>
                            <p class="text-xs text-ink-500">{{ $category->products_count }} ürün</p>
                        </div>
                        <a href="{{ route('panel.categories.edit', $category) }}" class="btn-ghost px-3 py-1.5 text-xs">Düzenle</a>
                        <form method="POST" action="{{ route('panel.categories.destroy', $category) }}"
                              onsubmit="return confirm('“{{ $category->name }}” ve içindeki ürünler silinsin mi?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg p-2 text-ink-400 hover:bg-red-50 hover:text-red-600" aria-label="Sil">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 7h12M9 7V5h6v2m-8 0l1 12h8l1-12"/></svg>
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
