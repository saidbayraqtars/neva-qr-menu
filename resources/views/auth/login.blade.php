<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-display text-2xl text-ink-900">Tekrar hoş geldiniz</h2>
        <p class="mt-1 text-sm text-ink-500">Panelinize giriş yapın.</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="'E-posta'" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" :value="'Şifre'" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-ink-600">
                <input type="checkbox" name="remember" class="rounded border-ink-300 text-ink-900 focus:ring-gold-500">
                Beni hatırla
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-ink-500 hover:text-ink-900">Şifremi unuttum</a>
            @endif
        </div>

        <x-primary-button>Giriş yap</x-primary-button>

        <p class="text-center text-sm text-ink-500">
            Hesabınız yok mu?
            <a href="{{ route('register') }}" class="font-semibold text-ink-900 hover:text-gold-600">Kayıt olun</a>
        </p>
    </form>
</x-guest-layout>
