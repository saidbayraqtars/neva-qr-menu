<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-display text-2xl text-ink-900">E-postanızı doğrulayın</h2>
        <p class="mt-1 text-sm text-ink-500">
            Kayıt sırasında gönderdiğimiz bağlantıya tıklayarak hesabınızı doğrulayın.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status status="Yeni bir doğrulama bağlantısı gönderildi." />
    @endif

    <div class="flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>Bağlantıyı tekrar gönder</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-ink-500 hover:text-ink-900">Çıkış yap</button>
        </form>
    </div>
</x-guest-layout>
