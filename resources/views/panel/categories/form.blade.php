<x-app-layout :title="$category->exists ? 'Kategori düzenle' : 'Yeni kategori'">
    <x-slot name="header">{{ $category->exists ? 'Kategoriyi düzenle' : 'Yeni kategori' }}</x-slot>

    <div class="mx-auto max-w-xl">
        <form method="POST"
              action="{{ $category->exists ? route('panel.categories.update', $category) : route('panel.categories.store') }}"
              class="card space-y-5 p-6">
            @csrf
            @if ($category->exists) @method('PUT') @endif

            <div>
                <x-input-label :value="'Kategori adı'" />
                <x-text-input name="name" value="{{ old('name', $category->name) }}" required autofocus placeholder="Kahveler" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label :value="'Açıklama (opsiyonel)'" />
                <textarea name="description" rows="2" class="field" placeholder="Kısa bir tanıtım">{{ old('description', $category->description) }}</textarea>
            </div>

            <label class="flex items-center gap-2 text-sm text-ink-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true)) class="rounded border-ink-300 text-ink-900 focus:ring-gold-500">
                Menüde görünür
            </label>

            <div class="flex justify-end gap-3 border-t border-ink-100 pt-5">
                <a href="{{ route('panel.categories.index') }}" class="btn-ghost">Vazgeç</a>
                <button class="btn-primary px-8">Kaydet</button>
            </div>
        </form>
    </div>
</x-app-layout>
