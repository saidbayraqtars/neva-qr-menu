@php
    use App\Support\MenuSchema;

    // İletişim sayfası, adresin gerçekten göründüğü yer: LocalBusiness burada
    // en yerinde duruyor. Google yerel eşleştirmede NAP (ad-adres-telefon)
    // tutarlılığına bakar; künye tek kaynaktan (config/neva.legal) beslenir.
    $contactJsonLd = MenuSchema::graph(
        MenuSchema::organization(),
        MenuSchema::localBusiness(),
        MenuSchema::breadcrumb([
            ['Ana sayfa', route('home')],
            ['İletişim', route('contact')],
        ]),
    );
@endphp

<x-marketing-layout
    title="İletişim ve Demo Talebi"
    description="QR menü demo talebi, paket soruları ve destek için Neva-QR ekibine ulaşın. Hafta içi 09:00–18:00 arası, bir iş günü içinde dönüş yapıyoruz."
    :canonical="route('contact')"
    :jsonld="$contactJsonLd">

    <section class="mx-auto max-w-5xl px-6 py-20">
        <div class="grid gap-14 lg:grid-cols-[0.9fr_1.1fr]">
            <div>
                <h1 class="font-display text-4xl text-ink-900 sm:text-5xl">İletişim</h1>
                <p class="mt-4 text-ink-500">Sorularınız, demo talepleriniz veya iş birlikleri için bize yazın. Genellikle bir iş günü içinde dönüş yapıyoruz.</p>

                <div class="mt-10 space-y-5">
                    <div class="flex items-start gap-4">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gold-500/12 ring-1 ring-gold-500/25">
                            <svg class="h-4 w-4 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M3 8v8a2 2 0 002 2h14a2 2 0 002-2V8M3 8l2-2h14l2 2"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink-900">E-posta</p>
                            <a href="mailto:{{ config('neva.brand.support_email') }}" class="text-sm text-ink-500 hover:text-ink-900">{{ config('neva.brand.support_email') }}</a>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gold-500/12 ring-1 ring-gold-500/25">
                            <svg class="h-4 w-4 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink-900">Çalışma saatleri</p>
                            <p class="text-sm text-ink-500">Hafta içi 09:00 – 18:00 (TSİ)</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-8">
                @if (session('success'))
                    <div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">{{ session('success') }}</div>
                @endif

                @auth
                    {{-- Giriş yapmış kullanıcıdan ad/e-posta TEKRAR İSTENMEZ.
                         Panel içi çift yönlü mesajlaşmaya yönlendirilir. --}}
                    <div class="text-center">
                        <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-gold-500/12 ring-1 ring-gold-500/25">
                            <svg class="h-5 w-5 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5m7-2a8 8 0 11-3.2-6.4L21 4l-1.2 4.2A7.96 7.96 0 0120 12z"/></svg>
                        </span>
                        <h2 class="mt-4 font-display text-xl text-ink-900">Merhaba {{ auth()->user()->name }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-ink-500">
                            Zaten giriş yaptınız — bilgilerinizi tekrar girmenize gerek yok.
                            Mesajlarınızı panelinizden yazın; yanıtlarımız aynı ekranda anında görünür.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('panel.messages.store') }}" class="mt-6 space-y-5">
                        @csrf
                        <div>
                            <x-input-label :value="'Konu'" />
                            <x-text-input name="subject" value="{{ old('subject') }}" required maxlength="150" placeholder="Örn. Paket yükseltme" />
                            <x-input-error :messages="$errors->get('subject')" />
                        </div>
                        <div>
                            <x-input-label :value="'Mesajınız'" />
                            <textarea name="body" rows="5" class="field" required maxlength="5000">{{ old('body') }}</textarea>
                            <x-input-error :messages="$errors->get('body')" />
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <button class="btn-primary">Mesajı gönder</button>
                            <a href="{{ route('panel.messages.index') }}" class="btn-ghost">Mesajlarım →</a>
                        </div>
                    </form>
                @else
                    <form method="POST" action="{{ route('contact') }}" class="space-y-5">
                        @csrf

                        {{-- Bot tuzağı: gerçek kullanıcı bu alanı görmez --}}
                        <div class="hidden" aria-hidden="true">
                            <label>Web sitesi <input type="text" name="website_url" tabindex="-1" autocomplete="off"></label>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-input-label :value="'Ad Soyad'" />
                                <x-text-input name="name" value="{{ old('name') }}" required maxlength="120" />
                                <x-input-error :messages="$errors->get('name')" />
                            </div>
                            <div>
                                <x-input-label :value="'Telefon (opsiyonel)'" />
                                <x-text-input name="phone" value="{{ old('phone') }}" maxlength="40" />
                                <x-input-error :messages="$errors->get('phone')" />
                            </div>
                        </div>
                        <div>
                            <x-input-label :value="'E-posta'" />
                            <x-text-input type="email" name="email" value="{{ old('email') }}" required maxlength="180" />
                            <x-input-error :messages="$errors->get('email')" />
                        </div>
                        <div>
                            <x-input-label :value="'Konu (opsiyonel)'" />
                            <x-text-input name="subject" value="{{ old('subject') }}" maxlength="150" />
                            <x-input-error :messages="$errors->get('subject')" />
                        </div>
                        <div>
                            <x-input-label :value="'Mesajınız'" />
                            <textarea name="message" rows="5" class="field" required minlength="10" maxlength="2000">{{ old('message') }}</textarea>
                            <x-input-error :messages="$errors->get('message')" />
                        </div>
                        <button class="btn-primary">Gönder</button>

                        <p class="text-xs leading-relaxed text-ink-400">
                            Gönderdiğiniz bilgiler yalnızca talebinize dönüş yapmak için kullanılır.
                            Hesabınız varsa <a href="{{ route('login') }}" class="font-semibold text-gold-700 hover:underline">giriş yapıp</a>
                            panelden yazarak yazışma geçmişinizi tek yerde tutabilirsiniz.
                        </p>
                    </form>
                @endauth
            </div>
        </div>
    </section>
</x-marketing-layout>
