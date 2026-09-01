<x-app-layout :title="$product->exists ? 'Ürün düzenle' : 'Yeni ürün'">
    <x-slot name="header">{{ $product->exists ? 'Ürünü düzenle' : 'Yeni ürün' }}</x-slot>

    <div class="mx-auto max-w-xl">
        <form method="POST"
              action="{{ $product->exists ? route('panel.products.update', $product) : route('panel.products.store') }}"
              enctype="multipart/form-data" class="card space-y-5 p-6">
            @csrf
            @if ($product->exists) @method('PUT') @endif

            <div>
                <x-input-label :value="'Kategori'" />
                <select name="category_id" class="field" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category_id')" />
            </div>

            <div>
                <x-input-label :value="'Ürün adı'" />
                <x-text-input name="name" value="{{ old('name', $product->name) }}" required autofocus placeholder="Flat White" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label :value="'Açıklama'" />
                <textarea name="description" rows="2" class="field" placeholder="Çift shot espresso, ipeksi süt">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label :value="'Fiyat'" />
                    <x-text-input name="price" type="number" step="0.01" min="0" value="{{ old('price', $product->price) }}" required />
                    <x-input-error :messages="$errors->get('price')" />
                </div>
                <div>
                    <x-input-label :value="'İndirimli fiyat (opsiyonel)'" />
                    <x-text-input name="discount_price" type="number" step="0.01" min="0" value="{{ old('discount_price', $product->discount_price) }}" />
                    <x-input-error :messages="$errors->get('discount_price')" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label :value="'Etiketler (virgülle)'" />
                    <x-text-input name="tags" value="{{ old('tags', is_array($product->tags) ? implode(', ', $product->tags) : '') }}" placeholder="vegan, yeni" />
                </div>
                <div>
                    <x-input-label :value="'Alerjenler (virgülle)'" />
                    <x-text-input name="allergens" value="{{ old('allergens', is_array($product->allergens) ? implode(', ', $product->allergens) : '') }}" placeholder="gluten, süt" />
                </div>
            </div>

            <div>
                <x-input-label :value="'Kalori (opsiyonel)'" />
                <x-text-input name="calories" type="number" min="0" value="{{ old('calories', $product->calories) }}" class="field sm:max-w-[160px]" />
            </div>

            <div>
                <x-input-label :value="'Ürün görseli'" />
                @if ($product->image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('neva.uploads.disk'))->url($product->image_path) }}" class="mb-2 h-28 w-full rounded-xl object-cover">
                @endif
                <input type="file" name="image" accept="image/*"
                       class="block w-full text-sm text-ink-500 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white">
                <x-input-error :messages="$errors->get('image')" />
            </div>

            <div class="flex flex-wrap gap-5">
                <label class="flex items-center gap-2 text-sm text-ink-700">
                    <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $product->is_available ?? true)) class="rounded border-ink-300 text-ink-900 focus:ring-gold-500">
                    Stokta / servis ediliyor
                </label>
                <label class="flex items-center gap-2 text-sm text-ink-700">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured)) class="rounded border-ink-300 text-ink-900 focus:ring-gold-500">
                    Öne çıkar
                </label>
            </div>

            <div class="flex justify-end gap-3 border-t border-ink-100 pt-5">
                <a href="{{ route('panel.products.index') }}" class="btn-ghost">Vazgeç</a>
                <button class="btn-primary px-8">Kaydet</button>
            </div>
        </form>
    </div>
</x-app-layout>
