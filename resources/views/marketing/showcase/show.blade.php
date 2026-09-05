@php
    use App\Support\MenuSchema;
    use App\Support\ShowcaseCopy;

    $canonical = route('showcase.show', $key);
    $copy = ShowcaseCopy::for($template);
    $total = count($all);

    $jsonld = MenuSchema::graph(
        MenuSchema::organization(),
        MenuSchema::website(),
        MenuSchema::breadcrumb([
            ['Ana sayfa', route('home')],
            ['QR menü şablonları', route('showcase.index')],
            [$template['label'], $canonical],
        ]),
        MenuSchema::templateShowcase($key, $template, $canonical),
    );
@endphp

<x-marketing-layout
    title="{{ $template['label'] }} QR Menü Şablonu"
    :description="ShowcaseCopy::metaDescription($template)"
    :canonical="$canonical"
    :jsonld="$jsonld">

    <div class="mx-auto max-w-6xl px-6 pt-10">
        <nav aria-label="Kırıntı yolu" class="text-xs text-ink-400">
            <a href="{{ route('home') }}" class="hover:text-ink-900">Ana sayfa</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('showcase.index') }}" class="hover:text-ink-900">QR menü şablonları</a>
            <span class="mx-1.5">/</span>
            <span class="text-ink-900">{{ $template['label'] }}</span>
        </nav>
    </div>

    {{-- ==================== ÜST: ÖNİZLEME + ÖZET ==================== --}}
    <section class="mx-auto max-w-6xl px-6 py-10">
        <div class="grid gap-12 lg:grid-cols-[minmax(0,22rem)_1fr] lg:gap-16">

            {{-- Telefon maketi: sayfanın kanıtı. `loading="eager"` çünkü bu,
                 sayfanın ana içeriği (LCP adayı) — tembel yüklenmemeli. --}}
            <div>
                <div class="mx-auto w-full max-w-[22rem] overflow-hidden rounded-[2rem] bg-ink-950 p-2.5 shadow-luxe ring-1 ring-ink-900/10">
                    <div class="relative w-full overflow-hidden rounded-[1.6rem] bg-white pt-[195%]">
                        <iframe src="{{ route('showcase.preview', $key) }}"
                                title="{{ $template['label'] }} şablonuyla hazırlanmış örnek QR menü"
                                class="absolute inset-0 h-full w-full border-0"></iframe>
                    </div>
                </div>
                <p class="mt-3 text-center text-xs text-ink-400">
                    Örnek menü — gerçek şablonun canlı render'ı, ekran görüntüsü değil.
                </p>
            </div>

            <div>
                <h1 class="font-display text-4xl leading-tight text-ink-900 sm:text-5xl">
                    {{ $template['label'] }}
                </h1>
                <p class="mt-2 text-sm font-medium uppercase tracking-wider text-gold-700">QR menü şablonu</p>

                <p class="mt-6 text-lg leading-relaxed text-ink-600">{{ $template['description'] }}</p>
                <p class="mt-4 leading-relaxed text-ink-600">{{ $copy['intro'] }}</p>

                {{-- Renk paleti — şablonun kendi tokenlarından, elle girilmez. --}}
                <div class="mt-8">
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Renk paleti</p>
                    <div class="mt-3 flex flex-wrap gap-3">
                        @foreach ([
                            'Zemin' => $template['tokens']['bg'],
                            'Yüzey' => $template['tokens']['surface'],
                            'Metin' => $template['tokens']['ink'],
                            'Vurgu' => $template['palette']['accent'],
                        ] as $label => $hex)
                            <div class="flex items-center gap-2">
                                <span class="h-7 w-7 rounded-lg ring-1 ring-ink-200" style="background: {{ $hex }}"></span>
                                <span class="text-xs text-ink-500">{{ $label }}<br><span class="font-mono text-ink-400">{{ strtoupper($hex) }}</span></span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Teknik künye: makine okunur olmasa da kullanıcıya net bilgi. --}}
                <dl class="mt-8 grid grid-cols-2 gap-x-6 gap-y-4 border-t border-ink-100 pt-6 text-sm">
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-ink-400">Yerleşim</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">{{ $template['layout'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-ink-400">Tema</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">{{ $template['mood'] === 'dark' ? 'Koyu' : 'Açık' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-ink-400">Başlık yazı tipi</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">{{ $template['font_display'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-ink-400">Gövde yazı tipi</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">{{ $template['font_body'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-ink-400">Kapak görseli</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">{{ ! empty($template['cover']) ? 'Destekler' : 'Kullanmaz' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-ink-400">Ürün açıklaması</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">{{ ! empty($template['hide_desc']) ? 'Gizli' : 'Görünür' }}</dd>
                    </div>
                </dl>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-gold px-6">Bu şablonla başlayın</a>
                    <a href="{{ route('pricing') }}" class="btn-ghost">Paketleri görün</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== METİN: ÖZELLİKLER ==================== --}}
    <section class="mx-auto max-w-3xl px-6 py-8">
        <h2 class="font-display text-2xl text-ink-900">{{ $template['label'] }} şablonu ne sunar?</h2>

        <div class="mt-5 space-y-4 leading-relaxed text-ink-600">
            <p>{{ $copy['layout'] }}</p>
            <p>{{ $copy['mood'] }}</p>
            <p>{{ $copy['typography'] }}</p>
            <p>{{ $copy['cover'] }}</p>
        </div>

        <h2 class="mt-12 font-display text-2xl text-ink-900">Kendi menünüze nasıl uygularsınız?</h2>

        <ol class="mt-5 space-y-4 leading-relaxed text-ink-600">
            <li>
                <strong class="text-ink-900">1. Hesabınızı açın.</strong>
                Paketinizi seçip başvurunuzu gönderin; ödeme onayının ardından panel erişiminiz açılır.
            </li>
            <li>
                <strong class="text-ink-900">2. {{ $template['label'] }} şablonunu seçin.</strong>
                Tasarım ekranında şablonu işaretlediğiniz anda telefon maketinde kendi menünüzle
                canlı önizlemesini görürsünüz.
            </li>
            <li>
                <strong class="text-ink-900">3. Menünüzü girin.</strong>
                Kategorileri ve ürünleri ekleyin, isterseniz fotoğraf yükleyin. Sıralamayı
                sürükle-bırak ile düzenlersiniz.
            </li>
            <li>
                <strong class="text-ink-900">4. QR kodunuzu bastırın.</strong>
                On QR tasarımı arasından seçin, masa başına ayrı kod üretin veya menünün
                yazdırılabilir PDF çıktısını indirin.
            </li>
        </ol>

        <p class="mt-6 leading-relaxed text-ink-600">
            Şablonu daha sonra değiştirmek isterseniz menünüzü yeniden girmeniz gerekmez:
            ürünleriniz, fiyatlarınız ve görselleriniz şablondan bağımsız saklanır.
            {{ $total }} tasarımın tamamı paketinize dahildir.
        </p>
    </section>

    {{-- ==================== BENZER ŞABLONLAR ==================== --}}
    <section class="mx-auto max-w-6xl px-6 py-12">
        <h2 class="font-display text-2xl text-ink-900">Benzer şablonlar</h2>

        <div class="mt-6 grid gap-8 sm:grid-cols-3">
            @foreach ($related as $relatedKey => $relatedTemplate)
                <article>
                    <a href="{{ route('showcase.show', $relatedKey) }}" class="group block">
                        <div class="overflow-hidden rounded-2xl bg-ink-100 ring-1 ring-ink-200 transition group-hover:ring-gold-400">
                            <div class="relative w-full pt-[195%]">
                                <iframe src="{{ route('showcase.preview', $relatedKey) }}"
                                        title="{{ $relatedTemplate['label'] }} şablonu önizlemesi"
                                        loading="lazy" tabindex="-1"
                                        class="pointer-events-none absolute inset-0 h-full w-full border-0"></iframe>
                            </div>
                        </div>
                        <h3 class="mt-3 font-display text-lg text-ink-900 group-hover:text-gold-700">{{ $relatedTemplate['label'] }}</h3>
                    </a>
                    <p class="mt-1 text-sm text-ink-500">{{ $relatedTemplate['description'] }}</p>
                </article>
            @endforeach
        </div>

        {{-- Önceki / sonraki: vitrini zincir haline getirir, hiçbir sayfa yalnız kalmaz. --}}
        <nav class="mt-12 flex flex-wrap items-center justify-between gap-4 border-t border-ink-100 pt-6 text-sm">
            <div>
                @if ($prev)
                    <a href="{{ route('showcase.show', $prev) }}" class="text-ink-600 hover:text-ink-900">
                        ← {{ $all[$prev]['label'] }}
                    </a>
                @endif
            </div>
            <a href="{{ route('showcase.index') }}" class="font-semibold text-gold-700 hover:underline">{{ $total }} şablonun tümü</a>
            <div>
                @if ($next)
                    <a href="{{ route('showcase.show', $next) }}" class="text-ink-600 hover:text-ink-900">
                        {{ $all[$next]['label'] }} →
                    </a>
                @endif
            </div>
        </nav>
    </section>
</x-marketing-layout>
