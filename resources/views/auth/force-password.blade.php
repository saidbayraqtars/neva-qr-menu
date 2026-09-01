<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-display text-2xl text-ink-900">Şifrenizi belirleyin</h2>
        <p class="mt-1 text-sm text-ink-500">
            Geçici şifreyle giriş yaptınız. Devam etmek için kalıcı bir şifre belirleyin.
        </p>
    </div>

    <form method="POST" action="{{ route('password.force.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="password" :value="'Yeni şifre'" />
            <x-text-input id="password" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="'Yeni şifre (tekrar)'" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button>Şifreyi kaydet ve devam et</x-primary-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm font-medium text-ink-400 hover:text-ink-700">Çıkış yap</button>
    </form>
</x-guest-layout>
