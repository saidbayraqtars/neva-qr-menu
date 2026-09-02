@props([
    'title',
    'description' => null,
    'canonical' => null,
    /** @var array<string,string> id => başlık — sağdaki içindekiler listesi */
    'toc' => [],
])

<x-marketing-layout :title="$title" :description="$description" :canonical="$canonical">

    <section class="border-b border-ink-100 bg-white">
        <div class="mx-auto max-w-5xl px-6 pb-10 pt-16">
            <nav class="mb-5 text-xs text-ink-400">
                <a href="{{ route('home') }}" class="hover:text-ink-700">Ana sayfa</a>
                <span class="mx-1.5">/</span>
                <span class="text-ink-600">{{ $title }}</span>
            </nav>

            <h1 class="font-display text-3xl leading-tight text-ink-900 sm:text-4xl">{{ $title }}</h1>

            <p class="mt-4 text-sm text-ink-500">
                Yürürlük tarihi:
                <time datetime="{{ config('neva.legal.effective_date') }}">
                    {{ \Illuminate\Support\Carbon::parse(config('neva.legal.effective_date'))->translatedFormat('j F Y') }}
                </time>
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="gap-12 lg:grid lg:grid-cols-[1fr_15rem]">

            <article class="legal-doc">{{ $slot }}</article>

            @if ($toc !== [])
                <aside class="order-first mb-10 lg:order-none lg:mb-0">
                    <div class="lg:sticky lg:top-24">
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">İçindekiler</p>
                        <ul class="mt-4 space-y-2 border-l border-ink-100 text-sm">
                            @foreach ($toc as $id => $label)
                                <li>
                                    <a href="#{{ $id }}" class="-ml-px block border-l border-transparent pl-3 text-ink-500 transition hover:border-gold-500 hover:text-ink-900">{{ $label }}</a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-8 border-t border-ink-100 pt-6 text-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Diğer metinler</p>
                            <ul class="mt-3 space-y-2">
                                @foreach (['legal.privacy' => 'Gizlilik Politikası', 'legal.kvkk' => 'KVKK Aydınlatma Metni', 'legal.cookies' => 'Çerez Politikası', 'legal.terms' => 'Kullanım Koşulları'] as $route => $label)
                                    @unless (request()->routeIs($route))
                                        <li><a href="{{ route($route) }}" class="text-ink-500 hover:text-ink-900">{{ $label }}</a></li>
                                    @endunless
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </aside>
            @endif

        </div>
    </div>
</x-marketing-layout>
