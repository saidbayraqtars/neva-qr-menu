<x-guest-layout>
    @php
        $planData = $plans->mapWithKeys(fn ($p) => [$p->id => [
            'name' => $p->name,
            'price' => (float) $p->price,
            'hasTables' => $p->hasTablePricing(),
            'limit' => (int) $p->setup_table_limit,
            'extra' => (float) $p->extra_table_price,
        ]])->all();
        $startStep = ($errors->any() && old('plan_id')) ? 2 : 1;
    @endphp

    <div x-data="{
            step: {{ $startStep }},
            plan: @js((int) old('plan_id')) || null,
            tables: @js((int) (old('table_count') ?: 15)),
            plans: @js($planData),
            get p() { return this.plan ? this.plans[this.plan] : null; },
            get total() {
                if (!this.p) return 0;
                let t = this.p.price;
                if (this.p.hasTables) t += Math.max(0, (this.tables || 0) - this.p.limit) * this.p.extra;
                return t;
            },
            fmt(n) { return new Intl.NumberFormat('tr-TR').format(Math.round(n)) + ' ₺'; },
         }">

        <div class="mb-6">
            <div class="mb-4 flex items-center gap-2">
                <span class="h-1.5 flex-1 rounded-full transition-colors" :class="step >= 1 ? 'bg-ink-900' : 'bg-ink-200'"></span>
                <span class="h-1.5 flex-1 rounded-full transition-colors" :class="step >= 2 ? 'bg-ink-900' : 'bg-ink-200'"></span>
            </div>
            <h2 class="font-display text-2xl text-ink-900">Neva'ya başvurun</h2>
            <p class="mt-1 text-sm text-ink-500" x-text="step === 1 ? 'Adım 1 / 2 · İşletme ve iletişim bilgileri' : 'Adım 2 / 2 · Paket seçimi'"></p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            {{-- ADIM 1 --}}
            <div x-show="step === 1" class="space-y-5">
                <div>
                    <x-input-label for="business_name" :value="'İşletme adı'" />
                    <x-text-input id="business_name" type="text" name="business_name" :value="old('business_name')" required autofocus placeholder="Lumina Bistro & Lounge" />
                    <x-input-error :messages="$errors->get('business_name')" />
                </div>
                <div>
                    <x-input-label for="name" :value="'Yetkili ad soyad'" />
                    <x-text-input id="name" type="text" name="name" :value="old('name')" required autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="email" :value="'E-posta'" />
                    <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="email" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>
                <div>
                    <x-input-label for="phone" :value="'Telefon (opsiyonel)'" />
                    <x-text-input id="phone" type="tel" name="phone" :value="old('phone')" placeholder="0555 000 00 00" />
                    <x-input-error :messages="$errors->get('phone')" />
                </div>

                <button type="button" @click="step = 2" class="btn-primary w-full">Devam et →</button>
            </div>

            {{-- ADIM 2 --}}
            <div x-show="step === 2" x-cloak class="space-y-4">
                <input type="hidden" name="plan_id" :value="plan">

                <div class="space-y-3">
                    @foreach ($plans as $planRow)
                        <label class="block cursor-pointer rounded-2xl border p-4 transition"
                               :class="plan === {{ $planRow->id }} ? 'border-gold-500 bg-gold-500/5 ring-1 ring-gold-500' : 'border-ink-200 hover:border-ink-300'">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <input type="radio" name="_plan_radio" value="{{ $planRow->id }}" @click="plan = {{ $planRow->id }}" class="sr-only">
                                    <p class="font-semibold text-ink-900">{{ $planRow->name }}</p>
                                    @if ($planRow->tagline)<p class="mt-0.5 text-xs text-ink-500">{{ $planRow->tagline }}</p>@endif
                                </div>
                                <p class="shrink-0 font-display text-lg text-ink-900">{{ money($planRow->price, 'TRY') }}</p>
                            </div>
                            @if ($planRow->hasTablePricing())
                                <p class="mt-2 text-xs text-ink-500">{{ $planRow->setup_table_limit }} masaya kadar. Sonraki her masa +{{ money($planRow->extra_table_price, 'TRY') }}</p>
                            @endif
                        </label>
                    @endforeach
                    <x-input-error :messages="$errors->get('plan_id')" />
                </div>

                {{-- Masa sayısı — yalnızca fiziksel QR paketinde --}}
                <div x-show="p && p.hasTables" x-cloak class="rounded-xl bg-ink-50 p-4 ring-1 ring-ink-100">
                    <x-input-label for="table_count" :value="'Toplam masa sayısı'" />
                    <input id="table_count" type="number" name="table_count" x-model.number="tables" min="1" max="2000" class="field mt-1 w-32">
                    <p class="mt-3 flex items-center justify-between text-sm">
                        <span class="text-ink-500">Tahmini toplam</span>
                        <span class="font-display text-lg text-ink-900" x-text="fmt(total)"></span>
                    </p>
                    <x-input-error :messages="$errors->get('table_count')" />
                </div>

                <div class="rounded-xl bg-ink-900 px-4 py-3 text-sm text-white">
                    Başvurunuz ekibimize iletilir. Ödeme onaylandığında hesabınız açılır ve
                    <strong>geçici şifreniz</strong> size iletilir.
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="step = 1" class="btn-ghost">← Geri</button>
                    <button type="submit" class="btn-primary flex-1" :class="!plan && 'opacity-50 pointer-events-none'">Başvuruyu gönder</button>
                </div>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-ink-500">
            Zaten hesabınız var mı?
            <a href="{{ route('login') }}" class="font-semibold text-ink-900 hover:text-gold-600">Giriş yapın</a>
        </p>
    </div>
</x-guest-layout>
