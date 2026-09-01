<x-marketing-layout
    title="Hakkımızda"
    description="Neva-QR Menü; restoran ve kafelere markasına özel, hızlı ve kolay yönetilen dijital QR menü çözümü sunar."
    :canonical="route('about')">

    {{-- ==================== HERO ==================== --}}
    <section class="relative overflow-hidden bg-ink-950 text-white">
        <div class="pointer-events-none absolute -right-40 -top-32 h-96 w-96 rounded-full bg-gold-500/15 blur-3xl"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.12] [background-image:radial-gradient(rgb(255_255_255/0.35)_1px,transparent_1px)] [background-size:22px_22px]"></div>
        <div class="relative mx-auto max-w-4xl px-6 py-24 text-center sm:py-28">
            <p data-reveal="fade" class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-gold-300 ring-1 ring-white/10">
                <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span>
                Hakkımızda
            </p>
            <h1 data-reveal style="--reveal-delay:80ms" class="font-display text-4xl leading-tight sm:text-5xl">
                Misafir masaya oturduğunda<br>ilk izlenim <span class="text-gold-400">menüdür.</span>
            </h1>
            <p data-reveal style="--reveal-delay:160ms" class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-ink-300">
                Neva-QR Menü; kafe ve restoranların dijitalleşme sürecini sadeleştirmek, bu ilk
                izlenimi güçlü bir markaya dönüştürmek için kuruldu. Basılı menülerin ve sıradan
                QR sayfalarının yerine, özenle tasarlanmış ve hızlı çalışan bir dijital deneyim.
            </p>
        </div>
    </section>

    {{-- ==================== MİSYON ==================== --}}
    <section class="mx-auto max-w-5xl px-6 py-24">
        <div class="grid gap-12 sm:grid-cols-2">
            <div data-reveal="left">
                <h2 class="font-display text-2xl text-ink-900">Neden Neva?</h2>
                <p class="mt-3 text-sm leading-relaxed text-ink-600">
                    Çoğu QR menü aracı, menünüzü gri bir tabloya çevirir. Biz tam tersini yapıyoruz:
                    işletmenizin rengini, logosunu ve karakterini taşıyan; telefonda saniyeler içinde
                    açılan; okunması keyifli menüler. Tasarımı biz düşündük — siz yalnızca içeriği
                    girin, gerisini platform hallesin.
                </p>
            </div>
            <div data-reveal="right" style="--reveal-delay:100ms">
                <h2 class="font-display text-2xl text-ink-900">Nasıl çalışıyoruz?</h2>
                <p class="mt-3 text-sm leading-relaxed text-ink-600">
                    Kayıt olun, paketinizi seçin ve başvurun. Menünüzü hazırlarken canlı önizlemede
                    her değişikliği anında görürsünüz. Alt domain talepleri ekibimizce kontrol edilip
                    onaylanır; böylece platformda tutarlı bir kalite ve güven korunur. Yayına
                    geçtikten sonra tek bir QR ve markalı bir adres yeterlidir.
                </p>
            </div>
        </div>

        {{-- Değerler — bento grid --}}
        <div class="nv-bento mt-16">
            @foreach ([
                ['Tasarım önce', 'Apple ve Stripe kalitesinde bir arayüzü her işletme için erişilebilir kılıyoruz. Şablon değil, üzerinde düşünülmüş gerçek bir tasarım.',
                    'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z', true],
                ['Hız', 'Menüler mobilde milisaniyeler içinde açılır; misafir beklemez, sipariş hızlanır.',
                    'M3.75 13.5 10.5 4.5v6h6.75L10.5 19.5v-6H3.75Z', false],
                ['Sadelik', 'Teknik bilgi gerekmez. Kayıt olun, menünüzü girin, tek tıkla yayınlayın.',
                    'M4.5 12.75 10.5 18.75 19.5 5.25', false],
                ['Güven', 'Güvenli bulut altyapısı, izole ve yedekli veri, kesintisiz erişim; alt domain onayıyla korunan platform kalitesi.',
                    'M9 12.75 11.25 15 15 9.75 M21 12c0 5.25-3.75 8.25-8.573 9.938a1.3 1.3 0 0 1-.854 0C6.75 20.25 3 17.25 3 12V6.375c0-.621.504-1.125 1.125-1.125 2.25 0 4.875-1.05 6.375-2.25 1.5 1.2 4.125 2.25 6.375 2.25.621 0 1.125.504 1.125 1.125V12Z', false],
            ] as $i => [$t, $b, $icon, $accent])
                <div class="nv-bento__card {{ $accent ? 'is-accent' : '' }}" data-reveal style="--reveal-delay:{{ $i * 80 }}ms">
                    <div class="nv-bento__ico">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                    </div>
                    <h3 class="nv-bento__t">{{ $t }}</h3>
                    <p class="nv-bento__b">{{ $b }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ==================== KİMLER İÇİN ==================== --}}
    <section class="bg-white py-24">
        <div class="mx-auto max-w-5xl px-6">
            <div class="text-center" data-reveal>
                <h2 class="font-display text-3xl text-ink-900">Kimler için?</h2>
                <p class="mx-auto mt-3 max-w-xl text-ink-500">
                    Tek şubeli bir kafeden çok lokasyonlu bir restoran grubuna kadar; menüsünü
                    dijitalleştirmek isteyen ama ucuz görünmek istemeyen herkes için.
                </p>
            </div>
            <div class="mt-12 grid gap-6 sm:grid-cols-3">
                @foreach ([
                    ['Kafeler', 'Üçüncü nesil kahvecilerden butik pastanelere — sade, hızlı taranan menüler.',
                        'M4.5 21h15M6 10.5h9a3.75 3.75 0 0 1 0 7.5H6v-7.5Zm0 0V6.75A2.25 2.25 0 0 1 8.25 4.5h4.5A2.25 2.25 0 0 1 15 6.75V10.5M18 12h.75a2.25 2.25 0 0 1 0 4.5H18'],
                    ['Restoranlar', 'Geniş menüleri kategorilere ayıran, öne çıkan ürünleri vurgulayan atmosferik düzen.',
                        'M4.5 3.75v6a3 3 0 0 0 6 0v-6M7.5 3.75v16.5M16.5 3.75c-1.657 0-3 2.015-3 4.5s1.343 4.5 3 4.5 3-2.015 3-4.5-1.343-4.5-3-4.5Zm0 9v7.5'],
                    ['Oteller & mekanlar', 'Kat kafeleri, teras barlar, etkinlik alanları için tek çatı altında tutarlı menüler.',
                        'M2.25 21h19.5M3.75 21V7.5l6-3.75 6 3.75V21M9 21v-4.5h3V21M9 10.5h.008v.008H9V10.5Zm3 0h.008v.008H12V10.5Zm3 3.75h3.75V21'],
                ] as $i => [$t, $b, $icon])
                    <div class="nv-feat" data-reveal style="--reveal-delay:{{ $i * 90 }}ms">
                        <div class="nv-feat__ico">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                        </div>
                        <h3 class="mt-4 font-display text-lg text-ink-900">{{ $t }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-ink-500">{{ $b }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==================== CTA ==================== --}}
    <section class="mx-auto max-w-6xl px-6 py-24">
        <div class="relative overflow-hidden rounded-3xl bg-ink-950 px-8 py-16 text-center text-white ring-1 ring-white/10 sm:px-16" data-reveal="scale">
            <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-gold-500/20 blur-3xl"></div>
            <h2 class="relative font-display text-3xl">Menünüzü birlikte tasarlayalım</h2>
            <p class="relative mx-auto mt-3 max-w-md text-ink-300">Paketinizi seçip başvurun veya sorularınız için bize yazın.</p>
            <div class="relative mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('register') }}" class="btn-gold px-7 py-3 text-base">Başvuru yap</a>
                <a href="{{ route('contact') }}" class="btn nv-btn-outline px-7 py-3 text-base text-white ring-1 ring-white/25 hover:bg-white/10">İletişime geç</a>
            </div>
        </div>
    </section>
</x-marketing-layout>
