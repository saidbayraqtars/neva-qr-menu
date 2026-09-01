<x-app-layout title="Kullanıcılar">
    <x-slot name="header">Kullanıcılar</x-slot>

    <div class="mx-auto max-w-4xl space-y-5" x-data="{ newAccount: @js($errors->any() && (old('business_name') || old('email'))) }">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex gap-2">
                <input name="q" value="{{ $q }}" class="field max-w-xs" placeholder="İsim veya e-posta ara…">
                <button class="btn-ghost">Ara</button>
            </form>
            <button type="button" @click="newAccount = true" class="btn-primary px-4">+ Yeni Hesap Oluştur</button>
        </div>

        {{-- Elle hesap oluşturma modalı --}}
        <div x-show="newAccount" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-ink-950/50 p-4" @keydown.escape.window="newAccount = false">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" @click.outside="newAccount = false">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h3 class="font-display text-lg text-ink-900">Yeni hesap oluştur</h3>
                        <p class="mt-0.5 text-xs text-ink-500">Hesap doğrudan aktif olur; geçici şifre üretilir.</p>
                    </div>
                    <button type="button" @click="newAccount = false" class="text-ink-400 hover:text-ink-700">✕</button>
                </div>
                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label :value="'İşletme adı'" />
                        <x-text-input name="business_name" value="{{ old('business_name') }}" required />
                        <x-input-error :messages="$errors->get('business_name')" />
                    </div>
                    <div>
                        <x-input-label :value="'Ad soyad'" />
                        <x-text-input name="name" value="{{ old('name') }}" required />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label :value="'E-posta'" />
                        <x-text-input name="email" type="email" value="{{ old('email') }}" required />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>
                    <div>
                        <x-input-label :value="'Telefon (opsiyonel)'" />
                        <x-text-input name="phone" type="tel" value="{{ old('phone') }}" placeholder="0555 000 00 00" />
                        <x-input-error :messages="$errors->get('phone')" />
                    </div>
                    <div>
                        <x-input-label :value="'Paket'" />
                        <select name="plan_id" class="field">
                            <option value="">— Paket seçilmedi —</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }} · {{ money($plan->price, 'TRY') }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('plan_id')" />
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="newAccount = false" class="btn-ghost">Vazgeç</button>
                        <button class="btn-primary px-6">Hesabı Oluştur</button>
                    </div>
                </form>
            </div>
        </div>


        @forelse ($users as $user)
            @php $sub = $user->subscriptions->first(); $rest = $user->restaurants->first(); @endphp
            <div class="card p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-semibold text-ink-900">{{ $user->name }}</p>
                        <p class="text-sm text-ink-600">{{ $user->email }}@if ($user->phone) · {{ $user->phone }}@endif</p>
                        <p class="mt-1 flex flex-wrap items-center gap-2 text-xs text-ink-400">
                            <span>{{ $rest?->name ?? 'İşletme yok' }}</span>
                            @if ($rest?->subdomain)
                                <a href="{{ tenant_domain($rest) }}" target="_blank" class="text-gold-700 hover:underline">{{ $rest->subdomain }}.{{ config('neva.root_domain') }} ↗</a>
                            @endif
                            @if ($sub?->plan)<span class="rounded-full bg-ink-100 px-2 py-0.5 font-medium text-ink-600">{{ $sub->plan->name }}</span>@endif
                            @if ($user->must_change_password)<span class="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-700">Şifre henüz belirlenmedi</span>@endif
                        </p>
                    </div>

                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                          onsubmit="return confirm('{{ $user->name }} adresine yeni bir şifre belirleme bağlantısı gönderilsin mi? Mevcut şifre geçersiz olur.')">
                        @csrf
                        <button class="btn-ghost">Şifre bağlantısı gönder</button>
                    </form>
                </div>

                @if ($user->must_change_password)
                    <div class="mt-4 rounded-xl bg-ink-50 px-4 py-3 text-xs text-ink-500 ring-1 ring-ink-100">
                        Kullanıcı henüz şifresini belirlemedi.
                        @if ($user->password_setup_expires_at)
                            Gönderilen bağlantı {{ $user->password_setup_expires_at->isFuture() ? $user->password_setup_expires_at->diffForHumans() : 'süresi dolmuş' }}.
                        @endif
                        Şifreler hiçbir yerde düz metin saklanmaz — gerekirse yeni bağlantı gönderin.
                    </div>
                @endif
            </div>
        @empty
            <div class="card grid place-items-center p-16 text-center">
                <p class="font-display text-lg text-ink-900">Kullanıcı bulunamadı</p>
            </div>
        @endforelse

        {{ $users->links() }}
    </div>
</x-app-layout>
