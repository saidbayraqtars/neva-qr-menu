<x-guest-layout>
    <div x-data="{ copied: false }">
        <div class="text-center">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-emerald-100">
                <svg class="h-7 w-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </span>
            <h2 class="mt-5 font-display text-2xl text-ink-900">Başvurunuz alındı</h2>
            <p class="mt-2 text-sm leading-relaxed text-ink-500">
                <strong>{{ $membershipRequest->business_name }}</strong> için başvurunuzu aldık.
                Ödemeniz hesabımıza geçtiğinde hesabınızı açıp
                <strong>şifre belirleme bağlantısını</strong> {{ $membershipRequest->email }} adresine göndereceğiz.
            </p>
        </div>

        {{-- Sipariş özeti --}}
        <div class="mt-6 space-y-2 rounded-2xl bg-ink-50 p-5 text-left text-sm ring-1 ring-ink-100">
            <div class="flex justify-between"><span class="text-ink-500">Paket</span><span class="font-semibold text-ink-900">{{ $membershipRequest->plan?->name ?? '—' }}</span></div>
            @if ($membershipRequest->table_count)
                <div class="flex justify-between"><span class="text-ink-500">Masa sayısı</span><span class="font-semibold text-ink-900">{{ $membershipRequest->table_count }}</span></div>
            @endif
            <div class="flex justify-between border-t border-ink-200 pt-2"><span class="text-ink-500">Tutar</span><span class="font-display text-base text-ink-900">{{ money($membershipRequest->amount, 'TRY') }}</span></div>
            <div class="flex justify-between"><span class="text-ink-500">Durum</span><span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Ödeme bekleniyor</span></div>
        </div>

        {{-- Havale / EFT talimatı --}}
        <div class="mt-4 rounded-2xl border border-gold-200 bg-gold-50/60 p-5 text-left">
            <h3 class="font-display text-base text-ink-900">Havale / EFT ile ödeme</h3>
            <p class="mt-1 text-xs leading-relaxed text-ink-500">
                Aşağıdaki hesaba ödemenizi yaptıktan sonra bize haber vermenize gerek yok —
                ödeme kontrol edildiğinde hesabınız otomatik olarak açılır.
                <strong>Açıklama kısmına referans kodunuzu yazmayı unutmayın.</strong>
            </p>

            @php $accounts = \App\Support\PaymentAccounts::active(); @endphp

            @forelse ($accounts as $account)
                <dl @class(['mt-4 space-y-2.5 text-sm', 'border-t border-gold-200 pt-4' => ! $loop->first])>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="shrink-0 text-ink-500">Banka</dt>
                        <dd class="text-right font-semibold text-ink-900">
                            {{ $account->bank_name }}
                            @if ($account->note)
                                <span class="block text-xs font-normal text-ink-400">{{ $account->note }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="shrink-0 text-ink-500">Alıcı</dt>
                        <dd class="text-right font-semibold text-ink-900">{{ $account->account_name }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="shrink-0 text-ink-500">IBAN</dt>
                        <dd class="text-right font-mono text-[13px] font-semibold tracking-tight text-ink-900">{{ $account->formatted_iban }}</dd>
                    </div>
                </dl>
            @empty
                {{-- Hesap tanımlı değil: uydurma bir IBAN göstermektense dürüst ol. --}}
                <p class="mt-4 rounded-xl bg-white px-4 py-3 text-sm text-ink-600 ring-1 ring-gold-200">
                    Ödeme bilgileri en kısa sürede e-posta ile iletilecektir.
                </p>
            @endforelse

            @if ($accounts->count() > 1)
                <p class="mt-3 text-xs text-ink-500">Hesaplardan herhangi birine ödeme yapabilirsiniz.</p>
            @endif

            <div class="mt-4 rounded-xl bg-white p-4 ring-1 ring-gold-200">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Havale açıklaması</p>
                <div class="mt-1 flex items-center justify-between gap-3">
                    <p class="font-mono text-lg font-bold tracking-wide text-ink-900">{{ $membershipRequest->reference_code }}</p>
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ $membershipRequest->reference_code }}'); copied = true; setTimeout(() => copied = false, 1500)"
                            class="btn-ghost shrink-0 px-3 py-1.5 text-xs">
                        <span x-show="!copied">Kopyala</span>
                        <span x-show="copied" x-cloak>Kopyalandı ✓</span>
                    </button>
                </div>
            </div>

            <p class="mt-3 text-xs text-ink-400">
                Bu bilgileri e-posta ile de gönderdik. Soru için
                <a href="{{ route('contact') }}" class="font-semibold text-gold-700 hover:underline">iletişim</a>
                sayfasından bize ulaşabilirsiniz.
            </p>
        </div>

        <a href="{{ url('/') }}" class="btn-ghost mt-6 w-full">Ana sayfaya dön</a>
    </div>
</x-guest-layout>
