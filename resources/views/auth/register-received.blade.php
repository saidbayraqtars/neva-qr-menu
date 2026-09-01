<x-guest-layout>
    <div class="text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-emerald-100">
            <svg class="h-7 w-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </span>
        <h2 class="mt-5 font-display text-2xl text-ink-900">Başvurunuz alındı</h2>
        <p class="mt-2 text-sm leading-relaxed text-ink-500">
            <strong>{{ $membershipRequest->business_name }}</strong> için başvurunuz ekibimize iletildi.
            Ödeme onayının ardından hesabınız aktif edilecek ve
            <strong>geçici şifreniz</strong> {{ $membershipRequest->email }} adresine / telefonunuza iletilecektir.
        </p>

        <div class="mt-6 space-y-2 rounded-2xl bg-ink-50 p-5 text-left text-sm ring-1 ring-ink-100">
            <div class="flex justify-between"><span class="text-ink-500">Paket</span><span class="font-semibold text-ink-900">{{ $membershipRequest->plan?->name ?? '—' }}</span></div>
            @if ($membershipRequest->table_count)
                <div class="flex justify-between"><span class="text-ink-500">Masa sayısı</span><span class="font-semibold text-ink-900">{{ $membershipRequest->table_count }}</span></div>
            @endif
            <div class="flex justify-between border-t border-ink-200 pt-2"><span class="text-ink-500">Tutar</span><span class="font-display text-base text-ink-900">{{ money($membershipRequest->amount, 'TRY') }}</span></div>
            <div class="flex justify-between"><span class="text-ink-500">Durum</span><span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Onay bekleniyor</span></div>
        </div>

        <a href="{{ url('/') }}" class="btn-ghost mt-6 w-full">Ana sayfaya dön</a>
    </div>
</x-guest-layout>
