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
                        @if ($req->reference_code)
                            <p class="mt-2 inline-flex items-center gap-2 rounded-lg bg-gold-50 px-2.5 py-1 text-xs ring-1 ring-gold-200">
                                <span class="text-ink-500">Havale açıklaması:</span>
                                <span class="font-mono font-bold text-ink-900">{{ $req->reference_code }}</span>
                            </p>
                        @endif
                        @if ($req->paid_at)
                            <p class="mt-1 text-[11px] text-emerald-600">Ödeme işaretlendi: {{ $req->paid_at->format('d.m.Y H:i') }}@if ($req->payment_note) · {{ $req->payment_note }}@endif</p>
                        @endif
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        @if ($req->isPending())
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.memberships.payment', $req) }}" class="flex items-center gap-2">
                                    @csrf
                                    @unless ($req->isPaid())
                                        <input name="payment_note" class="w-40 rounded-lg border-0 bg-ink-50 px-2.5 py-1.5 text-xs ring-1 ring-inset ring-ink-200 focus:ring-2 focus:ring-gold-500"
                                               placeholder="Dekont notu (ops.)">
                                    @endunless
                                    <button class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $req->isPaid() ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-ink-100 text-ink-500 hover:bg-ink-200' }}">
                                        {{ $req->isPaid() ? '✓ Havale geldi' : 'Havale geldi olarak işaretle' }}
                                    </button>
                                </form>
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.memberships.approve', $req) }}"
                                      onsubmit="return confirm('Hesap açılacak ve kullanıcıya şifre belirleme bağlantısı e-posta ile gönderilecek. Devam edilsin mi?')">
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

                @if ($req->status === 'approved')
                    <div class="mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200">
                        Hesap açıldı. Şifre belirleme bağlantısı <strong>{{ $req->email }}</strong> adresine gönderildi
                        (72 saat geçerli). Şifreyi yalnızca kullanıcı belirler — hiç kimse göremez.
                        <a href="{{ route('admin.users.index', ['q' => $req->email]) }}" class="font-semibold underline">Kullanıcı kaydı →</a>
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
