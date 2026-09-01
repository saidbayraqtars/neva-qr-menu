<x-app-layout title="Tasarım & Şablon">
    <x-slot name="header">Tasarım & Şablon</x-slot>

    @php
        $allControls = array_keys(config('neva.variations'));
        $active = $restaurant->template_settings[$restaurant->template] ?? [];
        $tplCfg = config("neva.templates.{$restaurant->template}") ?? config('neva.templates.'.\App\Models\Restaurant::DEFAULT_TEMPLATE);
        $val = fn ($k, $fallback = null) => $active[$k] ?? $fallback;
    @endphp

    <div x-data="designStudio({
            previewUrl: '{{ route('panel.preview') }}',
            template: @js($restaurant->template),
            startMode: @js($startMode),
            templates: @js(array_keys($templates)),
            meta: @js(collect($templates)->map(fn ($t) => [
                'label' => $t['label'], 'description' => $t['description'],
                'layout' => $t['layout'],
                'font' => $t['font_display'], 'mood' => $t['mood'] === 'dark' ? 'Koyu tema' : 'Açık tema',
                'accent' => $t['palette']['accent'], 'bg' => $t['tokens']['bg'],
                'headingC' => $t['palette']['heading'], 'textC' => $t['tokens']['ink'],
                'cover' => $t['cover'] ?? false,
                'focus' => $t['focus'],
                'focusLabel' => $t['focus'] === 'visual' ? 'Görselli' : 'Tipografik',
                'anim' => ([
                    'none' => null, 'scale' => 'Hover büyüme', 'glow' => 'Işıma efekti', 'fade' => 'Fade-in',
                    'pop' => 'Pop-in', 'slide' => 'Slide-up',
                ][$t['animation'] ?? 'none'] ?? null),
            ])),
            templateAccents: @js(collect($templates)->map(fn ($t) => $t['palette']['accent'])),
            templateFonts: @js(collect($templates)->map(fn ($t) => $t['font_display'])),
            templateRadii: @js(collect($templates)->map(fn ($t) => $t['tokens']['radius'])),
            templateSettings: @js($restaurant->template_settings ?: (object) []),
            templateSupports: @js(collect($templates)->map(fn ($t) => collect($allControls)->mapWithKeys(fn ($c) => [$c => ! in_array($c, $t['locks'] ?? [], true)])->all())),
            variationDefaults: @js(collect($variations)->map(fn ($v) => $v['default'] ?? null)),
            radiusMap: @js(config('neva.radius_map')),
            logoScales: @js(collect($logoSizes)->map(fn ($s) => $s['scale'])),
            currentFont: @js($val('font_family', $tplCfg['font_display'])),
            currentAccent: @js($val('accent_color', $tplCfg['palette']['accent'])),
            colors: @js(['bg' => $val('bg_color'), 'heading' => $val('heading_color'), 'text' => $val('text_color')]),
            fonts: @js(collect($fonts)->map(fn ($f, $name) => ['name' => $name, 'type' => $f['type'], 'stack' => $f['stack']])->values()),
         })">

        {{-- ================= GALERİ MODU — yatay tek-kart carousel ================= --}}
        <div x-show="mode === 'gallery'" x-cloak class="mx-auto max-w-4xl"
             @keydown.window.arrow-left="mode === 'gallery' && slide(-1)"
             @keydown.window.arrow-right="mode === 'gallery' && slide(1)">
            <div class="text-center">
                <h2 class="font-display text-2xl text-ink-900">Bir şablon seçin</h2>
                <p class="mt-1.5 text-sm text-ink-500">{{ count($templates) }} farklı mimari — yan oklar ya da ← → tuşlarıyla tek tek gezinin. Her önizleme şablonu <em>kendi orijinal paletiyle</em> gösterir.</p>
            </div>

            {{-- Filtre --}}
            <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                <template x-for="f in [{ k: 'all', l: 'Tümü' }, { k: 'visual', l: 'Görselli' }, { k: 'typographic', l: 'Görselsiz · Tipografik' }]" :key="f.k">
                    <button type="button" @click="setFocus(f.k)"
                            class="rounded-full px-4 py-2 text-sm font-semibold transition"
                            :class="galleryFocus === f.k ? 'bg-ink-900 text-white shadow-sm' : 'bg-white text-ink-600 ring-1 ring-ink-200 hover:bg-ink-100'">
                        <span x-text="f.l"></span>
                        <span class="ml-1.5 text-xs opacity-60" x-text="focusCounts[f.k]"></span>
                    </button>
                </template>
            </div>

            {{-- Split vitrin: solda telefon + floating oklar, sağda bilgi bloğu --}}
            <template x-for="key in [currentKey]" :key="key">
                <div class="mt-10 grid items-center gap-10 lg:grid-cols-[auto_minmax(0,1fr)] lg:gap-16"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0">

                    {{-- Sol: telefon mockup --}}
                    <div class="relative mx-auto w-[272px]">
                        <button type="button" @click="slide(-1)" :disabled="galleryIndex === 0" aria-label="Önceki şablon"
                                class="absolute -left-5 top-1/2 z-20 grid h-12 w-12 -translate-y-1/2 place-items-center rounded-full bg-white text-ink-700 shadow-[0_10px_30px_-8px_rgba(23,23,15,.35)] ring-1 ring-ink-200 transition hover:bg-ink-50 hover:text-ink-900 disabled:pointer-events-none disabled:opacity-25 sm:-left-7">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                        </button>

                        <div class="relative rounded-[2.6rem] bg-gradient-to-b from-ink-900 to-ink-950 p-2.5 shadow-[0_2px_10px_rgba(0,0,0,.12),0_50px_90px_-42px_rgba(23,23,15,.55)]">
                            <span class="absolute left-1/2 top-3.5 z-10 h-1.5 w-14 -translate-x-1/2 rounded-full bg-white/12"></span>
                            <div class="overflow-hidden rounded-[2.1rem] bg-white" style="height:520px">
                                <iframe :src="miniUrl(key)" title="" loading="lazy" tabindex="-1"
                                        class="pointer-events-none border-0"
                                        style="width:640px;height:1300px;transform:scale(.4);transform-origin:top left"></iframe>
                            </div>
                            <span x-show="key === selected" class="absolute right-3 top-3 z-10 rounded-full bg-gold-500 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-ink-950">Aktif</span>
                        </div>

                        <button type="button" @click="slide(1)" :disabled="galleryIndex === filteredKeys.length - 1" aria-label="Sonraki şablon"
                                class="absolute -right-5 top-1/2 z-20 grid h-12 w-12 -translate-y-1/2 place-items-center rounded-full bg-white text-ink-700 shadow-[0_10px_30px_-8px_rgba(23,23,15,.35)] ring-1 ring-ink-200 transition hover:bg-ink-50 hover:text-ink-900 disabled:pointer-events-none disabled:opacity-25 sm:-right-7">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                        </button>
                    </div>

                    {{-- Sağ: bilgi bloğu --}}
                    <div class="max-w-lg text-center lg:text-left">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide"
                              :class="meta[key].focus === 'visual' ? 'bg-indigo-50 text-indigo-600' : 'bg-ink-100 text-ink-500'"
                              x-text="meta[key].focusLabel"></span>
                        <h3 class="mt-3 font-display text-3xl leading-tight text-ink-900 sm:text-4xl" x-text="meta[key].label"></h3>
                        <p class="mt-3 text-base leading-relaxed text-ink-600" x-text="meta[key].description"></p>
                        <div class="mt-5 flex flex-wrap justify-center gap-2 lg:justify-start">
                            <template x-for="chip in [meta[key].layout, meta[key].mood, meta[key].font].filter(Boolean)" :key="chip">
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-ink-600 ring-1 ring-ink-200" x-text="chip"></span>
                            </template>
                            <span x-show="meta[key].cover" class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">Kapak görseli ✓</span>
                        </div>
                        <button type="button" @click="chooseTemplate(key)"
                                class="mt-7 inline-flex rounded-xl px-7 py-3 text-sm font-semibold transition"
                                :class="key === selected ? 'bg-ink-100 text-ink-700 hover:bg-ink-200' : 'bg-ink-900 text-white hover:bg-ink-800'"
                                x-text="key === selected ? 'Bu şablonu düzenle' : 'Bu şablonu seç ve düzenle'"></button>
                    </div>
                </div>
            </template>

            {{-- Alt: sayaç + ince ilerleme çizgisi --}}
            <div class="mt-12 flex flex-col items-center gap-2.5">
                <div class="h-[3px] w-52 overflow-hidden rounded-full bg-ink-200">
                    <div class="h-full rounded-full bg-ink-900 transition-all duration-300"
                         :style="`width:${((galleryIndex + 1) / Math.max(1, filteredKeys.length)) * 100}%`"></div>
                </div>
                <p class="text-xs font-medium text-ink-400">
                    <span class="text-ink-800" x-text="galleryIndex + 1"></span> / <span x-text="filteredKeys.length"></span>
                </p>
            </div>
        </div>

        {{-- ================= DÜZENLEME MODU ================= --}}
        <div x-show="mode === 'edit'" x-cloak
             class="mx-auto grid max-w-[1600px] gap-8 xl:grid-cols-[minmax(0,1fr)_minmax(440px,50%)]">

            <form method="POST" action="{{ route('panel.design.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf @method('PUT')
                <input type="hidden" name="template" :value="selected">

                <button type="button" @click="backToGallery"
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-500 hover:text-ink-900">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
                    Şablon galerisine dön
                </button>

                <div class="card flex items-center gap-4 p-5">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl" :style="`background:${meta[selected].bg}`">
                        <span class="h-5 w-5 rounded-md" :style="`background:${meta[selected].accent}`"></span>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-ink-900" x-text="meta[selected].label"></p>
                        <p class="truncate text-xs text-ink-500" x-text="meta[selected].description"></p>
                    </div>
                </div>

                {{-- İşletme bilgisi yönlendirmesi --}}
                <div class="flex items-start gap-3 rounded-2xl bg-ink-50 p-4 text-sm ring-1 ring-ink-100">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-ink-600">
                        İşletme adı, slogan, logo ve iletişim bilgileri artık tek yerde:
                        <a href="{{ route('panel.business.edit') }}" class="font-semibold text-ink-900 underline decoration-gold-500 underline-offset-2 hover:text-gold-700">İşletme Profili</a>.
                        Burada yalnızca menünün <strong>görünümünü</strong> ayarlarsınız.
                    </p>
                </div>

                {{-- Logo boyutu & kapak --}}
                <div class="card space-y-5 p-6">
                    <h3 class="font-display text-lg text-ink-900">Logo & kapak</h3>
                    <div>
                        <x-input-label :value="'Logo boyutu'" />
                        <select name="logo_size" class="field sm:max-w-[220px]" @change="onField($event)">
                            @foreach ($logoSizes as $k => $s)
                                <option value="{{ $k }}" @selected(($restaurant->logo_size ?: 'medium') === $k)>{{ $s['label'] }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-ink-400">Menü başlığındaki logonun ölçeği. Logonun kendisi İşletme Profili'nden yüklenir.</p>
                    </div>

                    {{-- Kapak görseli: yalnızca destekleyen şablonlarda görünür --}}
                    <div x-show="meta[selected].cover" x-cloak>
                        <x-input-label :value="'Kapak görseli (hero alanı)'" />
                        <input type="file" name="cover" accept="image/*" @change="onImage($event, 'cover')"
                               class="block w-full text-sm text-ink-500 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white">
                        <button type="button" @click="removeAsset('cover')" class="mt-1.5 text-xs font-medium text-ink-400 hover:text-red-600">Kapağı kaldır</button>
                        <x-input-error :messages="$errors->get('cover')" />
                    </div>
                    <p x-show="!meta[selected].cover" x-cloak class="rounded-xl bg-ink-50 px-3.5 py-2.5 text-xs text-ink-500 ring-1 ring-ink-100">
                        Bu şablon kapak görseli kullanmaz — tipografi ve düzen ön planda.
                    </p>
                </div>

                {{-- Renk & yazı tipi --}}
                <div class="card p-6">
                    <h3 class="font-display text-lg text-ink-900">Renk & yazı tipi</h3>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label :value="'Vurgu rengi — fiyatlar, sekmeler, rozetler'" />
                            <label class="flex w-full items-center gap-3 rounded-xl bg-white px-3 py-2 ring-1 ring-inset ring-ink-200">
                                <input type="color" name="accent_color" :value="accentHex"
                                       class="h-9 w-9 shrink-0 cursor-pointer rounded-lg border-0 bg-transparent p-0" @input="onField($event)">
                                <span class="font-mono text-sm uppercase text-ink-600" x-text="accentHex"></span>
                            </label>
                            <x-input-error :messages="$errors->get('accent_color')" />
                        </div>
                        <div>
                            <x-input-label :value="'Para birimi'" />
                            <select name="currency" class="field" @change="onField($event)">
                                @foreach (['TRY' => '₺ Türk Lirası', 'USD' => '$ Dolar', 'EUR' => '€ Euro', 'GBP' => '£ Sterlin'] as $code => $label)
                                    <option value="{{ $code }}" @selected($restaurant->currency === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="relative" @keydown.escape="fontOpen = false">
                            <x-input-label :value="'Yazı tipi — 50 Google Font'" />
                            <input type="hidden" name="font_family" :value="fontName">
                            <button type="button" @click="fontOpen = !fontOpen"
                                    class="field flex w-full items-center justify-between text-left">
                                <span class="truncate" x-text="fontName" :style="`font-family:${fontStack(fontName)}`"></span>
                                <svg class="h-4 w-4 shrink-0 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 9l6 6 6-6"/></svg>
                            </button>

                            <div x-show="fontOpen" x-cloak x-transition.opacity.duration.150ms @click.outside="fontOpen = false"
                                 class="absolute left-0 right-0 z-30 mt-1 overflow-hidden rounded-xl bg-white shadow-luxe ring-1 ring-ink-200">
                                <div class="border-b border-ink-100 p-2">
                                    <input type="text" x-model="fontQuery" placeholder="Font ara…" @keydown.stop
                                           class="w-full rounded-lg bg-ink-50 px-3 py-2 text-sm text-ink-900 outline-none ring-1 ring-inset ring-ink-200 focus:ring-2 focus:ring-gold-500">
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <template x-for="t in ['all','sans','serif','display','script']" :key="t">
                                            <button type="button" @click="fontType = t"
                                                    :class="fontType === t ? 'bg-ink-900 text-white' : 'bg-ink-100 text-ink-500 hover:bg-ink-200'"
                                                    class="rounded-full px-2.5 py-1 text-xs font-medium transition" x-text="fontTypeLabels[t]"></button>
                                        </template>
                                    </div>
                                </div>
                                <ul class="max-h-64 overflow-y-auto py-1">
                                    <template x-for="f in filteredFonts" :key="f.name">
                                        <li>
                                            <button type="button" @click="pickFont(f.name)"
                                                    :class="f.name === fontName && 'bg-gold-500/10'"
                                                    class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left hover:bg-ink-50">
                                                <span class="truncate text-[15px] text-ink-900" x-text="f.name" :style="`font-family:${f.stack}`"></span>
                                                <span class="shrink-0 text-[10px] uppercase tracking-wide text-ink-400" x-text="fontTypeLabels[f.type]"></span>
                                            </button>
                                        </li>
                                    </template>
                                    <li x-show="filteredFonts.length === 0" class="px-3 py-6 text-center text-xs text-ink-400">Sonuç bulunamadı</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-5">
                        <label class="flex items-center gap-2 text-sm text-ink-700">
                            <input type="checkbox" name="show_prices" value="1" @checked($restaurant->show_prices) class="rounded border-ink-300 text-ink-900 focus:ring-gold-500" @change="onField($event); reloadDebounced()">
                            Fiyatları göster
                        </label>
                        <label class="flex items-center gap-2 text-sm text-ink-700">
                            <input type="checkbox" name="show_calories" value="1" @checked($restaurant->show_calories) class="rounded border-ink-300 text-ink-900 focus:ring-gold-500" @change="reloadDebounced()">
                            Kalori bilgisini göster
                        </label>
                    </div>
                </div>

                {{-- Gelişmiş Dokunuşlar (accordion — şablona göre dinamik) --}}
                <div class="card overflow-hidden" x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                            class="flex w-full items-center justify-between gap-3 p-6 text-left">
                        <span>
                            <span class="block font-display text-lg text-ink-900">Gelişmiş Dokunuşlar</span>
                            <span class="mt-0.5 block text-xs text-ink-500">Arka plan / başlık / metin renkleri, arka plan dokusu, 16 giriş animasyonu, görsel stili, köşe yuvarlaklığı, başlık kalınlığı, yazı boyutu. Yalnızca bu şablonun iskeletinde çalışan ayarlar görünür.</span>
                        </span>
                        <svg class="h-5 w-5 shrink-0 text-ink-400 transition-transform" :class="open && 'rotate-180'"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 9l6 6 6-6"/></svg>
                    </button>

                    <div x-show="open" x-collapse x-cloak class="border-t border-ink-100 p-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            {{-- Özel renkler: arka plan / başlık / gövde metni --}}
                            @foreach (['bg' => 'Arka plan rengi', 'heading' => 'Başlık rengi', 'text' => 'Gövde metni rengi'] as $cName => $cLabel)
                                <div x-show="supports('{{ $cName }}_color')" x-cloak>
                                    <x-input-label :value="$cLabel" />
                                    <input type="hidden" name="{{ $cName }}_color" :value="(colorOn['{{ $cName }}'] && colorHex['{{ $cName }}']) ? colorHex['{{ $cName }}'] : ''">
                                    <label class="flex items-center gap-2 text-sm text-ink-700">
                                        <input type="checkbox" x-model="colorOn['{{ $cName }}']" @change="toggleColor('{{ $cName }}')"
                                               class="rounded border-ink-300 text-ink-900 focus:ring-gold-500">
                                        Özel renk kullan
                                    </label>
                                    <label class="mt-2 flex w-full items-center gap-3 rounded-xl bg-white px-3 py-2 ring-1 ring-inset ring-ink-200 transition"
                                           :class="!colorOn['{{ $cName }}'] && 'opacity-40 pointer-events-none'">
                                        <input type="color" :value="colorValue('{{ $cName }}')" @input="onColor('{{ $cName }}', $event)"
                                               class="h-9 w-9 shrink-0 cursor-pointer rounded-lg border-0 bg-transparent p-0">
                                        <span class="font-mono text-sm uppercase text-ink-600" x-text="colorOn['{{ $cName }}'] ? colorValue('{{ $cName }}') : 'Şablon rengi'"></span>
                                    </label>
                                    <x-input-error :messages="$errors->get($cName.'_color')" />
                                </div>
                            @endforeach

                            {{-- Seçim tabanlı varyasyon kontrolleri --}}
                            @foreach ($variations as $key => $variation)
                                @continue(($variation['type'] ?? null) === 'color')
                                <div x-show="supports('{{ $key }}')" x-cloak>
                                    <x-input-label :value="$variation['label']" />
                                    <select name="{{ $key }}" class="field" @change="onField($event)">
                                        @foreach ($variation['options'] as $optValue => $optLabel)
                                            <option value="{{ $optValue }}"
                                                @selected(($val($key) ?? $variation['default']) === $optValue)>{{ $optLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                        <p x-show="!anySupported()" x-cloak class="text-sm text-ink-500">
                            Bu şablonun iskeleti sabit bir tasarım diline sahip — gelişmiş dokunuşlar için başka bir şablona geçebilirsiniz.
                        </p>
                        <p x-show="anySupported()" x-cloak class="mt-4 text-xs text-ink-400">
                            Bu ayarlar önizlemeye anında yansır ve <strong>yalnızca</strong> aktif şablona özel saklanır — başka şablona geçince o şablonun kendi ayarları gelir.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('panel.dashboard') }}" class="btn-ghost">Vazgeç</a>
                    <button class="btn-primary px-8">Kaydet</button>
                </div>
            </form>

            {{-- Canlı önizleme --}}
            <div class="xl:sticky xl:top-20 xl:h-fit">
                <div class="mb-3 flex items-center justify-center gap-1.5 rounded-xl bg-white p-1 ring-1 ring-ink-200">
                    <button type="button" @click="setView('phone')"
                            :class="view === 'phone' ? 'bg-ink-900 text-white' : 'text-ink-500 hover:text-ink-900'"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="2.5" width="10" height="19" rx="2.5"/><path d="M10.5 18.5h3"/></svg>
                        Telefon
                    </button>
                    <button type="button" @click="setView('desktop')"
                            :class="view === 'desktop' ? 'bg-ink-900 text-white' : 'text-ink-500 hover:text-ink-900'"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="4" width="19" height="12" rx="2"/><path d="M9 20h6M12 16v4"/></svg>
                        Bilgisayar
                    </button>
                </div>

                <div class="relative rounded-2xl bg-ink-100 p-4 ring-1 ring-ink-200">
                    <div x-ref="host" class="mx-auto overflow-hidden transition-all"
                         :class="view === 'phone' ? 'w-[360px] max-w-full rounded-[2.25rem] bg-ink-950 p-3 shadow-2xl' : 'w-full rounded-xl shadow-xl'"
                         :style="`height: ${hostHeight + (view === 'phone' ? 24 : 0)}px`">
                        <iframe x-ref="frame" title="Canlı önizleme" class="border-0 bg-white"
                                :class="view === 'phone' ? 'rounded-[1.6rem]' : 'rounded-lg'"
                                :style="`width:${dims.w}px; height:${dims.h}px; transform: scale(${scale}); transform-origin: top left`"></iframe>
                    </div>
                    <div x-show="loading" class="pointer-events-none absolute inset-4 grid place-items-center">
                        <span class="rounded-full bg-ink-950/80 px-3 py-1 text-xs font-medium text-white">güncelleniyor…</span>
                    </div>
                </div>
                <p class="mt-3 text-center text-xs text-ink-400">
                    Renk & logo boyutu anında; şablon/font kısa gecikmeyle yansır.
                    Yaptığınız gelişmiş dokunuşlar bu şablona özel saklanır; <strong>Kaydet</strong>'e basınca yayınlanır.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
