@php
    $root = config('neva.root_domain');
    $full = $label.'.'.$root;

    // Sayfa 404 durum koduyla dönüyor (bkz. ResolveTenant::claimResponse).
    // Görsel olarak satış ekranı, teknik olarak "burada içerik yok" — ikisi
    // birbiriyle çelişmiyor: adres gerçekten boş.
    $headline = $available
        ? 'Bu adres boşta'
        : 'Bu adrese ait yayında menü yok';

    $lead = $available
        ? 'Kimse almamış. İşletmenizin QR menüsü burada yayınlanabilir.'
        : 'Adres yanlış yazılmış olabilir ya da işletme menüsünü henüz yayına almamış olabilir.';
@endphp

<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Sahipsiz alt domainler dizine GİRMEMELİ: wildcard yüzünden sonsuz sayıdalar. --}}
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $full }} — {{ config('neva.brand.name') }}</title>
    <link rel="icon" href="{{ asset('img/nevalogo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-ink-950 text-white antialiased">

    <div class="mx-auto flex min-h-screen max-w-2xl flex-col justify-center px-6 py-16">

        <a href="{{ config('app.url') }}" class="mb-10 inline-flex">
            <img src="{{ asset('img/nevalogo.png') }}" alt="{{ config('neva.brand.name') }}" class="h-8 w-auto">
        </a>

        {{-- Aranan adres — ziyaretçi hangi adrese geldiğini görsün --}}
        <div class="inline-flex w-fit items-center gap-2.5 rounded-full bg-white/5 px-4 py-2 text-sm ring-1 ring-white/10">
            <span class="h-2 w-2 shrink-0 rounded-full {{ $available ? 'bg-emerald-400' : 'bg-ink-500' }}"></span>
            <span class="font-mono text-ink-200">{{ $full }}</span>
        </div>

        <h1 class="mt-6 font-display text-4xl leading-tight sm:text-5xl">
            {{ $headline }}
            @if ($available)
                <span class="text-gold-400">.</span>
            @endif
        </h1>

        <p class="mt-5 max-w-lg text-lg leading-relaxed text-ink-300">{{ $lead }}</p>

        @if ($available)
            <p class="mt-4 max-w-lg leading-relaxed text-ink-400">
                Neva-QR ile restoran ve kafeler menülerini kendi markalı adreslerinde yayınlar:
                40 hazır tasarımdan birini seçersiniz, ürün ve fiyat değişiklikleri anında canlıya yansır,
                masalarınıza QR kodu bastırırsınız.
            </p>

            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ config('app.url') }}/register?alan={{ urlencode($label) }}" class="btn-gold px-7 py-3 text-base">
                    “{{ $label }}” adresini al
                </a>
                <a href="{{ config('app.url') }}/qr-menu-sablonlari"
                   class="rounded-xl bg-white/10 px-7 py-3 text-sm font-semibold text-white ring-1 ring-white/15 transition hover:bg-white/15">
                    40 tasarımı gör
                </a>
            </div>

            <p class="mt-4 text-xs text-ink-500">
                Adres kaydınız onaylandığında ayrılır. Şu an kimseye tahsis edilmemiştir.
            </p>
        @else
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ config('app.url') }}" class="btn-gold px-7 py-3 text-base">Neva-QR nedir?</a>
                <a href="{{ config('app.url') }}/qr-menu-sablonlari"
                   class="rounded-xl bg-white/10 px-7 py-3 text-sm font-semibold text-white ring-1 ring-white/15 transition hover:bg-white/15">
                    Şablonlar
                </a>
            </div>
        @endif

        {{-- Ürün özeti: ziyaretçi buraya bir QR okutup geldiyse ne olduğunu anlasın --}}
        <dl class="mt-14 grid gap-x-8 gap-y-6 border-t border-white/10 pt-8 sm:grid-cols-3">
            @foreach ([
                ['40 tasarım', 'Hepsi dahil, istediğiniz zaman değiştirin'],
                ['Anında güncelleme', 'Fiyat değişince QR aynı kalır'],
                ['Masaya özel QR', 'Hangi masa ne sıklıkta açtı, görün'],
            ] as [$t, $d])
                <div>
                    <dt class="font-display text-lg text-white">{{ $t }}</dt>
                    <dd class="mt-1 text-sm leading-relaxed text-ink-400">{{ $d }}</dd>
                </div>
            @endforeach
        </dl>

        <p class="mt-12 text-xs text-ink-500">
            <a href="{{ config('app.url') }}" class="hover:text-ink-300">{{ $root }}</a>
            <span class="mx-2">·</span>
            <a href="{{ config('app.url') }}/iletisim" class="hover:text-ink-300">İletişim</a>
        </p>
    </div>
</body>
</html>
