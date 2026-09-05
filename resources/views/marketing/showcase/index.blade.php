@php
    use App\Support\MenuSchema;

    $canonical = route('showcase.index');
    $count = count($templates);

    $jsonld = MenuSchema::graph(
        MenuSchema::organization(),
        MenuSchema::website(),
        MenuSchema::breadcrumb([
            ['Ana sayfa', route('home')],
            ['QR menü şablonları', $canonical],
        ]),
        [
            '@type' => 'CollectionPage',
            'name' => $count.' QR menü şablonu',
            'url' => $canonical,
            'inLanguage' => 'tr-TR',
            'isPartOf' => ['@id' => MenuSchema::websiteId()],
            'about' => ['@id' => url('/').'#app'],
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $count,
                'itemListElement' => collect($templates)->values()->map(fn ($t, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $t['label'],
                    'url' => route('showcase.show', array_keys($templates)[$i]),
                ])->all(),
            ],
        ],
    );
@endphp

<x-marketing-layout
    title="QR Menü Şablonları — {{ $count }} Hazır Tasarım"
    description="Restoran ve kafeler için {{ $count }} hazır QR menü tasarımı: koyu tema, sade liste, görselli ızgara, klasik menü kağıdı. Hepsi pakete dahil."
    :canonical="$canonical"
    :jsonld="$jsonld">

    {{-- ==================== BAŞLIK ==================== --}}
    <section class="bg-ink-950 text-white">
        <div class="mx-auto max-w-6xl px-6 py-20 sm:py-24">
            <nav aria-label="Kırıntı yolu" class="text-xs text-ink-300">
                <a href="{{ route('home') }}" class="hover:text-white">Ana sayfa</a>
                <span class="mx-1.5 text-ink-500">/</span>
                <span class="text-white">QR menü şablonları</span>
            </nav>

            <h1 class="mt-5 max-w-3xl font-display text-4xl leading-tight sm:text-5xl">
                {{ $count }} hazır QR menü şablonu
            </h1>

            <p class="mt-5 max-w-2xl text-lg leading-relaxed text-ink-200">
                Her şablonun kendi iskeleti, kendi tipografisi ve kendi rengi var — renk varyantı değil,
                ayrı ayrı tasarlanmış {{ $count }} menü. Aşağıdaki önizlemeler ekran görüntüsü değil,
                şablonun örnek bir menüyle canlı render'ı.
            </p>

            <p class="mt-4 max-w-2xl leading-relaxed text-ink-300">
                Tamamı her pakete dahildir. Bir şablon seçip menünüzü girdikten sonra
                dilediğiniz zaman başka bir şablona geçebilirsiniz; ürünleriniz, fiyatlarınız
                ve görselleriniz olduğu gibi taşınır.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="btn-gold px-6">Hemen başlayın</a>
                <a href="{{ route('pricing') }}" class="rounded-xl bg-white/10 px-6 py-2.5 text-sm font-semibold text-white ring-1 ring-white/15 transition hover:bg-white/15">Paketleri görün</a>
            </div>
        </div>
    </section>

    {{-- ==================== GALERİ ==================== --}}
    <section class="mx-auto max-w-6xl px-6 py-16" x-data="{ filter: 'all' }">

        {{-- Filtreler yalnızca görsel bir kolaylık: bütün kartlar HTML'de hep var,
             JS kapalıyken de arama motoru {{ $count }} bağlantının hepsini görür. --}}
        <div class="flex flex-wrap gap-2">
            @foreach ($groups as $value => $label)
                <button type="button" @click="filter = '{{ $value }}'"
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition"
                        :class="filter === '{{ $value }}' ? 'bg-ink-900 text-white' : 'bg-white text-ink-600 ring-1 ring-ink-200 hover:bg-ink-50'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($templates as $key => $template)
                <article class="group"
                         x-show="['all', '{{ $template['mood'] }}', '{{ $template['layout_type'] }}'].includes(filter)"
                         x-transition.opacity>
                    <a href="{{ route('showcase.show', $key) }}" class="block">
                        <div class="relative overflow-hidden rounded-2xl bg-ink-100 ring-1 ring-ink-200 transition group-hover:ring-gold-400">
                            {{-- Telefon oranı (9:19.5). iframe tıklanamaz: kart bir bağlantı.

                                 src DEĞİL data-src: tarayıcının `loading="lazy"`
                                 sezgisi bu ızgarada 40 iframe'i de hemen yüklüyor
                                 ve her biri kendi CSS/JS/font isteklerini açıyor.
                                 IntersectionObserver ile gerçekten görünen kart
                                 yüklenir; JS kapalıysa <noscript> devreye girer. --}}
                            <div class="relative w-full pt-[195%]">
                                <iframe data-src="{{ route('showcase.preview', $key) }}"
                                        title="{{ $template['label'] }} QR menü şablonu önizlemesi"
                                        loading="lazy"
                                        tabindex="-1"
                                        class="pointer-events-none absolute inset-0 h-full w-full border-0"></iframe>
                            </div>
                        </div>

                        <h2 class="mt-4 font-display text-xl text-ink-900 group-hover:text-gold-700">{{ $template['label'] }}</h2>
                    </a>

                    <p class="mt-1.5 text-sm leading-relaxed text-ink-500">{{ $template['description'] }}</p>

                    <div class="mt-3 flex flex-wrap gap-1.5 text-[0.6875rem]">
                        <span class="rounded-full bg-ink-50 px-2.5 py-1 font-medium text-ink-500 ring-1 ring-ink-100">{{ $template['layout'] }}</span>
                        <span class="rounded-full bg-ink-50 px-2.5 py-1 font-medium text-ink-500 ring-1 ring-ink-100">{{ $template['mood'] === 'dark' ? 'Koyu tema' : 'Açık tema' }}</span>
                        <span class="rounded-full bg-ink-50 px-2.5 py-1 font-medium text-ink-500 ring-1 ring-ink-100">{{ $template['font_display'] }}</span>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- ==================== METİN İÇERİK ====================
         Vitrin sayfası yalnızca görsellerden ibaret kalmasın: arama motoru
         sayfanın NE hakkında olduğunu metinden okur. --}}
    <section class="mx-auto max-w-3xl px-6 pb-8">
        <h2 class="font-display text-2xl text-ink-900">Doğru QR menü şablonu nasıl seçilir?</h2>

        <div class="mt-5 space-y-4 leading-relaxed text-ink-600">
            <p>
                Şablon seçimi, misafirin menüyü açtığı ilk üç saniyeyi belirler. Kararı kolaylaştıran
                tek soru şu: <strong class="text-ink-900">ürün fotoğraflarınız var mı?</strong>
                Fotoğrafınız varsa görselli ızgara ve masonry şablonlar tabakları öne çıkarır.
                Fotoğrafınız yoksa sade liste ve klasik menü kağıdı şablonları tipografiyle çalışır;
                boş görsel kutuları yerine düzgün bir okuma deneyimi verir.
            </p>
            <p>
                İkinci soru mekânın ışığı. Loş bir akşam restoranında koyu tema şablonlar telefonu
                göz yormayan bir seviyeye çeker; gün ışığı alan bir kafede açık tema hem daha okunaklı
                hem de baskıya daha yakın durur.
            </p>
            <p>
                Üçüncüsü menü uzunluğu. Elli kalemin üzerindeki menülerde sekmeli ve kompakt düzenler
                misafiri kaydırmaktan kurtarır; kısa ve seçili menülerde ise merkezî büyük kartlar
                her ürünü bir vitrin gibi sunar.
            </p>
            <p>
                Yanlış seçmekten çekinmeyin: {{ $count }} şablonun tamamı hesabınıza dahildir ve
                geçiş yapmak tek tıktır. Menü içeriğiniz şablondan bağımsız saklandığı için
                tasarımı değiştirdiğinizde hiçbir ürününüzü yeniden girmezsiniz.
            </p>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('faq') }}" class="btn-ghost">Sıkça sorulan sorular</a>
            <a href="{{ route('pricing') }}" class="btn-gold">Paketler ve fiyatlar</a>
        </div>
    </section>

    @push('scripts')
        <script>
            // Şablon önizlemelerini görünür oldukça bağla.
            // Her önizleme kendi CSS'ini, JS'ini ve fontlarını yükleyen tam bir
            // belge; 40'ını birden açmak ilk yüklemeyi saniyelerce uzatıyor.
            (function () {
                var frames = document.querySelectorAll('iframe[data-src]');
                if (!frames.length) return;

                function mount(frame) {
                    if (frame.dataset.src) {
                        frame.src = frame.dataset.src;
                        delete frame.dataset.src;
                    }
                }

                if (!('IntersectionObserver' in window)) {
                    frames.forEach(mount);
                    return;
                }

                // rootMargin: kart görünüre girmeden bir ekran önce yüklensin ki
                // kullanıcı boş kutu görmesin.
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) return;
                        mount(entry.target);
                        io.unobserve(entry.target);
                    });
                }, { rootMargin: '600px 0px' });

                frames.forEach(function (frame) { io.observe(frame); });

                // Emniyet: sekme arka planda açıldığında ya da sayfa ön-render
                // edildiğinde gözlemci hiç tetiklenmez ve kullanıcı sekmeye
                // döndüğünde boş kutular görür. Ekrana yakın olanları elle bağla.
                function mountVisible() {
                    // innerHeight 0 olabilir (ön-render, gizli sekme, bazı gömülü
                    // görünümler); o durumda ölçüye güvenmeyip makul bir taban kullan.
                    var limit = Math.max(window.innerHeight, 800) * 2;
                    var pending = document.querySelectorAll('iframe[data-src]');

                    pending.forEach(function (frame, i) {
                        var box = frame.getBoundingClientRect();
                        // İlk birkaçı koşulsuz bağla: ölçüm hiç çalışmasa bile
                        // kullanıcı sayfayı boş kutularla karşılamasın.
                        if (i < 6 || (box.top < limit && box.bottom > -limit)) {
                            mount(frame);
                            io.unobserve(frame);
                        }
                    });
                }

                document.addEventListener('visibilitychange', function () {
                    if (document.visibilityState === 'visible') mountVisible();
                });

                window.setTimeout(mountVisible, 2000);
            })();
        </script>
    @endpush
</x-marketing-layout>
