<x-marketing-layout
    title="QR Menü Fiyatları ve Paketler"
    description="QR menü fiyatları: hosting hariç, hosting dahil ve fiziksel QR basım paketleri. 40 tasarım her pakete dahil, gizli ücret yok."
    :canonical="route('pricing')"
    :jsonld="\App\Support\MenuSchema::offers($plans)">
    <section class="mx-auto max-w-5xl px-6 pt-20 pb-10 text-center">
        <h1 class="font-display text-4xl text-ink-900 sm:text-5xl">Tek seferlik, şeffaf paketler</h1>
        <p class="mx-auto mt-4 max-w-lg text-ink-500">Aylık abonelik yok. İhtiyacınıza uygun paketi seçin, başvurun; ödeme onayının ardından hesabınız açılır.</p>
    </section>

    <section class="mx-auto max-w-6xl px-6">
        <div class="grid gap-6 lg:grid-cols-3">
            @foreach ($plans as $plan)
                <div class="card flex flex-col p-8 {{ $plan->is_featured ? 'ring-2 ring-gold-500' : '' }}">
                    @if ($plan->is_featured)
                        <span class="mb-3 inline-block w-fit rounded-full bg-gold-500/15 px-2.5 py-1 text-xs font-semibold text-gold-700">En çok tercih edilen</span>
                    @endif
                    <h3 class="font-display text-xl text-ink-900">{{ $plan->name }}</h3>
                    @if ($plan->tagline)<p class="mt-1 text-sm text-ink-500">{{ $plan->tagline }}</p>@endif

                    <p class="mt-5">
                        <span class="font-display text-4xl text-ink-900">{{ money($plan->price, $plan->currency) }}</span>
                        <span class="text-sm text-ink-400">/ tek seferlik</span>
                    </p>
                    @if ($plan->hasTablePricing())
                        <p class="mt-1 text-xs text-ink-400">{{ $plan->setup_table_limit }} masaya kadar · sonraki her masa +{{ money($plan->extra_table_price, $plan->currency) }}</p>
                    @endif

                    <ul class="mt-6 flex-1 space-y-3 text-sm text-ink-600">
                        @foreach (($plan->features ?? []) as $feature)
                            <li class="flex gap-2.5">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('register') }}" class="{{ $plan->is_featured ? 'btn-gold' : 'btn-primary' }} mt-8">Bu paketle başvur</a>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Her pakette var --}}
    <section class="mx-auto max-w-4xl px-6 py-20">
        <h2 class="text-center font-display text-2xl text-ink-900">Her pakette var</h2>
        <div class="mt-8 grid gap-3 sm:grid-cols-2">
            @foreach ([
                '40+ premium kafe & restoran şablonu', 'Canlı önizleme editörü', 'Sınırsız kategori ve ürün',
                'Görsel & etiket yönetimi', 'Logo, renk ve yazı tipi özelleştirme', 'İndirilebilir QR kod',
                'Menü tasarımı ekibimizce kurulur', 'Türkçe arayüz ve destek',
            ] as $item)
                <div class="flex items-center gap-3 rounded-xl bg-white px-4 py-3 ring-1 ring-ink-100">
                    <svg class="h-4 w-4 shrink-0 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm text-ink-700">{{ $item }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- SSS --}}
    <section class="mx-auto max-w-3xl px-6 pb-20" x-data="{ open: 0 }">
        <h2 class="text-center font-display text-2xl text-ink-900">Sık sorulanlar</h2>
        <div class="mt-8 space-y-3">
            @foreach ([
                ['Kayıt nasıl işliyor?', 'Kayıt Ol adımında işletme bilgilerinizi girip bir paket seçersiniz. Başvurunuz ekibimize düşer; ödeme onaylandığında hesabınız açılır ve geçici şifreniz iletilir. İlk girişte kalıcı şifrenizi belirlersiniz.'],
                ['“Hosting Hariç” pakette QR nasıl çalışıyor?', 'Menünüzü kendi sitenizde/barındırmanızda tutarsınız. Panelde menünüzün web adresini girersiniz; sistem o adrese giden bağımsız, sabit bir QR kod üretir. QR’ı indirip doğrudan masalarınıza bastırırsınız. Bu pakette alt domain ve masa bazlı takip yer almaz — bunlar “Hosting Dahil” pakete özeldir.'],
                ['“Hosting Dahil” pakette ne değişiyor?', 'Menünüz isim.'.config('neva.root_domain').' alt domaininde yayınlanır ve panelde yaptığınız her düzenleme — şablon değişimi dahil — anında canlıya yansır. Tek işletme QR’ı + akıllı masa algılama, barındırma ve SSL bize aittir.'],
                ['Fiziksel QR paketinde masa sayısı nasıl hesaplanıyor?', '15 masaya kadar baskı ve kurulum paket fiyatına dahildir. 15’in üzerindeki her masa için 300 ₺ eklenir; başvuru adımında toplam tutarı anında görürsünüz.'],
                ['Kendi logomu ve renklerimi kullanabilir miyim?', 'Evet. Logo yükler, vurgu/başlık/zemin renklerini ve yazı tipini seçersiniz; hepsi canlı önizlemede görünür.'],
            ] as $i => [$q, $a])
                <div class="card overflow-hidden">
                    <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left">
                        <span class="font-semibold text-ink-900">{{ $q }}</span>
                        <svg class="h-4 w-4 shrink-0 text-ink-400 transition" :class="open === {{ $i }} && 'rotate-45'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse style="display:none">
                        <p class="px-5 pb-4 text-sm leading-relaxed text-ink-500">{{ $a }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-marketing-layout>
