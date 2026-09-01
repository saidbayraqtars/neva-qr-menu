<x-marketing-layout title="İletişim">
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

                <form method="POST" action="{{ route('contact') }}" class="space-y-5">
                    @csrf
                    <div>
                        <x-input-label :value="'Ad Soyad'" />
                        <x-text-input name="name" value="{{ old('name') }}" required />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label :value="'E-posta'" />
                        <x-text-input type="email" name="email" value="{{ old('email') }}" required />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>
                    <div>
                        <x-input-label :value="'Mesajınız'" />
                        <textarea name="message" rows="5" class="field" required>{{ old('message') }}</textarea>
                        <x-input-error :messages="$errors->get('message')" />
                    </div>
                    <button class="btn-primary">Gönder</button>
                </form>
            </div>
        </div>
    </section>
</x-marketing-layout>
