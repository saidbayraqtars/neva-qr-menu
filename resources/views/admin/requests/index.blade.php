<x-app-layout title="Onay Kuyruğu">
    <x-slot name="header">Alt Domain Onay Kuyruğu</x-slot>

    <div class="mx-auto max-w-4xl space-y-5">
        <div class="flex gap-2">
            @foreach (['pending' => 'Bekleyen', 'approved' => 'Onaylı', 'rejected' => 'Reddedilen', 'all' => 'Tümü'] as $key => $label)
                <a href="{{ route('admin.requests.index', ['status' => $key]) }}"
                   class="rounded-xl px-3.5 py-2 text-sm font-medium transition {{ $status === $key ? 'bg-ink-900 text-white' : 'bg-white text-ink-500 ring-1 ring-ink-200 hover:text-ink-900' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @forelse ($requests as $req)
            <div class="card p-5" x-data="{ reject: false }">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-mono text-lg text-ink-900">
                            {{ $req->requested_subdomain }}<span class="text-ink-400">.{{ config('neva.root_domain') }}</span>
                        </p>
                        <p class="mt-1 text-sm text-ink-600">{{ $req->restaurant?->name ?? '—' }}</p>
                        <p class="text-xs text-ink-400">
                            {{ $req->restaurant?->owner?->name }} · {{ $req->restaurant?->owner?->email }}
                            · {{ $req->restaurant?->categories?->count() ?? 0 }} kategori
                            · {{ $req->created_at->diffForHumans() }}
                        </p>
                    </div>

                    @if ($req->status === 'pending')
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('admin.requests.approve', $req) }}">
                                @csrf
                                <button class="btn-gold px-5">Onayla & Yayınla</button>
                            </form>
                            <button @click="reject = !reject" class="btn-ghost">Reddet</button>
                        </div>
                    @else
                        <span class="rounded-lg px-2.5 py-1 text-xs font-semibold {{ $req->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $req->status === 'approved' ? 'Onaylandı' : 'Reddedildi' }}
                        </span>
                    @endif
                </div>

                <form x-show="reject" x-collapse method="POST" action="{{ route('admin.requests.reject', $req) }}" class="mt-4 flex gap-2">
                    @csrf
                    <input name="admin_note" class="field" placeholder="Red gerekçesi (kullanıcıya iletilir)">
                    <button class="btn bg-red-600 text-white hover:bg-red-500 px-4">Gönder</button>
                </form>

                @if ($req->admin_note)
                    <p class="mt-3 rounded-lg bg-ink-50 px-3 py-2 text-xs text-ink-500">Not: {{ $req->admin_note }}</p>
                @endif
            </div>
        @empty
            <div class="card grid place-items-center p-16 text-center">
                <p class="font-display text-lg text-ink-900">Kuyruk boş</p>
                <p class="mt-1 text-sm text-ink-500">Bu filtrede talep bulunmuyor.</p>
            </div>
        @endforelse

        {{ $requests->links() }}
    </div>
</x-app-layout>
