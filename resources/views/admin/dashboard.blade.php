<x-app-layout title="Admin">
    <x-slot name="header">Platform Genel Bakış</x-slot>

    <div class="mx-auto max-w-5xl space-y-6">

        {{-- Aksiyon bekleyenler --}}
        @php
            $actions = array_values(array_filter([
                $todo['paid_waiting'] > 0
                    ? ['Ödemesi gelmiş, onay bekleyen üyelik', $todo['paid_waiting'], route('admin.memberships.index'), 'bg-emerald-50 text-emerald-700 ring-emerald-200']
                    : null,
                $todo['memberships'] > 0
                    ? ['Bekleyen üyelik başvurusu', $todo['memberships'], route('admin.memberships.index'), 'bg-amber-50 text-amber-700 ring-amber-200']
                    : null,
                $stats['pending'] > 0
                    ? ['Onay bekleyen alt domain', $stats['pending'], route('admin.requests.index'), 'bg-amber-50 text-amber-700 ring-amber-200']
                    : null,
                $todo['unread_messages'] > 0
                    ? ['Okunmamış destek mesajı', $todo['unread_messages'], route('admin.messages.index'), 'bg-red-50 text-red-700 ring-red-200']
                    : null,
                $todo['new_contacts'] > 0
                    ? ['Yeni iletişim formu', $todo['new_contacts'], route('admin.contact.index'), 'bg-ink-50 text-ink-700 ring-ink-200']
                    : null,
            ]));
        @endphp

        @if ($actions)
            <div class="card p-6">
                <h3 class="font-display text-lg text-ink-900">Sizi bekleyenler</h3>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($actions as [$label, $count, $href, $classes])
                        <a href="{{ $href }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold ring-1 transition hover:-translate-y-0.5 {{ $classes }}">
                            <span class="grid h-7 min-w-7 place-items-center rounded-full bg-white/70 px-2 text-xs font-bold">{{ $count }}</span>
                            <span class="flex-1">{{ $label }}</span>
                            <span>→</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Yayın sorunları --}}
        @if ($broken->isNotEmpty())
            <div class="card border border-red-200 bg-red-50/60 p-6">
                <h3 class="font-display text-lg text-red-800">Doğrulaması başarısız alt domainler</h3>
                <p class="mt-1 text-sm text-red-700">
                    Bu adresler yayında görünüyor ama otomatik kontrol açılmadıklarını söylüyor.
                    DNS veya sunucu yapılandırmasını kontrol edin.
                </p>
                <ul class="mt-4 space-y-2">
                    @foreach ($broken as $restaurant)
                        <li class="rounded-xl bg-white px-4 py-3 text-sm ring-1 ring-red-100">
                            <span class="font-mono font-semibold text-ink-900">{{ $restaurant->subdomain }}.{{ config('neva.root_domain') }}</span>
                            <span class="text-ink-500">· {{ $restaurant->name }}</span>
                            <p class="mt-1 text-xs text-red-600">{{ $restaurant->publish_error }}</p>
                            @if ($restaurant->last_health_check_at)
                                <p class="text-[11px] text-ink-400">Son kontrol: {{ $restaurant->last_health_check_at->diffForHumans() }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 text-xs text-red-700">
                    Elle yeniden denemek için: <code class="rounded bg-white px-1.5 py-0.5 font-mono">php artisan neva:health-check</code>
                </p>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach ([
                ['Bekleyen talep', $stats['pending'], 'text-amber-600'],
                ['Yayında menü', $stats['live'], 'text-emerald-600'],
                ['Restoran sahibi', $stats['owners'], 'text-ink-900'],
                ['Taslak', $stats['drafts'], 'text-ink-400'],
            ] as [$label, $value, $color])
                <div class="card p-5">
                    <p class="font-display text-3xl {{ $color }}">{{ $value }}</p>
                    <p class="mt-1 text-xs font-medium uppercase tracking-wide text-ink-500">{{ $label }}</p>
                </div>
            @endforeach
        </div>

        <div class="card p-6">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg text-ink-900">Son talepler</h3>
                <a href="{{ route('admin.requests.index') }}" class="text-sm font-medium text-gold-700 hover:underline">Tümü →</a>
            </div>
            <ul class="mt-4 divide-y divide-ink-100">
                @forelse ($recent as $req)
                    <li class="flex items-center gap-3 py-3">
                        <span class="font-mono text-sm text-ink-900">{{ $req->requested_subdomain }}</span>
                        <span class="text-xs text-ink-400">{{ $req->restaurant?->name ?? '—' }}</span>
                        <span class="ml-auto text-xs {{ ['pending' => 'text-amber-600', 'approved' => 'text-emerald-600', 'rejected' => 'text-red-500'][$req->status] }}">
                            {{ ['pending' => 'Bekliyor', 'approved' => 'Onaylı', 'rejected' => 'Reddedildi'][$req->status] }}
                        </span>
                    </li>
                @empty
                    <li class="py-8 text-center text-sm text-ink-400">Henüz talep yok.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
