@php
    use App\Support\MenuSchema;
    use Illuminate\Support\Str;

    $canonical = route('city', $slug);
    $name = $city['name'];
    $in = $city['in'];

    /*
    | Yerinde kurulum metni sahadaki gerçeğe bağlı. Uydurma söz vermek yerel
    | SEO'da en pahalı hata: müşteri arar, olmadığını duyar, yorum düşer.
    */
    $onsite = match ($city['onsite']) {
        'hub' => [
            'label' => 'Merkezimiz burada',
            'text' => 'Şirket merkezimiz '.$in.'. Fiziksel QR Basım & Kurulum Paketi’nde masa standlarını basıp işletmenize gelip kendimiz yerleştiriyoruz; şehir içi randevular genellikle aynı hafta veriliyor.',
        ],
        'route' => [
            'label' => 'Randevulu yerinde kurulum',
            'text' => 'Samsun merkezliyiz, '.$name.' karayoluyla yaklaşık '.$city['distance_km'].' km. Fiziksel QR Basım & Kurulum Paketi aldıysanız masa standlarını basıp randevulu olarak yerinde kuruyoruz. Diğer paketlerde kuruluma gerek yoktur: menü hazırlığı, alt domain ve QR üretimi tamamen uzaktan yapılır.',
        ],
        default => [
            'label' => 'Uzaktan kurulum',
            'text' => 'Menü hazırlığı, alt domain açılışı ve QR üretimi uzaktan yapılır; masa standları kargoyla gönderilir. Kurulum için gelmemiz gerekmez, yayına geçmeniz gecikmez.',
        ],
    };

    /*
    | Meta açıklama 160 karakterde kesilir. Cümle ortasında kesilen açıklama
    | sonuç sayfasında yarım görünür, tıklama oranını düşürür — bu yüzden
    | metin sığacak uzunlukta kuruluyor, Str::limit yalnızca emniyet kemeri.
    */
    $districtLine = collect($city['districts'])->take(3)->join(', ', ' ve ');

    $description = Str::limit(
        $name.' QR menü kurulumu: 40 hazır tasarım, işletmenize özel alt domain, anlık fiyat güncelleme. '
        .$districtLine.' dahil '.$name.' geneli.',
        160
    );

    /*
    | İşaretleme:
    |  - Service + areaServed → "bu hizmet bu ilde veriliyor". Şubemiz olmayan
    |    bir ile LocalBusiness koymak yanlış beyandır, o yüzden Service.
    |  - LocalBusiness YALNIZCA merkezin bulunduğu şehirde (onsite = hub).
    |  - FAQPage şehre özel sorulardan üretilir; genel SSS'yi tekrar etmez.
    */
    $jsonld = MenuSchema::graph(
        MenuSchema::organization(),
        MenuSchema::website(),
        $city['onsite'] === 'hub' ? MenuSchema::localBusiness() : null,
        MenuSchema::cityService($slug, $city, $plans),
        MenuSchema::breadcrumb([
            ['Ana sayfa', route('home')],
            [$name.' QR Menü', $canonical],
        ]),
        MenuSchema::faq($city['faq']),
    );
@endphp

<x-marketing-layout
    title="{{ $name }} QR Menü — Restoran ve Kafeler"
    :description="$description"
    :canonical="$canonical"
    :jsonld="$jsonld">

    <div class="mx-auto max-w-6xl px-6 pt-10">
        <nav aria-label="Kırıntı yolu" class="text-xs text-ink-400">
            <a href="{{ route('home') }}" class="hover:text-ink-900">Ana sayfa</a>
            <span class="mx-1.5">/</span>
            <span class="text-ink-900">{{ $name }} QR Menü</span>
        </nav>
    </div>

    {{-- ==================== HERO ==================== --}}
    <section class="relative overflow-hidden">
        <div class="mx-auto max-w-4xl px-6 py-16 sm:py-20">
            <p data-reveal="fade" class="mb-5 inline-flex items-center gap-2 rounded-full bg-ink-900/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-ink-500 ring-1 ring-ink-900/10">
                <span class="h-1.5 w-1.5 rounded-full bg-gold-500"></span>
                {{ $city['plate'] }} · {{ $name }}
            </p>
            <h1 data-reveal style="--reveal-delay:80ms" class="font-display text-4xl leading-tight text-ink-900 sm:text-5xl">
                {{ $name }} QR menü:<br>kafeniz ve restoranınız için <span class="text-gold-600">dijital menü</span>
            </h1>
            <p data-reveal style="--reveal-delay:160ms" class="mt-6 max-w-2xl text-lg leading-relaxed text-ink-600">
                {{ $city['lead'] }}
            </p>
            <div data-reveal style="--reveal-delay:240ms" class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="btn-primary px-7 py-3 text-base">Başvuru yap</a>
                <a href="{{ route('showcase.index') }}" class="btn nv-btn-outline px-7 py-3 text-base">40 şablonu gör</a>
            </div>
        </div>
    </section>

    {{-- ==================== YEREL SAHNE ==================== --}}
    <section class="border-y border-ink-100 bg-white">
        <div class="mx-auto max-w-5xl px-6 py-16">
            <div class="grid gap-10 sm:grid-cols-[1.4fr_1fr] sm:gap-14">
                <div data-reveal="left">
                    <h2 class="font-display text-2xl text-ink-900">{{ $name }}’de işletmeler neyle uğraşıyor?</h2>
                    <p class="mt-4 text-sm leading-relaxed text-ink-600">{{ $city['scene'] }}</p>
                </div>
                <div data-reveal="right" style="--reveal-delay:100ms">
                    <h2 class="font-display text-2xl text-ink-900">Hizmet verdiğimiz ilçeler</h2>
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($city['districts'] as $district)
                            <li class="rounded-lg bg-ink-50 px-3 py-1.5 text-sm text-ink-700 ring-1 ring-ink-100">{{ $district }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-6 rounded-xl bg-gold-50 p-4 ring-1 ring-gold-200/70">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gold-700">{{ $onsite['label'] }}</p>
                        <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ $onsite['text'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== ÖNERİLEN ŞABLONLAR ==================== --}}
    <section class="mx-auto max-w-6xl px-6 py-20">
        <div data-reveal>
            <h2 class="font-display text-3xl text-ink-900">{{ $name }} işletmeleri için önerdiğimiz şablonlar</h2>
            <p class="mt-3 max-w-2xl text-ink-500">{{ $city['template_why'] }} Paketinize 40 şablonun tamamı dahildir; aralarında istediğiniz kadar geçiş yapabilirsiniz.</p>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-3">
            @foreach ($templates as $key => $template)
                <a href="{{ route('showcase.show', $key) }}"
                   class="group rounded-2xl bg-white p-6 ring-1 ring-ink-100 transition hover:-translate-y-0.5 hover:ring-ink-200"
                   data-reveal style="--reveal-delay:{{ $loop->index * 90 }}ms">
                    <span class="inline-flex h-8 items-center rounded-lg px-3 text-xs font-semibold"
                          style="background:{{ $template['tokens']['bg'] }};color:{{ $template['palette']['heading'] }}">
                        {{ $template['layout'] }}
                    </span>
                    <h3 class="mt-4 font-display text-lg text-ink-900">{{ $template['label'] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-500">{{ $template['description'] }}</p>
                    <span class="mt-4 inline-block text-sm font-semibold text-gold-700 group-hover:underline">Şablonu incele →</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ==================== PAKETLER ==================== --}}
    <section class="border-y border-ink-100 bg-white">
        <div class="mx-auto max-w-5xl px-6 py-20">
            <div data-reveal>
                <h2 class="font-display text-3xl text-ink-900">{{ $name }} için paketler</h2>
                <p class="mt-3 max-w-2xl text-ink-500">
                    Aylık abonelik yoktur — paketler tek seferliktir. Fiyatlar {{ $name }} dahil Türkiye genelinde aynıdır;
                    yerinde kurulum yalnızca fiziksel pakette bulunur.
                </p>
            </div>

            <div class="mt-10 grid gap-5 sm:grid-cols-3">
                @foreach ($plans as $plan)
                    <div class="rounded-2xl p-6 ring-1 {{ $plan->is_featured ? 'bg-ink-950 text-white ring-ink-900' : 'bg-ink-50 ring-ink-100' }}"
                         data-reveal style="--reveal-delay:{{ $loop->index * 90 }}ms">
                        <h3 class="font-display text-lg {{ $plan->is_featured ? 'text-white' : 'text-ink-900' }}">{{ $plan->name }}</h3>
                        <p class="mt-1.5 text-sm {{ $plan->is_featured ? 'text-ink-300' : 'text-ink-500' }}">{{ $plan->tagline }}</p>
                        <p class="mt-4 font-display text-2xl {{ $plan->is_featured ? 'text-gold-400' : 'text-ink-900' }}">{{ money($plan->price) }}</p>
                    </div>
                @endforeach
            </div>

            <a href="{{ route('pricing') }}" class="mt-8 inline-block text-sm font-semibold text-gold-700 hover:underline">Paketlerin tam kapsamı ve ödeme koşulları →</a>
        </div>
    </section>

    {{-- ==================== ŞEHRE ÖZEL SSS ==================== --}}
    <section class="mx-auto max-w-3xl px-6 py-20">
        <h2 class="font-display text-3xl text-ink-900" data-reveal>{{ $name }} için sık sorulanlar</h2>
        <dl class="mt-8 space-y-4">
            @foreach ($city['faq'] as $i => $item)
                <div class="rounded-2xl bg-white p-6 ring-1 ring-ink-100" data-reveal style="--reveal-delay:{{ $i * 80 }}ms">
                    <dt class="font-semibold text-ink-900">{{ $item['q'] }}</dt>
                    <dd class="mt-2 text-sm leading-relaxed text-ink-600">{{ $item['a'] }}</dd>
                </div>
            @endforeach
        </dl>
        <a href="{{ route('faq') }}" class="mt-6 inline-block text-sm font-semibold text-gold-700 hover:underline">Tüm sıkça sorulan sorular →</a>
    </section>

    {{-- ==================== DİĞER ŞEHİRLER ==================== --}}
    <section class="border-t border-ink-100 bg-white">
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-400">Hizmet verdiğimiz diğer şehirler</h2>
            <ul class="mt-4 flex flex-wrap gap-x-5 gap-y-2.5 text-sm text-ink-600">
                @foreach ($others as $otherSlug => $other)
                    <li><a href="{{ route('city', $otherSlug) }}" class="hover:text-ink-900">{{ $other['name'] }} QR menü</a></li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ==================== CTA ==================== --}}
    <section class="mx-auto max-w-6xl px-6 py-20">
        <div class="relative overflow-hidden rounded-3xl bg-ink-950 px-8 py-16 text-center text-white ring-1 ring-white/10 sm:px-16" data-reveal="scale">
            <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-gold-500/20 blur-3xl"></div>
            <h2 class="relative font-display text-3xl">{{ $name }}’deki menünüzü dijitale taşıyalım</h2>
            <p class="relative mx-auto mt-3 max-w-md text-ink-300">Paketinizi seçip başvurun, ya da önce konuşmak isterseniz bize yazın.</p>
            <div class="relative mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('register') }}" class="btn-gold px-7 py-3 text-base">Başvuru yap</a>
                <a href="{{ route('contact') }}" class="btn nv-btn-outline px-7 py-3 text-base text-white ring-1 ring-white/25 hover:bg-white/10">İletişime geç</a>
            </div>
        </div>
    </section>
</x-marketing-layout>
