@php
    use App\Support\MenuSchema;

    // Ana sayfa varlık grafiğin kökü: Organization + WebSite + ürünün kendisi
    // aynı blokta, `@id` ile bağlı. Diğer sayfalar bu düğümlere referans verir.
    $homeJsonLd = MenuSchema::graph(
        MenuSchema::organization(),
        MenuSchema::website(),
        MenuSchema::softwareApplication($plans),
        MenuSchema::faq(array_slice((array) config('neva.seo.faq', []), 0, 4)),
    );
@endphp

<x-marketing-layout
    title="QR Menü — Restoran ve Kafeler İçin Dijital Menü"
    description="Restoranlar için QR menü: 40 hazır tasarımdan seçin, istediğiniz an değiştirin. İşletmenize özel alt domain, masaya özel QR, anlık fiyat güncelleme."
    :canonical="route('home')"
    :jsonld="$homeJsonLd">

    {{-- ==================== HERO ==================== --}}
    <section class="relative overflow-hidden bg-ink-950 text-white">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(58%_55%_at_18%_30%,rgba(200,169,106,0.20),transparent_62%)]"></div>
        <div class="pointer-events-none absolute -right-40 -top-32 h-[34rem] w-[34rem] rounded-full bg-gold-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 -left-32 h-96 w-96 rounded-full bg-gold-700/10 blur-3xl"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.12] [background-image:radial-gradient(rgb(255_255_255/0.35)_1px,transparent_1px)] [background-size:22px_22px] [mask-image:radial-gradient(80%_80%_at_50%_40%,#000,transparent)]"></div>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-ink-950 to-transparent"></div>

        <div class="relative mx-auto grid max-w-6xl gap-16 px-6 py-24 sm:py-28 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
            <div>
                {{-- Başlık ürünün ASIL farkını söylüyor: menü bir kez girilir,
                     tasarım istendiği zaman değişir. Eski metin ("bir deneyim")
                     hiçbir şey vaat etmiyordu ve "şablon aileleri" ifadesi
                     40 şablon sistemine geçilmeden önceki kurgudan kalmıştı. --}}
                <h1 data-reveal class="font-display text-[3.4rem] leading-[1.02] sm:text-6xl">
                    Bir menü girin,<br><span class="text-gold-400">40 tasarımda</span> deneyin.
                </h1>
                <p data-reveal style="--reveal-delay:100ms" class="mt-6 max-w-lg text-lg leading-relaxed text-ink-300">
                    Ürünlerinizi bir kez girersiniz; 40 hazır tasarım arasında dilediğiniz zaman
                    geçiş yaparsınız. Fiyatı değiştirdiğinizde masadaki QR kod aynı kalır,
                    menü anında güncellenir.
                </p>
                <div data-reveal style="--reveal-delay:180ms" class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-gold px-7 py-3 text-base">Hemen başvur</a>
                    <a href="{{ route('pricing') }}" class="btn nv-btn-outline px-7 py-3 text-base text-white ring-1 ring-white/25 hover:bg-white/10">Paketleri gör</a>
                </div>
                <ul data-reveal style="--reveal-delay:260ms" class="mt-9 flex flex-wrap gap-x-7 gap-y-2.5 text-sm text-ink-300">
                    @foreach (['40 tasarım, hepsi dahil', 'Tasarım değişimi tek tık', 'Fiyat değişikliği anında yansır'] as $f)
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 flex-shrink-0 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ $f }}
                        </li>
                    @endforeach
                </ul>
                <p data-reveal style="--reveal-delay:320ms" class="mt-7 text-xs text-ink-400">Tek seferlik ödeme · aylık abonelik yok · başvuru birkaç dakika</p>
            </div>

            {{-- Telefon maketi — içinde elde tasarlanmış menü akıyor --}}
            <div data-reveal="scale" style="--reveal-delay:200ms" class="nv-stage mx-auto w-[264px] py-6">
                <div class="nv-phone">
                    <div class="nv-phone-screen">
                        <div class="nv-phone-track">
                            <x-menu-mock variant="cafe" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== ŞABLONLAR ==================== --}}
    <section class="bg-white py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="mx-auto max-w-2xl text-center" data-reveal>
                <h2 class="font-display text-3xl text-ink-900 sm:text-4xl">Kırk tasarım, tek menü</h2>
                <p class="mt-3 text-ink-500">
                    Menünüzü bir kez girersiniz. Sonra istediğiniz tasarıma geçersiniz —
                    ürünleriniz, fiyatlarınız ve fotoğraflarınız olduğu gibi taşınır.
                    Aşağıda ikisi var; kalan otuz sekizi vitrin sayfasında.
                </p>
            </div>

            <div class="mt-16 grid gap-12 md:grid-cols-2 lg:gap-16">
                <div data-reveal="left">
                    <div class="nv-mock is-peek mx-auto max-w-[340px]">
                        <x-menu-mock variant="cafe" />
                    </div>
                    <div class="mt-7 text-center">
                        <span class="inline-flex rounded-full bg-ink-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider text-ink-500">Saf tipografi</span>
                        <h3 class="mt-2.5 font-display text-xl text-ink-900">Fotoğrafsız menüler için</h3>
                        <p class="mx-auto mt-1.5 max-w-sm text-sm leading-relaxed text-ink-500">Ürün fotoğrafı çekmeye vaktiniz yoksa: sade tipografi, hızlı taranan dikey liste. Boş görsel kutuları yerine düzgün bir okuma düzeni.</p>
                    </div>
                </div>

                <div data-reveal="right" style="--reveal-delay:120ms">
                    <div class="nv-mock is-peek mx-auto max-w-[340px]">
                        <x-menu-mock variant="dark" />
                    </div>
                    <div class="mt-7 text-center">
                        <span class="inline-flex rounded-full bg-gold-500/15 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider text-gold-700">Görsel odaklı</span>
                        <h3 class="mt-2.5 font-display text-xl text-ink-900">Fotoğraflı menüler için</h3>
                        <p class="mx-auto mt-1.5 max-w-sm text-sm leading-relaxed text-ink-500">Tabak fotoğraflarınız varsa: görseller büyük tutulur, öne çıkan ürünler vurgulanır. Akşam servisinde koyu zemin göz yormaz.</p>
                    </div>
                </div>
            </div>

            <p class="mt-14 text-center text-sm" data-reveal>
                <a href="{{ route('showcase.index') }}" class="font-medium text-gold-700 hover:underline">40 tasarımın tümünü görün →</a>
            </p>
        </div>
    </section>

    {{-- ==================== ÖZELLİKLER (görselli bento) ==================== --}}
    <section class="mx-auto max-w-6xl px-6 py-24">
        <div class="mx-auto max-w-2xl text-center" data-reveal>
            <h2 class="font-display text-3xl text-ink-900 sm:text-4xl">Menüden fazlası</h2>
            <p class="mt-3 text-ink-500">Küçük bir işletme için de büyük bir zincir için de aynı özenle çalışır.</p>
        </div>

        @php
            $qr = '';
            mt_srand(7);
            for ($y = 0; $y < 21; $y++) {
                for ($x = 0; $x < 21; $x++) {
                    $finder = ($x < 7 && $y < 7) || ($x >= 14 && $y < 7) || ($x < 7 && $y >= 14);
                    if (! $finder && mt_rand(0, 100) < 45) {
                        $qr .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"1\" height=\"1\"/>";
                    }
                }
            }
        @endphp

        <div class="nv-fbento mt-14">
            {{-- 1 · Canlı önizleme — geniş --}}
            <article class="nv-fcard nv-fcard--wide" data-reveal>
                <div class="nv-fcard__body">
                    <div class="nv-feat__ico">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    </div>
                    <h3 class="mt-4 font-display text-xl text-ink-900">Canlı önizleme</h3>
                    <p class="mt-1.5 max-w-sm text-sm leading-relaxed text-ink-500">Renk, logo ve ürün değişiklikleri telefon maketinde anında görünür. Kaydetmeden önce misafirin göreceği menüyü görürsünüz.</p>
                </div>
                <div class="nv-fcard__art">
                    <div class="fx-phone">
                        <div class="fx-phone__scr">
                            <x-menu-mock variant="cafe" />
                        </div>
                    </div>
                </div>
            </article>

            {{-- 2 · Kendi alt domaininiz --}}
            <article class="nv-fcard" data-reveal style="--reveal-delay:80ms">
                <div class="nv-fcard__art">
                    <div class="fx-browser">
                        <div class="fx-browser__bar">
                            <i></i><i></i><i></i>
                            <span class="fx-browser__url">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75M3.75 21.75h16.5a1.5 1.5 0 0 0 1.5-1.5v-8.25a1.5 1.5 0 0 0-1.5-1.5H3.75a1.5 1.5 0 0 0-1.5 1.5v8.25a1.5 1.5 0 0 0 1.5 1.5Z"/></svg>
                                lumina.{{ config('neva.root_domain') }}
                            </span>
                        </div>
                        <div class="fx-browser__page">Lumina</div>
                    </div>
                </div>
                <div class="nv-fcard__body">
                    <h3 class="font-display text-lg text-ink-900">Kendi alt domaininiz</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-500">isletmeniz.{{ config('neva.root_domain') }} gibi markalı bir adres. Ekip onayının ardından anında yayında.</p>
                </div>
            </article>

            {{-- 3 · Tek QR, akıllı masa algılama --}}
            <article class="nv-fcard" data-reveal style="--reveal-delay:160ms">
                <div class="nv-fcard__art">
                    <div class="fx-qr">
                        <svg viewBox="0 0 21 21" fill="#17170f" shape-rendering="crispEdges" aria-hidden="true">
                            {!! $qr !!}
                            @foreach ([[0,0],[14,0],[0,14]] as [$fx,$fy])
                                <rect x="{{ $fx }}" y="{{ $fy }}" width="7" height="7"/>
                                <rect x="{{ $fx+1 }}" y="{{ $fy+1 }}" width="5" height="5" fill="#fff"/>
                                <rect x="{{ $fx+2 }}" y="{{ $fy+2 }}" width="3" height="3" fill="#17170f"/>
                            @endforeach
                        </svg>
                        <span class="fx-qr__tag">Masa 4</span>
                    </div>
                </div>
                <div class="nv-fcard__body">
                    <h3 class="font-display text-lg text-ink-900">Tek QR, akıllı masa algılama</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-500">Tek bir işletme QR'ı yeterli. Menü hangi masadan açıldığını algılar, zarif bir etiketle gösterir.</p>
                </div>
            </article>

            {{-- 4 · Tek tıkla PDF broşür --}}
            <article class="nv-fcard" data-reveal style="--reveal-delay:240ms">
                <div class="nv-fcard__art">
                    <div class="fx-pdf">
                        <div class="fx-pdf__sheet">
                            <i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                        </div>
                    </div>
                </div>
                <div class="nv-fcard__body">
                    <h3 class="font-display text-lg text-ink-900">Tek tıkla PDF broşür</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-500">Menünüzün tamamı, canlı tasarımıyla birebir aynı; baskıya hazır A4 broşür veya masa standı.</p>
                </div>
            </article>
        </div>
    </section>

    {{-- ==================== FİYAT ==================== --}}
    <section class="bg-white py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="mx-auto max-w-2xl text-center" data-reveal>
                <h2 class="font-display text-3xl text-ink-900 sm:text-4xl">Tek seferlik paketler</h2>
                <p class="mt-3 text-ink-500">Aylık abonelik yok. Bir kez ödeyin, menünüz sizin olsun.</p>
            </div>
            <div class="mt-12 grid gap-6 lg:grid-cols-3">
                @foreach ($plans as $i => $plan)
                    <div class="card nv-card flex flex-col p-8 {{ $plan->is_featured ? 'ring-2 ring-gold-500' : '' }}" data-reveal style="--reveal-delay:{{ $i * 90 }}ms">
                        @if ($plan->is_featured)
                            <span class="mb-3 inline-block w-fit rounded-full bg-gold-500/15 px-2.5 py-1 text-xs font-semibold text-gold-700">En çok tercih edilen</span>
                        @endif
                        <h3 class="font-display text-xl text-ink-900">{{ $plan->name }}</h3>
                        @if ($plan->tagline)<p class="mt-1 text-sm text-ink-500">{{ $plan->tagline }}</p>@endif
                        <p class="mt-4"><span class="font-display text-4xl text-ink-900">{{ money($plan->price, $plan->currency) }}</span><span class="text-sm text-ink-400"> / tek seferlik</span></p>
                        @if ($plan->hasTablePricing())
                            <p class="mt-1 text-xs text-ink-400">{{ $plan->setup_table_limit }} masaya kadar · sonrası masa başı +{{ money($plan->extra_table_price, $plan->currency) }}</p>
                        @endif
                        <ul class="mt-6 flex-1 space-y-2.5 text-sm text-ink-600">
                            @foreach (($plan->features ?? []) as $feature)
                                <li class="flex gap-2"><span class="mt-0.5 text-gold-600">✓</span><span>{{ $feature }}</span></li>
                            @endforeach
                        </ul>
                        <a href="{{ route('register') }}" class="{{ $plan->is_featured ? 'btn-gold' : 'btn-primary' }} mt-7">Bu paketle başvur</a>
                    </div>
                @endforeach
            </div>
            <p class="mt-6 text-center text-sm"><a href="{{ route('pricing') }}" class="font-medium text-gold-700 hover:underline">Paket detayları ve SSS →</a></p>
        </div>
    </section>

    {{-- ==================== SON CTA ==================== --}}
    <section class="mx-auto max-w-6xl px-6 py-24">
        <div class="relative overflow-hidden rounded-3xl bg-ink-950 px-8 py-16 text-center text-white sm:px-16" data-reveal="scale">
            <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-gold-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-16 h-64 w-64 rounded-full bg-gold-700/15 blur-3xl"></div>
            <h2 class="relative font-display text-3xl sm:text-4xl">Menünüzü bugün dönüştürün</h2>
            <p class="relative mx-auto mt-3 max-w-md text-ink-300">Paketinizi seçin, başvurun; ödeme onayının ardından hemen yayında.</p>
            <a href="{{ route('register') }}" class="btn-gold relative mt-8 px-8 py-3 text-base">Başvuru yap</a>
        </div>
    </section>
</x-marketing-layout>
