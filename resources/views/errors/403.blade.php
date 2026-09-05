<x-errors.layout
    code="403"
    title="Bu sayfaya erişiminiz yok"
    :lead="$exception?->getMessage() ?: 'Bu içerik yalnızca yetkili hesaplara açık. Yanlış hesapla giriş yapmış olabilirsiniz.'">

    <x-slot:actions>
        @auth
            <a class="btn gold" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('panel.dashboard') }}">Panele dön</a>
        @else
            <a class="btn gold" href="{{ route('login') }}">Giriş yap</a>
        @endauth
        <a class="btn ghost" href="{{ route('home') }}">Ana sayfa</a>
    </x-slot:actions>
</x-errors.layout>
