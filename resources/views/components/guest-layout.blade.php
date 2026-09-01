<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('neva.brand.name') }}</title>
    <link rel="icon" href="{{ asset('img/nevalogo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-ink-950 antialiased">
    <div class="flex min-h-full">
        {{-- Sol: marka paneli --}}
        <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-ink-950 p-12 lg:flex">
            <div class="pointer-events-none absolute -right-40 -top-40 h-96 w-96 rounded-full bg-gold-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-gold-700/10 blur-3xl"></div>

            <a href="{{ url('/') }}" class="relative flex items-center gap-3">
                <img src="{{ asset('img/nevalogo.png') }}" alt="Neva" class="h-10 w-auto brightness-0 invert">
            </a>

            <div class="relative max-w-md">
                <h1 class="font-display text-4xl leading-tight text-white">
                    Menünüz artık bir <span class="text-gold-400">deneyim.</span>
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-ink-300">
                    QR kod ile açılan, markanıza özel tasarlanmış dijital menüler.
                    Kafe ve restoran şablonları, canlı önizleme, tek tıkla QR ve PDF çıktısı.
                </p>
            </div>

            <p class="relative text-xs text-ink-400">&copy; {{ date('Y') }} {{ config('neva.brand.name') }}</p>
        </div>

        {{-- Sağ: form --}}
        <div class="flex w-full items-center justify-center bg-ink-50 px-6 py-12 lg:w-1/2">
            <div class="w-full max-w-md">
                <a href="{{ url('/') }}" class="mb-8 flex items-center gap-3 lg:hidden">
                    <img src="{{ asset('img/nevalogo.png') }}" alt="Neva" class="h-9 w-auto">
                </a>

                <div class="card p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
