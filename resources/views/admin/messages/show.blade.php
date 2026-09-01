<x-app-layout :title="$conversation->subject">
    <x-slot name="header">{{ $conversation->subject }}</x-slot>
    <x-slot name="actions">
        <form method="POST" action="{{ route('admin.messages.status', $conversation) }}">
            @csrf
            <button class="btn-ghost">{{ $conversation->isOpen() ? 'Görüşmeyi kapat' : 'Yeniden aç' }}</button>
        </form>
        <a href="{{ route('admin.messages.index') }}" class="btn-ghost">← Liste</a>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-5">

        {{-- Kullanıcı künyesi --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-ink-100">
            <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                <div>
                    <p class="font-semibold text-ink-900">{{ $conversation->user?->name }}</p>
                    <p class="text-xs text-ink-400">{{ $conversation->user?->email }}{{ $conversation->user?->phone ? ' · '.$conversation->user->phone : '' }}</p>
                </div>
                @if ($conversation->restaurant)
                    <div class="text-right">
                        <p class="font-semibold text-ink-900">{{ $conversation->restaurant->name }}</p>
                        <p class="text-xs text-ink-400">
                            {{ $conversation->restaurant->subdomain ? $conversation->restaurant->subdomain.'.'.config('neva.root_domain') : 'alt domain yok' }}
                            · {{ $conversation->restaurant->currentPlan()?->name ?? 'paket yok' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-ink-100/80 shadow-[0_1px_2px_rgba(23,23,15,.04),0_26px_50px_-30px_rgba(23,23,15,.22)]">
            <x-chat-thread
                :conversation="$conversation"
                side="admin"
                :reply-action="route('admin.messages.reply', $conversation)"
                :poll-url="route('admin.messages.poll', $conversation)"
                :closed="! $conversation->isOpen()" />
        </div>
    </div>
</x-app-layout>
