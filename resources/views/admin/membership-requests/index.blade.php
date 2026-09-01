<x-app-layout title="Üyelik Talepleri">
    <x-slot name="header">Kayıt / Üyelik Talepleri</x-slot>

    <div class="mx-auto max-w-4xl space-y-5">
        <div class="flex gap-2">
            @foreach (['pending' => 'Bekleyen', 'approved' => 'Onaylı', 'rejected' => 'Reddedilen', 'all' => 'Tümü'] as $key => $label)
                <a href="{{ route('admin.memberships.index', ['status' => $key]) }}"
                   class="rounded-xl px-3.5 py-2 text-sm font-medium transition {{ $status === $key ? 'bg-ink-900 text-white' : 'bg-white text-ink-500 ring-1 ring-ink-200 hover:text-ink-900' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @forelse ($requests as $req)
            <div class="card p-5" x-data="{ reject: false }">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-display text-lg text-ink-900">{{ $req->business_name }}</p>
                        <p class="mt-0.5 text-sm text-ink-600">{{ $req->name }} · {{ $req->email }}@if ($req->phone) · {{ $req->phone }}@endif</p>
                        <p class="mt-1 flex flex-wrap items-center gap-2 text-xs text-ink-400">
                            <span class="rounded-full bg-ink-100 px-2 py-0.5 font-medium text-ink-600">{{ $req->plan?->name ?? 'Paket yok' }}</span>
                            @if ($req->table_count)<span>{{ $req->table_count }} masa</span>@endif
                            <span class="font-semibold text-ink-700">{{ money($req->amount, 'TRY') }}</span>
                            <span>· {{ $req->created_at->diffForHumans() }}</span>
                        </p>
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        @if ($req->isPending())
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.memberships.payment', $req) }}">
                                    @csrf
                                    <button class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $req->isPaid() ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-ink-100 text-ink-500 hover:bg-ink-200' }}">
                                        {{ $req->isPaid() ? '✓ Ödeme alındı' : 'Ödeme bekliyor' }}
                                    </button>
                                </form>
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.memberships.approve', $req) }}"
                                      onsubmit="return confirm('Hesap oluşturulacak ve geçici şifre üretilecek. Devam edilsin mi?')">
                                    @csrf
                                    <button class="btn-gold px-5" @disabled(! $req->isPaid())>Onayla & Hesap Aç</button>
                                </form>
                                <button @click="reject = !reject" class="btn-ghost">Reddet</button>
                            </div>
                            @unless ($req->isPaid())
                                <p class="text-[11px] text-ink-400">Onay için önce ödemeyi işaretleyin</p>
                            @endunless
                        @else
                            <span class="rounded-lg px-2.5 py-1 text-xs font-semibold {{ $req->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $req->status === 'approved' ? 'Onaylandı' : 'Reddedildi' }}
                            </span>
                        @endif
                    </div>
                </div>

                <form x-show="reject" x-collapse method="POST" action="{{ route('admin.memberships.reject', $req) }}" class="mt-4 flex gap-2" style="display:none">
                    @csrf
                    <input name="admin_note" class="field" placeholder="Red gerekçesi (opsiyonel)">
                    <button class="btn bg-red-600 px-4 text-white hover:bg-red-500">Gönder</button>
                </form>

                @if ($req->status === 'approved' && $req->temp_password)
                    <div class="mt-4 rounded-xl bg-ink-900 px-4 py-3 text-sm text-white" x-data="{ copied: false }">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs text-ink-300">Geçici şifre ({{ $req->email }})</p>
                                <p class="mt-0.5 font-mono text-base tracking-wide">{{ $req->temp_password }}</p>
                            </div>
                            <button type="button" class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-white/20"
                                    @click="navigator.clipboard.writeText('{{ $req->temp_password }}'); copied = true; setTimeout(() => copied = false, 1500)">
                                <span x-text="copied ? 'Kopyalandı ✓' : 'Kopyala'"></span>
                            </button>
                        </div>
                        <p class="mt-1.5 text-[11px] text-ink-400">Kullanıcı ilk girişte kalıcı şifre belirlemek zorunda.</p>
                    </div>
                @endif

                @if ($req->admin_note)
                    <p class="mt-3 rounded-lg bg-ink-50 px-3 py-2 text-xs text-ink-500">Not: {{ $req->admin_note }}</p>
                @endif
            </div>
        @empty
            <div class="card grid place-items-center p-16 text-center">
                <p class="font-display text-lg text-ink-900">Talep yok</p>
                <p class="mt-1 text-sm text-ink-500">Bu filtrede kayıt talebi bulunmuyor.</p>
            </div>
        @endforelse

        {{ $requests->links() }}
    </div>
</x-app-layout>
