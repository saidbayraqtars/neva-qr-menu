<x-app-layout :title="$conversation->subject">
    <x-slot name="header">{{ $conversation->subject }}</x-slot>
    <x-slot name="actions">
        <a href="{{ route('panel.messages.index') }}" class="btn-ghost">← Tüm mesajlar</a>
    </x-slot>

    <div class="mx-auto max-w-3xl">
        <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-ink-100/80 shadow-[0_1px_2px_rgba(23,23,15,.04),0_26px_50px_-30px_rgba(23,23,15,.22)]">
            <x-chat-thread
                :conversation="$conversation"
                side="user"
                :reply-action="route('panel.messages.reply', $conversation)"
                :poll-url="route('panel.messages.poll', $conversation)"
                :closed="! $conversation->isOpen()" />
        </div>
    </div>
</x-app-layout>
