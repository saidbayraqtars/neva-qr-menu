<x-app-layout title="İstatistik">
    <x-slot name="header">İstatistik</x-slot>

    @php
        $max = max(1, collect($series)->max('views'));
    @endphp

    <div class="mx-auto max-w-4xl space-y-6">
        {{-- Özet kartlar --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                ['Bugün', $today],
                ['Son 7 gün', $last7],
                ['Son 30 gün', $last30],
                ['Toplam', $allTime],
            ] as [$label, $value])
                <div class="card p-4">
                    <p class="text-xs uppercase tracking-wide text-ink-400">{{ $label }}</p>
                    <p class="mt-1 font-display text-2xl text-ink-900">{{ number_format($value, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-ink-400">görüntülenme</p>
                </div>
            @endforeach
        </div>

        {{-- Günlük grafik --}}
        <div class="card p-6">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 class="font-display text-lg text-ink-900">Son {{ $rangeDays }} gün</h2>
                <p class="text-xs text-ink-500">
                    {{ number_format($visitors30, 0, ',', '.') }} farklı cihaz · en yoğun gün {{ number_format($max, 0, ',', '.') }} görüntülenme
                </p>
            </div>

            @if ($last30 === 0)
                <p class="mt-6 rounded-xl bg-ink-50 px-4 py-6 text-center text-sm text-ink-500 ring-1 ring-ink-100">
                    Henüz veri yok. Menünüz yayına girip QR okutulduğunda rakamlar burada birikmeye başlar.
                </p>
            @else
                <div class="mt-6 flex h-40 items-end gap-[3px]">
                    @foreach ($series as $point)
                        <div class="group relative flex-1"
                             style="height: {{ max(2, (int) round($point['views'] / $max * 100)) }}%">
                            <div class="h-full w-full rounded-t bg-gold-400/70 transition group-hover:bg-gold-500"></div>
                            <span class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 whitespace-nowrap rounded-lg bg-ink-900 px-2 py-1 text-[11px] text-white group-hover:block">
                                {{ $point['date']->format('d.m') }} · {{ $point['views'] }} görüntülenme
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-[11px] text-ink-400">
                    <span>{{ $series[0]['date']->format('d.m.Y') }}</span>
                    <span>{{ $series[count($series) - 1]['date']->format('d.m.Y') }}</span>
                </div>
            @endif
        </div>

        {{-- Masa kırılımı --}}
        <div class="card p-6">
            <h2 class="font-display text-lg text-ink-900">Masalara göre ({{ $rangeDays }} gün)</h2>
            <p class="mt-1 text-sm text-ink-500">
                QR linkindeki <code class="rounded bg-ink-100 px-1">?masa=</code> etiketine göre gruplanır.
            </p>

            @if ($tables->isEmpty())
                <p class="mt-4 rounded-xl bg-ink-50 px-4 py-4 text-sm text-ink-500 ring-1 ring-ink-100">
                    Masa etiketli okutma yok. QR ekranından masa linklerini oluşturup masalara koyabilirsiniz.
                </p>
            @else
                @php $tableMax = max(1, (int) $tables->max('views')); @endphp
                <ul class="mt-4 space-y-2">
                    @foreach ($tables as $row)
                        <li class="flex items-center gap-3">
                            <span class="w-28 shrink-0 truncate text-sm text-ink-700">{{ $row->table_label }}</span>
                            <span class="h-2 rounded-full bg-gold-400" style="width: {{ max(4, (int) round($row->views / $tableMax * 70)) }}%"></span>
                            <span class="text-xs text-ink-500">{{ number_format((int) $row->views, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            <p class="mt-5 text-xs text-ink-400">
                Kişisel veri toplanmaz: IP, konum veya cihaz kimliği saklanmaz; yalnızca gün ve masa bazlı sayaç tutulur.
            </p>
        </div>
    </div>
</x-app-layout>
