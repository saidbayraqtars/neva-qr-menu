@props(['title' => null, 'dark' => false])

<!DOCTYPE html>
<html lang="tr" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Scroll-reveal yalnız JS varken devreye girer; FOUC olmadan --}}
    <script>document.documentElement.classList.add('js');</script>
    <title>{{ $title ? $title.' · ' : '' }}{{ config('neva.brand.name') }}</title>
    <meta name="description" content="Neva-QR Menü — QR ile açılan, markanıza özel lüks dijital menüler. Kafe ve restoran şablonları, canlı önizleme, tek tıkla QR ve PDF.">
    <link rel="icon" href="{{ asset('img/nevalogo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-ink-50 antialiased" x-data="{ menu: false }">
    <header class="sticky top-0 z-40 border-b border-ink-100 bg-ink-50/85 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('img/nevalogo.png') }}" alt="Neva-QR Menü" class="h-8 w-auto">
            </a>

            <nav class="hidden items-center gap-1 text-sm md:flex">
                <a href="{{ route('about') }}" class="rounded-lg px-3 py-2 font-medium text-ink-600 transition hover:text-ink-900 {{ request()->routeIs('about') ? 'text-ink-900' : '' }}">Hakkımızda</a>
                <a href="{{ route('pricing') }}" class="rounded-lg px-3 py-2 font-medium text-ink-600 transition hover:text-ink-900 {{ request()->routeIs('pricing') ? 'text-ink-900' : '' }}">Fiyatlar</a>
                <a href="{{ route('contact') }}" class="rounded-lg px-3 py-2 font-medium text-ink-600 transition hover:text-ink-900 {{ request()->routeIs('contact') ? 'text-ink-900' : '' }}">İletişim</a>
                <span class="mx-2 h-5 w-px bg-ink-200"></span>
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('panel.dashboard') }}" class="btn-primary px-4">Panele git</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 font-medium text-ink-600 transition hover:text-ink-900">Giriş</a>
                    <a href="{{ route('register') }}" class="btn-primary px-4">Kayıt Ol</a>
                @endauth
            </nav>

            <button @click="menu = !menu" class="md:hidden" aria-label="Menü">
                <svg class="h-6 w-6 text-ink-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <div x-show="menu" x-collapse class="border-t border-ink-100 bg-white px-6 py-4 md:hidden" style="display:none">
            <div class="flex flex-col gap-1 text-sm">
                <a href="{{ route('about') }}" class="rounded-lg px-3 py-2.5 font-medium text-ink-700 hover:bg-ink-50">Hakkımızda</a>
                <a href="{{ route('pricing') }}" class="rounded-lg px-3 py-2.5 font-medium text-ink-700 hover:bg-ink-50">Fiyatlar</a>
                <a href="{{ route('contact') }}" class="rounded-lg px-3 py-2.5 font-medium text-ink-700 hover:bg-ink-50">İletişim</a>
                <a href="{{ route('register') }}" class="btn-primary mt-2 px-4">Kayıt Ol</a>
            </div>
        </div>
    </header>

    @if (session('success'))
        <div class="mx-auto mt-4 max-w-6xl px-6">
            <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">{{ session('success') }}</div>
        </div>
    @endif

    <main class="overflow-x-clip">{{ $slot }}</main>

    <footer class="mt-24 border-t border-ink-100 bg-white">
        <div class="mx-auto max-w-6xl px-6 py-14">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <img src="{{ asset('img/nevalogo.png') }}" alt="Neva-QR Menü" class="h-8 w-auto">
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-ink-500">
                        Restoran ve kafeler için QR ile açılan, markaya özel tasarlanan lüks dijital menü platformu.
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Ürün</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-ink-600">
                        <li><a href="{{ route('pricing') }}" class="hover:text-ink-900">Fiyatlandırma</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-ink-900">Hakkımızda</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-ink-900">Kayıt Ol</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">İletişim</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-ink-600">
                        <li><a href="{{ route('contact') }}" class="hover:text-ink-900">Bize yazın</a></li>
                        <li><a href="mailto:{{ config('neva.brand.support_email') }}" class="hover:text-ink-900">{{ config('neva.brand.support_email') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-ink-100 pt-6 text-xs text-ink-400 sm:flex-row">
                <p>&copy; {{ date('Y') }} {{ config('neva.brand.name') }}. Tüm hakları saklıdır.</p>
                <p>Türkiye'de tasarlandı</p>
            </div>
        </div>
    </footer>

    {{-- Scroll-reveal gözlemcisi: [data-reveal] elemanları görünüre girince açılır --}}
    <script>
        (function () {
            var nodes = document.querySelectorAll('[data-reveal]');
            if (!nodes.length) return;

            var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce || !('IntersectionObserver' in window)) {
                nodes.forEach(function (n) { n.classList.add('is-visible'); });
                return;
            }

            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    // Görünüre giren VEYA sayfa zaten üzerinden kaydırılmış elemanları aç.
                    if (entry.isIntersecting || entry.boundingClientRect.top < 0) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

            nodes.forEach(function (n) { io.observe(n); });

            // Emniyet: JS çalıştı ama gözlemci bir şekilde tetiklenmediyse
            // (çok hızlı scroll, sekme), 2.5 sn sonra kalanları göster.
            window.setTimeout(function () {
                nodes.forEach(function (n) { n.classList.add('is-visible'); });
            }, 2500);
        })();
    </script>
</body>
</html>
