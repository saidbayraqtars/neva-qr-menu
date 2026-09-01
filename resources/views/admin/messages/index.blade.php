<x-app-layout title="Mesajlar">
    <x-slot name="header">Destek mesajları</x-slot>

    @php
        $tabs = ['open' => 'Açık', 'closed' => 'Kapalı', 'all' => 'Tümü'];
        $card = 'rounded-3xl bg-white ring-1 ring-ink-100/80 shadow-[0_1px_2px_rgba(23,23,15,.04),0_26px_50px_-30px_rgba(23,23,15,.22)]';
    @endphp

    <div class="mx-auto max-w-5xl space-y-5">
        <div class="flex flex-wrap gap-2">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin.messages.index', ['status' => $key]) }}"
                   class="rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ $status === $key ? 'bg-ink-900 text-white' : 'bg-white text-ink-500 ring-1 ring-ink-100 hover:text-ink-900' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="{{ $card }} overflow-hidden">
            @forelse ($conversations as $conversation)
                <a href="{{ route('admin.messages.show', $conversation) }}"
                   class="flex items-start gap-4 border-b border-ink-100 px-6 py-5 transition last:border-0 hover:bg-ink-50">
                    <span @class([
                        'mt-1 grid h-9 w-9 shrink-0 place-items-center rounded-full text-xs font-bold',
                        'bg-red-500 text-white' => $conversation->unread_for_admin > 0,
                        'bg-ink-100 text-ink-500' => $conversation->unread_for_admin === 0,
                    ])>
                        {{ $conversation->unread_for_admin > 0 ? $conversation->unread_for_admin : '—' }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="truncate font-semibold text-ink-900">{{ $conversation->subject }}</span>
                            @unless ($conversation->isOpen())
                                <span class="rounded-full bg-ink-100 px-2 py-0.5 text-[11px] font-semibold text-ink-500">Kapalı</span>
                            @endunless
                        </span>
                        <span class="mt-0.5 block truncate text-xs text-ink-400">
                            {{ $conversation->user?->name }} · {{ $conversation->user?->email }}
                            @if ($conversation->restaurant)
                                · {{ $conversation->restaurant->name }}
                            @endif
                        </span>
                        <span class="mt-0.5 block text-xs text-ink-400">
                            {{ $conversation->last_message_at?->diffForHumans() ?? $conversation->created_at->diffForHumans() }}
                        </span>
                    </span>
                    <span class="mt-1 text-ink-300">→</span>
                </a>
            @empty
                <div class="px-6 py-14 text-center text-sm text-ink-400">Bu filtrede mesaj yok.</div>
            @endforelse
        </div>

        {{ $conversations->links() }}
    </div>
</x-app-layout>
