@php
    use App\Support\ServerHealth as H;

    $mem = $health['memory'];
    $disk = $health['disk'];
    $load = $health['load'];
    $db = $health['database'];
    $up = $health['uploads'];
    $q = $health['queue'];
    $backup = $health['backup'];

    /*
     * Eşikler — "ne zaman büyütmeliyim" sorusunun cevabı.
     * RAM'de sinyal MemAvailable: 500 MB'ın altına inmeye başladıysa menü PDF
     * üretimi (headless Chrome, istek başına ~350 MB) sıkışmaya başlar.
     */
    $tone = function (bool $bad, bool $warn = false) {
        return $bad ? 'text-red-600' : ($warn ? 'text-amber-600' : 'text-emerald-600');
    };
    $bar = function (?int $percent, bool $bad, bool $warn) {
        $color = $bad ? 'bg-red-500' : ($warn ? 'bg-amber-500' : 'bg-emerald-500');

        return '<div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-ink-100">'
            .'<div class="h-full '.$color.'" style="width: '.max(2, (int) $percent).'%"></div></div>';
    };

    $memLow = $mem && $mem['available'] < 400 * 1024 * 1024;
    $memWarn = $mem && $mem['available'] < 700 * 1024 * 1024;
    $diskLow = $disk && $disk['percent'] >= 90;
    $diskWarn = $disk && $disk['percent'] >= 75;
@endphp

<x-app-layout title="Sistem">
    <x-slot name="header">Sistem Durumu</x-slot>

    <div class="mx-auto max-w-5xl space-y-6">

        {{-- Dikkat gerektirenler en üstte: admin sayfayı açar açmaz görsün --}}
        @php
            $alerts = array_values(array_filter([
                $memLow ? 'Kullanılabilir RAM 400 MB altında — menü PDF üretimi sıkışabilir. Sunucuyu 4 GB\'a çıkarmayı düşünün.' : null,
                $diskLow ? 'Disk %90 üzerinde dolu.' : null,
                ($q['stalled'] ?? false) ? 'Kuyrukta 5 dakikadan eski bekleyen iş var — işçi çalışmıyor olabilir (nevaqr-queue).' : null,
                ($q['failed'] ?? 0) > 0 ? $q['failed'].' başarısız kuyruk işi var.' : null,
                ($backup && ! ($backup['exists'] ?? false)) ? 'Hiç yedek alınmamış.' : null,
                ($backup['stale'] ?? false) ? 'Son yedek 36 saatten eski — zamanlanmış iş çalışmıyor olabilir.' : null,
                ($db['journal'] ?? null) !== null && strtolower($db['journal']) !== 'wal'
                    ? 'SQLite WAL modunda değil — yazmalar okuyucuları kilitler.' : null,
            ]));
        @endphp

        @if ($alerts)
            <div class="card border-l-4 border-amber-400 p-6">
                <h3 class="font-display text-lg text-ink-900">Dikkat</h3>
                <ul class="mt-3 space-y-2 text-sm text-ink-700">
                    @foreach ($alerts as $a)
                        <li class="flex gap-2.5">
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"></span>
                            <span>{{ $a }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="card border-l-4 border-emerald-400 px-6 py-4">
                <p class="text-sm font-medium text-emerald-700">Her şey normal görünüyor.</p>
            </div>
        @endif

        {{-- Kaynaklar --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Bellek</p>
                @if ($mem)
                    <p class="mt-2 font-display text-2xl {{ $tone($memLow, $memWarn) }}">{{ H::bytes($mem['available']) }}</p>
                    <p class="mt-0.5 text-xs text-ink-500">{{ H::bytes($mem['total']) }} içinde kullanılabilir</p>
                    {!! $bar($mem['percent'], $memLow, $memWarn) !!}
                @else
                    <p class="mt-2 font-display text-2xl text-ink-300">—</p>
                    <p class="mt-0.5 text-xs text-ink-400">bu sistemde okunamıyor</p>
                @endif
            </div>

            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Disk</p>
                @if ($disk)
                    <p class="mt-2 font-display text-2xl {{ $tone($diskLow, $diskWarn) }}">{{ H::bytes($disk['free']) }}</p>
                    <p class="mt-0.5 text-xs text-ink-500">{{ H::bytes($disk['total']) }} içinde boş (%{{ $disk['percent'] }} dolu)</p>
                    {!! $bar($disk['percent'], $diskLow, $diskWarn) !!}
                @else
                    <p class="mt-2 font-display text-2xl text-ink-300">—</p>
                @endif
            </div>

            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">İşlemci yükü</p>
                @if ($load)
                    <p class="mt-2 font-display text-2xl {{ $tone(($load['per_core'] ?? 0) > 1.5, ($load['per_core'] ?? 0) > 0.9) }}">
                        {{ $load['avg'][0] }}
                    </p>
                    <p class="mt-0.5 text-xs text-ink-500">
                        {{ $load['cores'] ?? '?' }} çekirdek ·
                        5dk {{ $load['avg'][1] }} · 15dk {{ $load['avg'][2] }}
                    </p>
                    <p class="mt-3 text-xs text-ink-400">
                        Çekirdek başına {{ $load['per_core'] ?? '—' }} — 1'in üstü sürekli kalırsa sıra oluşuyor.
                    </p>
                @else
                    <p class="mt-2 font-display text-2xl text-ink-300">—</p>
                @endif
            </div>
        </div>

        {{-- Uygulama tarafı — barındırma panelinin göremediği kısım --}}
        <div class="card p-6">
            <h3 class="font-display text-lg text-ink-900">Uygulama</h3>

            <dl class="mt-5 grid gap-x-8 gap-y-5 sm:grid-cols-2">
                <div class="flex items-baseline justify-between gap-4 border-b border-ink-100 pb-3">
                    <dt class="text-sm text-ink-500">Veritabanı</dt>
                    <dd class="text-right text-sm font-medium text-ink-900">
                        {{ $db['driver'] }}
                        @if ($db['size']) · {{ H::bytes($db['size']) }} @endif
                        @if ($db['journal'])
                            <span class="ml-1 rounded px-1.5 py-0.5 text-[11px] {{ strtolower($db['journal']) === 'wal' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                {{ strtoupper($db['journal']) }}
                            </span>
                        @endif
                    </dd>
                </div>

                <div class="flex items-baseline justify-between gap-4 border-b border-ink-100 pb-3">
                    <dt class="text-sm text-ink-500">Yüklenen görseller</dt>
                    <dd class="text-right text-sm font-medium text-ink-900">
                        {{ $up ? H::bytes($up['bytes']).' · '.number_format($up['files'], 0, ',', '.').' dosya' : '—' }}
                    </dd>
                </div>

                <div class="flex items-baseline justify-between gap-4 border-b border-ink-100 pb-3">
                    <dt class="text-sm text-ink-500">Kuyruk</dt>
                    <dd class="text-right text-sm font-medium {{ ($q['stalled'] ?? false) || ($q['failed'] ?? 0) > 0 ? 'text-red-600' : 'text-ink-900' }}">
                        {{ $q['connection'] }} · {{ $q['pending'] ?? '—' }} bekleyen
                        @if (($q['failed'] ?? 0) > 0) · {{ $q['failed'] }} başarısız @endif
                    </dd>
                </div>

                <div class="flex items-baseline justify-between gap-4 border-b border-ink-100 pb-3">
                    <dt class="text-sm text-ink-500">Görüntülenme kaydı</dt>
                    <dd class="text-right text-sm font-medium text-ink-900">
                        {{ $db['visit_rows'] !== null ? number_format($db['visit_rows'], 0, ',', '.').' satır' : '—' }}
                    </dd>
                </div>

                <div class="flex items-baseline justify-between gap-4 border-b border-ink-100 pb-3">
                    <dt class="text-sm text-ink-500">Son yedek</dt>
                    <dd class="text-right text-sm font-medium {{ ($backup['stale'] ?? false) || ! ($backup['exists'] ?? false) ? 'text-amber-600' : 'text-ink-900' }}">
                        @if ($backup && ($backup['exists'] ?? false))
                            {{ $backup['at']->diffForHumans() }} · {{ H::bytes($backup['size']) }}
                            <span class="text-ink-400">({{ $backup['count'] }} arşiv)</span>
                        @else
                            alınmamış
                        @endif
                    </dd>
                </div>

                <div class="flex items-baseline justify-between gap-4 border-b border-ink-100 pb-3">
                    <dt class="text-sm text-ink-500">PHP</dt>
                    <dd class="text-right text-sm font-medium text-ink-900">
                        {{ $health['php']['version'] }}
                        @if ($health['php']['opcache'])
                            <span class="ml-1 rounded bg-emerald-50 px-1.5 py-0.5 text-[11px] text-emerald-700">
                                OPcache %{{ $health['php']['opcache']['hit_rate'] }}
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>

            <p class="mt-6 text-xs leading-relaxed text-ink-400">
                Görsel boyutu 5 dakikada bir yenilenir. Bu sayfa sunucuda hiçbir kabuk komutu
                çalıştırmaz; veriler doğrudan sistem dosyalarından ve veritabanından okunur.
            </p>
        </div>
    </div>
</x-app-layout>
