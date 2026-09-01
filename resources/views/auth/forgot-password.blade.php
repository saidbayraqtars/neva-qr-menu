<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-display text-2xl text-ink-900">Şifrenizi mi unuttunuz?</h2>
        <p class="mt-1 text-sm text-ink-500">E-postanıza sıfırlama bağlantısı gönderelim.</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <x-input-label for="email" :value="'E-posta'" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button>Sıfırlama bağlantısı gönder</x-primary-button>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="font-medium text-ink-500 hover:text-ink-900">← Girişe dön</a>
        </p>
    </form>
</x-guest-layout>
