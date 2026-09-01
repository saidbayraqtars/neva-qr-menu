<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-display text-2xl text-ink-900">Şifrenizi oluşturun</h2>
        <p class="mt-1 text-sm leading-relaxed text-ink-500">
            Hesabınız hazır. Güvenliğiniz için şifrenizi yalnızca siz belirliyorsunuz —
            ekibimiz dahil hiç kimse şifrenizi göremez.
        </p>
    </div>

    <x-input-error :messages="$errors->get('token')" class="mb-4" />

    <form method="POST" action="{{ route('password.setup.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <x-input-label for="email" :value="'E-posta'" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $email)" required readonly
                          class="bg-ink-50 text-ink-500" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" :value="'Şifreniz'" />
            <x-text-input id="password" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
            <p class="mt-1.5 text-xs text-ink-400">En az 8 karakter. Büyük/küçük harf ve rakam karışımı önerilir.</p>
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="'Şifreniz (tekrar)'" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full justify-center">Şifremi kaydet ve panele gir</x-primary-button>
    </form>

    <p class="mt-5 text-center text-xs text-ink-400">
        Bağlantının süresi dolduysa
        <a href="{{ route('password.request') }}" class="font-semibold text-gold-700 hover:underline">şifremi unuttum</a>
        ile yenisini isteyebilirsiniz.
    </p>
</x-guest-layout>
