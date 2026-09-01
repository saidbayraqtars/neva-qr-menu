<x-app-layout title="İletişim Formu">
    <x-slot name="header">İletişim formu kayıtları</x-slot>

    @php $tabs = ['new' => 'Yeni', 'read' => 'Okundu', 'archived' => 'Arşiv', 'all' => 'Tümü']; @endphp

    <div class="mx-auto max-w-5xl space-y-5">
        <div class="flex flex-wrap gap-2">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin.contact.index', ['status' => $key]) }}"
                   class="rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ $status === $key ? 'bg-ink-900 text-white' : 'bg-white text-ink-500 ring-1 ring-ink-100 hover:text-ink-900' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @forelse ($messages as $message)
            <div class="rounded-2xl bg-white p-6 ring-1 ring-ink-100">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-ink-900">{{ $message->name }}</p>
                        <p class="text-xs text-ink-400">
                            <a href="mailto:{{ $message->email }}" class="hover:underline">{{ $message->email }}</a>
                            @if ($message->phone) · {{ $message->phone }} @endif
                            · {{ $message->created_at->format('d.m.Y H:i') }}
                        </p>
                    </div>
                    <span @class([
                        'rounded-full px-2.5 py-1 text-xs font-semibold',
                        'bg-amber-100 text-amber-700' => $message->status === 'new',
                        'bg-emerald-100 text-emerald-700' => $message->status === 'read',
                        'bg-ink-100 text-ink-500' => $message->status === 'archived',
                    ])>
                        {{ ['new' => 'Yeni', 'read' => 'Okundu', 'archived' => 'Arşiv'][$message->status] ?? $message->status }}
                    </span>
                </div>

                @if ($message->subject)
                    <p class="mt-3 text-sm font-semibold text-ink-800">{{ $message->subject }}</p>
                @endif
                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-ink-600">{{ $message->body }}</p>

                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-ink-100 pt-4">
                    @foreach (['read' => 'Okundu işaretle', 'archived' => 'Arşivle', 'new' => 'Yeniye al'] as $value => $label)
                        @continue($message->status === $value)
                        <form method="POST" action="{{ route('admin.contact.update', $message) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $value }}">
                            <button class="btn-ghost px-3 py-1.5 text-xs">{{ $label }}</button>
                        </form>
                    @endforeach
                    <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'Neva-QR Menü')) }}"
                       class="btn-gold px-3 py-1.5 text-xs">E-posta ile yanıtla</a>

                    @if ($message->handler)
                        <span class="ml-auto text-xs text-ink-400">{{ $message->handler->name }} · {{ $message->handled_at?->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-white px-6 py-14 text-center text-sm text-ink-400 ring-1 ring-ink-100">
                Bu filtrede kayıt yok.
            </div>
        @endforelse

        {{ $messages->links() }}
    </div>
</x-app-layout>
