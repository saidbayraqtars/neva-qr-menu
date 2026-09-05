@props([
    'code' => '500',
    'title' => 'Bir sorun oluştu',
    'lead' => null,
])

{{--
    Hata sayfası iskeleti.

    NEDEN kendi içinde tam (inline CSS, @vite yok, x-marketing-layout yok):
    hata sayfası, uygulamanın bozuk olduğu anda render edilir. Derlenmiş varlık
    manifesti eksikse ya da bir servis sağlayıcı patlamışsa layout'un kendisi de
    patlar ve kullanıcı Laravel'in çıplak İngilizce ekranını görür. Buradaki
    hiçbir şey dış bağımlılığa dayanmaz.
--}}
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $code }} — {{ $title }} · {{ config('neva.brand.name', 'Neva-QR Menü') }}</title>
    <link rel="icon" href="{{ asset('img/nevalogo.png') }}">
    <style>
        :root {
            --ink-50: #f6f6f5;
            --ink-100: #e7e7e4;
            --ink-200: #cfcfc9;
            --ink-400: #7c7c72;
            --ink-500: #5c5c53;
            --ink-900: #17170f;
            --ink-950: #0d0d07;
            --gold-400: #d3b985;
            --gold-500: #c8a96a;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem 1.5rem;
            background: var(--ink-50);
            color: var(--ink-500);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
            line-height: 1.6;
        }
        .card {
            width: 100%;
            max-width: 30rem;
            background: #fff;
            border-radius: 1.5rem;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 1px 2px rgb(23 23 15 / 0.04), 0 12px 32px -8px rgb(23 23 15 / 0.12);
        }
        .logo { height: 2rem; width: auto; margin-bottom: 1.75rem; }
        .code {
            display: inline-block;
            font-size: .6875rem;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--ink-400);
            background: var(--ink-50);
            border: 1px solid var(--ink-100);
            border-radius: 999px;
            padding: .25rem .75rem;
        }
        h1 {
            margin: 1rem 0 0;
            font-family: Fraunces, ui-serif, Georgia, 'Times New Roman', serif;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: -.02em;
            color: var(--ink-900);
        }
        p { margin: .75rem 0 0; font-size: .9375rem; }
        .actions { margin-top: 1.75rem; display: flex; flex-wrap: wrap; gap: .5rem; justify-content: center; }
        a.btn {
            display: inline-block;
            border-radius: .75rem;
            padding: .625rem 1.25rem;
            font-size: .875rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color .15s ease;
        }
        a.gold { background: var(--gold-500); color: var(--ink-950); }
        a.gold:hover { background: var(--gold-400); }
        a.ghost { background: #fff; color: var(--ink-900); box-shadow: inset 0 0 0 1px var(--ink-200); }
        a.ghost:hover { background: var(--ink-50); }
        .foot { margin-top: 1.5rem; font-size: .75rem; color: var(--ink-400); }
        .foot a { color: inherit; }
    </style>
</head>
<body>
    <main class="card">
        <img class="logo" src="{{ asset('img/nevalogo.png') }}" alt="{{ config('neva.brand.name', 'Neva-QR Menü') }}">

        <span class="code">Hata {{ $code }}</span>
        <h1>{{ $title }}</h1>

        @if ($lead)
            <p>{{ $lead }}</p>
        @endif

        {{ $slot ?? '' }}

        <div class="actions">
            {{ $actions ?? '' }}
        </div>

        <p class="foot">
            Sorun sürüyorsa
            <a href="mailto:{{ config('neva.brand.support_email') }}">{{ config('neva.brand.support_email') }}</a>
            adresinden bize yazın.
        </p>
    </main>
</body>
</html>
