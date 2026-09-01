<x-app-layout title="QR & PDF">
    <x-slot name="header">QR Kodları & PDF</x-slot>
    <x-slot name="actions">
        <a href="{{ route('panel.menu.pdf') }}" class="btn-primary px-4">Menüyü PDF indir</a>
    </x-slot>

    @php($selfHosted = $selfHosted ?? false)

    <div class="mx-auto max-w-3xl space-y-6">

    {{-- ============ QR TASARIMI — kaydırarak seç (10 dekoratif stil) ============ --}}
    <div class="card p-6 sm:p-8"
         x-data="{
            designs: @js(collect($qrDesigns)->map(fn ($d, $k) => [
                'k' => $k, 'label' => $d['label'], 'ink' => $d['ink'], 'accent' => $d['accent'], 'logo' => $d['logo'],
                'blurb' => [
                    'brush' => 'Elle çizilmiş fırça darbeli çerçeve.',
                    'floral' => 'Köşelerde zarif botanik motifler.',
                    'deco' => 'Art Deco köşe yelpazeleri, çift ince çerçeve.',
                    'scallop' => 'Dört kenarı dalgalı tarak deseni.',
                    'ticket' => 'Yan çentikli, kesik çizgili bilet formu.',
                    'seal' => 'Yuvarlak mühür — çevresinde kavisli yazı.',
                    'halftone' => 'Kenarlara doğru büyüyen nokta yağmuru.',
                    'bracket' => 'Klasik köşe parantezleri, serif etiket.',
                    'ribbon' => 'Üstte katlanmış şerit banner.',
                    'minimal' => 'Bol boşluk, tek ince vurgu çizgisi.',
                ][$d['frame']] ?? '',
            ])->values()),
            current: @js($restaurant->qr_design),
            i: 0,
            previewUrl: @js(route('panel.qr.design.preview')),
            init() { const x = this.designs.findIndex(d => d.k === this.current); this.i = x >= 0 ? x : 0; },
            get d() { return this.designs[this.i]; },
            slide(n) { this.i = Math.max(0, Math.min(this.designs.length - 1, this.i + n)); },
         }">
        <div class="text-center sm:text-left">
            <h3 class="font-display text-lg text-ink-900">QR tasarımı</h3>
            <p class="mt-1 text-sm text-ink-500">10 dekoratif stil — süsleme QR'ın <strong>dışında</strong> kalır, kod her zaman taranır. Seçiminiz tüm QR çıktılarınıza uygulanır.</p>
        </div>

        <div class="mt-6 grid items-center gap-8 sm:grid-cols-[auto_minmax(0,1fr)]">
            <div class="relative mx-auto w-[210px]">
                <button type="button" @click="slide(-1)" :disabled="i === 0" aria-label="Önceki tasarım"
                        class="absolute -left-4 top-1/2 z-10 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white text-ink-700 shadow-[0_10px_28px_-8px_rgba(23,23,15,.35)] ring-1 ring-ink-200 transition hover:bg-ink-50 disabled:pointer-events-none disabled:opacity-25">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <template x-for="dd in [d]" :key="dd.k">
                    <img :src="previewUrl + '?design=' + dd.k" alt="QR önizleme"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-[.96]" x-transition:enter-end="opacity-100 scale-100"
                         class="w-full rounded-2xl shadow-[0_2px_6px_rgba(23,23,15,.06),0_30px_50px_-30px_rgba(23,23,15,.35)] ring-1 ring-ink-100">
                </template>
                <button type="button" @click="slide(1)" :disabled="i === designs.length - 1" aria-label="Sonraki tasarım"
                        class="absolute -right-4 top-1/2 z-10 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white text-ink-700 shadow-[0_10px_28px_-8px_rgba(23,23,15,.35)] ring-1 ring-ink-200 transition hover:bg-ink-50 disabled:pointer-events-none disabled:opacity-25">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                </button>
            </div>

            <div class="text-center sm:text-left">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-ink-50 px-2.5 py-1 text-[11px] font-semibold text-ink-600 ring-1 ring-ink-200">
                    <span class="h-2 w-2 rounded-full" :style="`background:${d.accent}`"></span>
                    <span x-text="d.logo ? 'Merkezde logo' : 'Logosuz'"></span>
                </span>
                <p class="mt-2.5 font-display text-2xl text-ink-900" x-text="d.label"></p>
                <p class="mt-1.5 text-sm leading-relaxed text-ink-500" x-text="d.blurb"></p>

                <form method="POST" action="{{ route('panel.qr.design.update') }}" class="mt-5">
                    @csrf @method('PUT')
                    <input type="hidden" name="qr_design" :value="d.k">
                    <button class="btn-primary px-6" :class="d.k === current && 'pointer-events-none opacity-40'"
                            x-text="d.k === current ? 'Seçili tasarım ✓' : 'Bu tasarımı kullan'"></button>
                </form>
            </div>
        </div>

        <div class="mt-7 flex flex-col items-center gap-2">
            <div class="h-[3px] w-44 overflow-hidden rounded-full bg-ink-200">
                <div class="h-full rounded-full bg-ink-900 transition-all duration-300" :style="`width:${((i + 1) / designs.length) * 100}%`"></div>
            </div>
            <p class="text-xs font-medium text-ink-400"><span class="text-ink-800" x-text="i + 1"></span> / <span x-text="designs.length"></span></p>
        </div>
    </div>

    @if ($selfHosted)
        {{-- ============ HOSTING HARİÇ — DIŞ LİNK STATİK QR ============ --}}
        <div class="card p-6">
            <div class="flex items-start gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gold-500/12 text-gold-700 ring-1 ring-gold-500/25">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 13.5h3.75v3.75H13.5zM19.5 19.5h.008v.008H19.5zM13.5 19.5h.008v.008H13.5z"/></svg>
                </span>
                <div class="min-w-0">
                    <h3 class="font-display text-lg text-ink-900">Dış link için statik QR</h3>
                    <p class="mt-1 text-sm text-ink-500">
                        Paketiniz <strong>Hosting Hariç</strong>. Menünüzü kendi barındırdığınız için
                        alt domain ve masa bazlı QR bu pakette yer almaz. Menünüzün web adresini girin;
                        sistem o adrese giden <strong>bağımsız, sabit bir QR kod</strong> üretsin.
                        İndirip doğrudan masalarınıza bastırabilirsiniz.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('panel.qr.external.update') }}" class="mt-5">
                @csrf @method('PUT')
                <x-input-label :value="'Menünüzün web adresi'" />
                <div class="mt-1 flex flex-wrap gap-2">
                    <x-text-input name="external_menu_url" type="url" inputmode="url"
                                  placeholder="https://menu.isletmeniz.com"
                                  value="{{ old('external_menu_url', $restaurant->external_menu_url) }}"
                                  class="min-w-[240px] flex-1 font-mono" required />
                    <button class="btn-primary px-5">Kaydet</button>
                </div>
                <x-input-error :messages="$errors->get('external_menu_url')" class="mt-2" />
            </form>

            @if (filled($restaurant->external_menu_url))
                <div class="mt-6 flex flex-col items-center gap-4 border-t border-ink-100 pt-6 sm:flex-row sm:items-start">
                    <img src="{{ route('panel.qr.external.png') }}?v={{ md5($restaurant->external_menu_url) }}" alt="Menü QR"
                         class="h-48 w-48 shrink-0 rounded-xl bg-white object-contain ring-1 ring-ink-100">
                    <div class="min-w-0 flex-1" x-data="{ copied: false }">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">QR şu adrese gider</p>
                        <p class="mt-1 break-all font-mono text-sm text-ink-800">{{ $restaurant->external_menu_url }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('panel.qr.external.png') }}" download class="btn-primary px-4">PNG indir</a>
                            <a href="{{ $restaurant->external_menu_url }}" target="_blank" rel="noopener" class="btn-ghost">Adresi aç ↗</a>
                        </div>
                        <p class="mt-3 text-xs text-ink-400">Adresinizi değiştirdiğinizde QR kod otomatik güncellenir; eski çıktıları yenilemeyi unutmayın.</p>
                    </div>
                </div>
            @endif
        </div>
    @else

        {{-- ============ ANA İŞLETME QR ============ --}}
        <div class="card p-6">
            <h3 class="font-display text-lg text-ink-900">Ana İşletme QR</h3>
            <p class="mt-1 text-sm text-ink-500">
                Tek bir QR — doğrudan menünüzün adresine gider. Bu QR'ı çıktı alıp <strong>tüm masalara</strong> koyabilirsiniz.
                Masayı otomatik göstermek isterseniz aşağıdaki hızlı linkleri kullanın.
            </p>

            @if ($restaurant->isLive())
                <div class="mt-5 flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                    <img src="{{ route('panel.qr.main') }}" alt="Ana QR"
                         class="h-48 w-48 shrink-0 rounded-xl bg-white object-contain ring-1 ring-ink-100">
                    <div class="min-w-0 flex-1" x-data="{ copied: false }">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Menü adresi</p>
                        <p class="mt-1 break-all font-mono text-sm text-ink-800">{{ $mainUrl }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('panel.qr.main') }}" download class="btn-primary px-4">PNG indir</a>
                            <button type="button" class="btn-ghost"
                                    @click="navigator.clipboard.writeText('{{ $mainUrl }}'); copied = true; setTimeout(() => copied = false, 1500)">
                                <span x-text="copied ? 'Kopyalandı ✓' : 'Adresi kopyala'"></span>
                            </button>
                            <a href="{{ $mainUrl }}" target="_blank" class="btn-ghost">Menüyü aç ↗</a>
                        </div>
                    </div>
                </div>
            @else
                <p class="mt-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700 ring-1 ring-amber-200">
                    Ana QR, alt domaininiz onaylandıktan sonra otomatik üretilir ve burada görünür.
                </p>
            @endif
        </div>

        {{-- ============ HIZLI MASA QR / LİNKLERİ ============ --}}
        <div class="card p-6" x-data="{
                count: {{ max(1, $tables->count() ?: 8) }},
                base: @js(rtrim($mainUrl, '/')),
                pngBase: @js(route('panel.qr.param')),
                copied: null,
                link(n) { return this.base + '/?masa=' + n; },
                copy(n) { navigator.clipboard.writeText(this.link(n)); this.copied = n; setTimeout(() => this.copied = null, 1400); },
             }">
            <h3 class="font-display text-lg text-ink-900">Hızlı masa QR / linkleri</h3>
            <p class="mt-1 text-sm text-ink-500">
                İsterseniz her masa için hazır <code class="rounded bg-ink-100 px-1 text-xs">?masa=1</code> linkini kopyalayın ya da o masaya özel QR'ı indirin.
                Müşteri taradığında menüde sağ üstte <em>“Masa 1”</em> rozeti görünür.
            </p>

            @if ($restaurant->isLive())
                <div class="mt-4">
                    <x-input-label :value="'Kaç masa?'" />
                    <input type="number" min="1" max="200" x-model.number="count" class="field w-24">
                </div>

                <div class="mt-4 max-h-80 space-y-2 overflow-y-auto pr-1">
                    <template x-for="n in Array.from({ length: Math.max(1, Math.min(200, count || 1)) }, (_, i) => i + 1)" :key="n">
                        <div class="flex items-center gap-2 rounded-xl bg-ink-50 px-3 py-2 text-sm ring-1 ring-ink-100">
                            <span class="w-16 shrink-0 font-semibold text-ink-700" x-text="'Masa ' + n"></span>
                            <span class="min-w-0 flex-1 truncate font-mono text-xs text-ink-500" x-text="link(n)"></span>
                            <button type="button" class="shrink-0 rounded-lg px-2.5 py-1 text-xs font-semibold text-ink-600 hover:bg-ink-200"
                                    @click="copy(n)">
                                <span x-text="copied === n ? '✓' : 'Kopyala'"></span>
                            </button>
                            <a :href="pngBase + '?masa=' + n" download
                               class="shrink-0 rounded-lg px-2.5 py-1 text-xs font-semibold text-ink-600 hover:bg-ink-200">QR</a>
                        </div>
                    </template>
                </div>
            @else
                <p class="mt-4 rounded-xl bg-ink-50 px-4 py-3 text-xs text-ink-500 ring-1 ring-ink-100">
                    Alt domaininiz onaylandıktan sonra masa linkleri burada oluşur.
                </p>
            @endif
        </div>

        {{-- ============ ADLANDIRILMIŞ MASALAR (opsiyonel — tarama takibi) ============ --}}
        <div class="card p-6">
            <h3 class="font-display text-lg text-ink-900">Adlandırılmış masalar</h3>
            <p class="mt-1 text-sm text-ink-500">“Teras 4”, “Bahçe 2” gibi özel adlar + kaç kez tarandığı bilgisi. (Opsiyonel)</p>

            <form method="POST" action="{{ route('panel.qr.store') }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                <div class="min-w-[160px] flex-1">
                    <x-input-label :value="'Etiket'" />
                    <x-text-input name="label" value="Masa" required />
                </div>
                <div class="w-20">
                    <x-input-label :value="'Adet'" />
                    <x-text-input name="count" type="number" min="1" max="100" value="1" />
                </div>
                <button class="btn-ghost">Ekle</button>
            </form>
            <x-input-error :messages="$errors->get('label')" class="mt-2" />

            @if ($tables->isNotEmpty())
                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($tables as $table)
                        <div class="flex flex-col items-center rounded-xl bg-ink-50 p-3 text-center ring-1 ring-ink-100">
                            <img src="{{ route('panel.qr.png', $table) }}" alt="{{ $table->label }} QR"
                                 class="h-28 w-28 rounded-lg bg-white object-contain ring-1 ring-ink-100" loading="lazy">
                            <p class="mt-2 text-sm font-semibold text-ink-900">{{ $table->label }}</p>
                            <p class="text-xs text-ink-400">{{ $table->scan_count }} tarama</p>
                            <div class="mt-2 flex gap-1.5">
                                <a href="{{ route('panel.qr.png', $table) }}" download class="rounded-lg px-2 py-1 text-xs font-semibold text-ink-600 hover:bg-ink-200">QR</a>
                                <form method="POST" action="{{ route('panel.qr.destroy', $table) }}" onsubmit="return confirm('Masa silinsin mi?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg px-2 py-1 text-xs text-ink-400 hover:bg-red-50 hover:text-red-600">Sil</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
    </div>
</x-app-layout>
