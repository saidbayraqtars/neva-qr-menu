@php
    use App\Support\MenuSchema;

    $canonical = route('faq');

    // FAQPage tek başına basılır (graph içinde değil): Google'ın SSS zengin
    // sonucu, sayfada TEK bir FAQPage düğümü beklediğinde daha güvenilir eşleşir.
    $jsonld = MenuSchema::faq($faq);
@endphp

<x-marketing-layout
    title="Sıkça Sorulan Sorular — QR Menü"
    description="QR menü nedir, nasıl kurulur, fiyatı ne kadar, tasarım sonradan değiştirilebilir mi? Restoran ve kafelerin en çok sorduğu soruların yanıtları."
    :canonical="$canonical"
    :jsonld="$jsonld">

    <section class="bg-ink-950 text-white">
        <div class="mx-auto max-w-4xl px-6 py-20 text-center">
            <nav aria-label="Kırıntı yolu" class="text-xs text-ink-300">
                <a href="{{ route('home') }}" class="hover:text-white">Ana sayfa</a>
                <span class="mx-1.5 text-ink-500">/</span>
                <span class="text-white">Sıkça sorulan sorular</span>
            </nav>
            <h1 class="mt-5 font-display text-4xl sm:text-5xl">Sıkça sorulan sorular</h1>
            <p class="mx-auto mt-5 max-w-xl leading-relaxed text-ink-200">
                QR menüye geçmeden önce restoran ve kafelerin en çok merak ettiği konular.
                Aradığınız yanıt burada yoksa bize yazın, aynı gün dönüş yapalım.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-6 py-16">
        <div class="divide-y divide-ink-100">
            @foreach ($faq as $i => $item)
                {{-- <details>: JS'siz açılır-kapanır. Arama motoru gizli metni de
                     okur; accordion içeriği bu yüzden JS ile sonradan yüklenmez. --}}
                <details class="group py-5" @if ($i === 0) open @endif>
                    <summary class="flex cursor-pointer list-none items-start justify-between gap-4">
                        <h2 class="font-display text-lg leading-snug text-ink-900">{{ $item['q'] }}</h2>
                        <span class="mt-1 shrink-0 text-ink-400 transition group-open:rotate-45" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                        </span>
                    </summary>
                    <p class="mt-3 pr-9 leading-relaxed text-ink-600">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>

        <div class="mt-12 rounded-2xl bg-white p-8 text-center ring-1 ring-ink-100">
            <h2 class="font-display text-xl text-ink-900">Başka bir sorunuz mu var?</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-ink-500">
                Menünüzü nasıl kuracağınızdan paket seçimine kadar her konuda yazabilirsiniz.
            </p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('contact') }}" class="btn-gold px-6">Bize yazın</a>
                <a href="{{ route('showcase.index') }}" class="btn-ghost">Şablonlara göz atın</a>
            </div>
        </div>
    </section>
</x-marketing-layout>
