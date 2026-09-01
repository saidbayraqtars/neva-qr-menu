<x-app-layout title="İşletme Profili">
    <x-slot name="header">İşletme Profili</x-slot>

    <div class="mx-auto max-w-2xl">
        <p class="mb-6 text-sm text-ink-500">
            Bu bilgiler markanızın <strong>merkezî kimliğidir</strong> — hangi şablonu seçerseniz seçin
            menünün başlık, logo ve alt bilgi alanlarına otomatik işlenir. Tek yerden düzenlersiniz.
        </p>

        <form method="POST" action="{{ route('panel.business.update') }}" enctype="multipart/form-data"
              class="space-y-6"
              x-data="{
                  logoPreview: @js($restaurant->logo_path ? \Illuminate\Support\Facades\Storage::disk(config('neva.uploads.disk'))->url($restaurant->logo_path) : null),
                  logoRemoved: false,
                  pickLogo(e) {
                      const f = e.target.files?.[0];
                      if (!f) return;
                      this.logoRemoved = false;
                      const r = new FileReader();
                      r.onload = ev => this.logoPreview = ev.target.result;
                      r.readAsDataURL(f);
                  },
                  dropLogo() {
                      this.logoPreview = null;
                      this.logoRemoved = true;
                      this.$refs.logoInput.value = '';
                  }
              }">
            @csrf @method('PUT')
            <input type="hidden" name="remove_logo" :value="logoRemoved ? 1 : 0">

            {{-- Kimlik --}}
            <div class="card space-y-5 p-6">
                <h3 class="font-display text-lg text-ink-900">Kimlik</h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label :value="'İşletme adı'" />
                        <x-text-input name="name" value="{{ old('name', $restaurant->name) }}" required placeholder="Lumina Bistro & Lounge" />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label :value="'Slogan'" />
                        <x-text-input name="tagline" value="{{ old('tagline', $restaurant->tagline) }}" placeholder="Üçüncü nesil kahve" />
                        <x-input-error :messages="$errors->get('tagline')" />
                    </div>
                </div>
            </div>

            {{-- Logo --}}
            <div class="card space-y-4 p-6">
                <h3 class="font-display text-lg text-ink-900">Logo</h3>
                <div class="flex items-center gap-5">
                    <span class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-2xl bg-ink-50 ring-1 ring-ink-100">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" alt="Logo önizleme" class="h-full w-full object-contain p-2">
                        </template>
                        <template x-if="!logoPreview">
                            <svg class="h-7 w-7 text-ink-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 6h16v12H4z"/></svg>
                        </template>
                    </span>
                    <div class="min-w-0 flex-1">
                        <input type="file" name="logo" accept="image/*" x-ref="logoInput" @change="pickLogo($event)"
                               class="block w-full text-sm text-ink-500 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white">
                        <button type="button" x-show="logoPreview" @click="dropLogo()" class="mt-1.5 text-xs font-medium text-ink-400 hover:text-red-600">Logoyu kaldır</button>
                        <p class="mt-1 text-xs text-ink-400">PNG / SVG önerilir. Ürün fotoğrafı olmayan kartlarda otomatik görsel olarak da kullanılır.</p>
                        <x-input-error :messages="$errors->get('logo')" />
                    </div>
                </div>
            </div>

            {{-- İletişim & sosyal --}}
            <div class="card space-y-4 p-6">
                <h3 class="font-display text-lg text-ink-900">İletişim & sosyal medya</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label :value="'Instagram kullanıcı adı'" />
                        <x-text-input name="instagram" value="{{ old('instagram', $restaurant->instagram) }}" placeholder="luminabistro" />
                        <x-input-error :messages="$errors->get('instagram')" />
                    </div>
                    <div>
                        <x-input-label :value="'WhatsApp numarası'" />
                        <x-text-input name="whatsapp" value="{{ old('whatsapp', $restaurant->whatsapp) }}" placeholder="905xxxxxxxxx" />
                        <x-input-error :messages="$errors->get('whatsapp')" />
                    </div>
                    <div>
                        <x-input-label :value="'Telefon'" />
                        <x-text-input name="phone" value="{{ old('phone', $restaurant->phone) }}" placeholder="0212 000 00 00" />
                        <x-input-error :messages="$errors->get('phone')" />
                    </div>
                    <div>
                        <x-input-label :value="'Web sitesi'" />
                        <x-text-input name="website" value="{{ old('website', $restaurant->website) }}" placeholder="luminabistro.com" />
                        <x-input-error :messages="$errors->get('website')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label :value="'Adres'" />
                        <x-text-input name="address" value="{{ old('address', $restaurant->address) }}" placeholder="Kadıköy, İstanbul" />
                        <x-input-error :messages="$errors->get('address')" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('panel.dashboard') }}" class="btn-ghost">Vazgeç</a>
                <button class="btn-primary px-8">Kaydet</button>
            </div>
        </form>
    </div>
</x-app-layout>
