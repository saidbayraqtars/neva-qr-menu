<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-display text-2xl text-ink-900">Yeni şifre belirleyin</h2>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="'E-posta'" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" :value="'Yeni şifre'" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="'Yeni şifre (tekrar)'" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
        </div>

        <x-primary-button>Şifreyi sıfırla</x-primary-button>
    </form>
</x-guest-layout>
