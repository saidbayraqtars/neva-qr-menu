<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-display text-2xl text-ink-900">Güvenli alan</h2>
        <p class="mt-1 text-sm text-ink-500">Devam etmeden önce şifrenizi tekrar girin.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf
        <div>
            <x-input-label for="password" :value="'Şifre'" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" autofocus />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button>Onayla</x-primary-button>
    </form>
</x-guest-layout>
