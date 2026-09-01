<x-app-layout title="Mesajlar">
    <x-slot name="header">Mesajlar</x-slot>

    @php $card = 'rounded-3xl bg-white p-7 ring-1 ring-ink-100/80 shadow-[0_1px_2px_rgba(23,23,15,.04),0_26px_50px_-30px_rgba(23,23,15,.22)]'; @endphp

    <div class="mx-auto max-w-4xl space-y-6">

        {{-- Yeni konu --}}
        <div class="{{ $card }}" x-data="{ open: {{ $conversations->total() === 0 ? 'true' : 'false' }} }">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-display text-lg text-ink-900">Ekibimize yazın</h3>
                    <p class="mt-1 text-sm text-ink-500">Yanıtımız bu ekranda anında görünür — e-posta beklemenize gerek yok.</p>
                </div>
                <button @click="open = !open" class="btn-gold" x-text="open ? 'Vazgeç' : 'Yeni mesaj'"></button>
            </div>

            <form method="POST" action="{{ route('panel.messages.store') }}" class="mt-5 space-y-4" x-show="open" x-cloak>
                @csrf
                <div>
                    <x-input-label :value="'Konu'" />
                    <x-text-input name="subject" value="{{ old('subject') }}" required maxlength="150" placeholder="Örn. Alt domain talebi hakkında" />
                    <x-input-error :messages="$errors->get('subject')" />
                </div>
                <div>
                    <x-input-label :value="'Mesajınız'" />
                    <textarea name="body" rows="5" class="field" required maxlength="5000">{{ old('body') }}</textarea>
                    <x-input-error :messages="$errors->get('body')" />
                </div>
                <button class="btn-primary">Gönder</button>
            </form>
        </div>

        {{-- Konu listesi --}}
        <div class="{{ $card }} !p-0 overflow-hidden">
            @forelse ($conversations as $conversation)
                <a href="{{ route('panel.messages.show', $conversation) }}"
                   class="flex items-start gap-4 border-b border-ink-100 px-6 py-5 transition last:border-0 hover:bg-ink-50">
                    <span @class([
                        'mt-1 grid h-9 w-9 shrink-0 place-items-center rounded-full text-xs font-bold',
                        'bg-red-500 text-white' => $conversation->unread_for_user > 0,
                        'bg-ink-100 text-ink-500' => $conversation->unread_for_user === 0,
                    ])>
                        {{ $conversation->unread_for_user > 0 ? $conversation->unread_for_user : $conversation->messages_count }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="truncate font-semibold text-ink-900">{{ $conversation->subject }}</span>
                            @if (! $conversation->isOpen())
                                <span class="rounded-full bg-ink-100 px-2 py-0.5 text-[11px] font-semibold text-ink-500">Kapalı</span>
                            @endif
                        </span>
                        <span class="mt-0.5 block text-xs text-ink-400">
                            {{ $conversation->last_message_at?->diffForHumans() ?? $conversation->created_at->diffForHumans() }}
                            @if ($conversation->last_sender_role === 'admin')
                                · destek ekibi yanıtladı
                            @endif
                        </span>
                    </span>
                    <span class="mt-1 text-ink-300">→</span>
                </a>
            @empty
                <div class="px-6 py-14 text-center">
                    <p class="text-sm text-ink-400">Henüz mesajınız yok. Yukarıdan yeni bir konu açabilirsiniz.</p>
                </div>
            @endforelse
        </div>

        {{ $conversations->links() }}
    </div>
</x-app-layout>
