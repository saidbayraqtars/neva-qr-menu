<x-app-layout title="Admin">
    <x-slot name="header">Platform Genel Bakış</x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
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
