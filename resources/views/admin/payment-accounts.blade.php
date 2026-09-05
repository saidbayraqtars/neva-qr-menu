@php
    $activeCount = $accounts->where('is_active', true)->count();
@endphp

<x-app-layout title="Banka Hesapları">
    <x-slot name="header">Banka Hesapları</x-slot>

    <div class="mx-auto max-w-4xl space-y-6">

        {{-- Hiç aktif hesap yoksa kayıt akışı yarım kalır: müşteri ödeme
             sayfasında nereye para yatıracağını göremez. En üstte uyar. --}}
        @if ($activeCount === 0)
            <div class="card border-l-4 border-red-400 p-6">
                <h3 class="font-display text-lg text-ink-900">Yayında hesap yok</h3>
                <p class="mt-2 text-sm leading-relaxed text-ink-600">
                    Şu anda kayıt olan bir müşteri ödeme sayfasında hiçbir hesap göremiyor
                    ve havale yapamıyor. En az bir hesap ekleyip yayına alın.
                </p>
            </div>
        @endif

        {{-- Yeni hesap --}}
        <div class="card p-6" x-data="{ open: {{ $accounts->isEmpty() ? 'true' : 'false' }} }">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="font-display text-lg text-ink-900">Hesap ekle</h3>
                    <p class="mt-1 text-sm text-ink-500">
                        Eklediğiniz aktif hesapların tamamı ödeme sayfasında ve havale
                        talimatı e-postasında listelenir.
                    </p>
                </div>
                <button type="button" @click="open = !open" class="btn-ghost shrink-0" x-text="open ? 'Kapat' : 'Yeni hesap'"></button>
            </div>

            <form method="POST" action="{{ route('admin.accounts.store') }}" class="mt-5 space-y-4" x-show="open" x-collapse>
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label value="Banka" />
                        <x-text-input name="bank_name" value="{{ old('bank_name') }}" placeholder="Ziraat Bankası" required maxlength="120" />
                        <x-input-error :messages="$errors->get('bank_name')" class="mt-1.5" />
                    </div>
                    <div>
                        <x-input-label value="Hesap sahibi" />
                        <x-text-input name="account_name" value="{{ old('account_name') }}" placeholder="Said Bayraktar" required maxlength="160" />
                        <x-input-error :messages="$errors->get('account_name')" class="mt-1.5" />
                    </div>
                </div>

                <div>
                    <x-input-label value="IBAN" />
                    <x-text-input name="iban" value="{{ old('iban') }}" placeholder="TR00 0000 0000 0000 0000 0000 00"
                                  required maxlength="40" class="font-mono" autocomplete="off" spellcheck="false" />
                    <p class="mt-1.5 text-xs text-ink-400">
                        Boşluklu girebilirsiniz. IBAN sağlama hanesi kontrol edilir — hatalı IBAN kaydedilmez.
                    </p>
                    <x-input-error :messages="$errors->get('iban')" class="mt-1.5" />
                </div>

                <div>
                    <x-input-label value="Not (opsiyonel)" />
                    <x-text-input name="note" value="{{ old('note') }}" placeholder="TL hesabı" maxlength="120" />
                    <x-input-error :messages="$errors->get('note')" class="mt-1.5" />
                </div>

                <label class="flex items-center gap-2.5 text-sm text-ink-700">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="rounded border-ink-300 text-ink-900 focus:ring-gold-500">
                    Hemen yayına al
                </label>

                <button class="btn-gold">Hesabı ekle</button>
            </form>
        </div>

        {{-- Mevcut hesaplar --}}
        @forelse ($accounts as $account)
            <div class="card p-6" x-data="{ edit: false }">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-display text-lg text-ink-900">{{ $account->bank_name }}</h3>
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $account->is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-ink-100 text-ink-500' }}">
                                {{ $account->is_active ? 'Yayında' : 'Gizli' }}
                            </span>
                            @if ($account->note)
                                <span class="text-xs text-ink-400">{{ $account->note }}</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-ink-500">{{ $account->account_name }}</p>
                        <p class="mt-2 break-all font-mono text-sm font-semibold tracking-tight text-ink-900">{{ $account->formatted_iban }}</p>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-2">
                        <button type="button" @click="edit = !edit" class="btn-ghost px-3 py-1.5 text-xs">Düzenle</button>

                        <form method="POST" action="{{ route('admin.accounts.toggle', $account) }}">
                            @csrf
                            <button class="btn-ghost px-3 py-1.5 text-xs">{{ $account->is_active ? 'Gizle' : 'Yayına al' }}</button>
                        </form>

                        <form method="POST" action="{{ route('admin.accounts.destroy', $account) }}"
                              onsubmit="return confirm('“{{ $account->bank_name }}” hesabı silinsin mi? Bu işlem geri alınamaz.')">
                            @csrf @method('DELETE')
                            <button class="rounded-xl px-3 py-1.5 text-xs font-semibold text-red-600 ring-1 ring-red-200 transition hover:bg-red-50">Sil</button>
                        </form>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.accounts.update', $account) }}" class="mt-5 space-y-4 border-t border-ink-100 pt-5"
                      x-show="edit" x-collapse style="display:none">
                    @csrf @method('PUT')

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label value="Banka" />
                            <x-text-input name="bank_name" value="{{ $account->bank_name }}" required maxlength="120" />
                        </div>
                        <div>
                            <x-input-label value="Hesap sahibi" />
                            <x-text-input name="account_name" value="{{ $account->account_name }}" required maxlength="160" />
                        </div>
                    </div>

                    <div>
                        <x-input-label value="IBAN" />
                        <x-text-input name="iban" value="{{ $account->formatted_iban }}" required maxlength="40" class="font-mono" />
                    </div>

                    <div>
                        <x-input-label value="Not" />
                        <x-text-input name="note" value="{{ $account->note }}" maxlength="120" />
                    </div>

                    <label class="flex items-center gap-2.5 text-sm text-ink-700">
                        <input type="checkbox" name="is_active" value="1" @checked($account->is_active)
                               class="rounded border-ink-300 text-ink-900 focus:ring-gold-500">
                        Yayında
                    </label>

                    <button class="btn-primary">Kaydet</button>
                </form>
            </div>
        @empty
            <div class="card p-8 text-center">
                <p class="text-sm text-ink-500">Henüz hesap eklenmemiş.</p>
            </div>
        @endforelse

        @if ($errors->any() && ! $errors->has('bank_name') && ! $errors->has('iban') && ! $errors->has('account_name'))
            <div class="card border-l-4 border-red-400 p-5">
                <ul class="space-y-1 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-app-layout>
