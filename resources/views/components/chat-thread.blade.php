@props([
    'conversation',
    'side' => 'user',      // bu ekranı kim görüyor: user | admin
    'replyAction',
    'pollUrl',
    'closed' => false,
])

{{--
    Çift yönlü mesaj akışı. Karşı taraf yazdığında 5 saniyede bir yeni mesajlar
    çekilir ve akışa eklenir — sayfa yenilemeye gerek yok.
--}}
<div x-data="chatThread({
        pollUrl: @js($pollUrl),
        lastId: {{ (int) $conversation->messages->max('id') }},
        side: @js($side),
     })"
     x-init="start()">

    <div class="max-h-[60vh] space-y-4 overflow-y-auto p-6" x-ref="stream">
        @foreach ($conversation->messages as $message)
            @php $mine = $message->sender_role === $side; @endphp
            <div @class(['flex', 'justify-end' => $mine])>
                <div @class([
                    'max-w-[80%] rounded-2xl px-4 py-3 text-sm leading-relaxed',
                    'bg-ink-900 text-white' => $mine,
                    'bg-ink-50 text-ink-800 ring-1 ring-ink-100' => ! $mine,
                ])>
                    <p class="whitespace-pre-line">{{ $message->body }}</p>
                    <p @class([
                        'mt-1.5 text-[11px]',
                        'text-white/50' => $mine,
                        'text-ink-400' => ! $mine,
                    ])>
                        {{ $message->sender_role === 'admin' ? 'Destek ekibi' : ($message->sender?->name ?? 'İşletme') }}
                        · {{ $message->created_at->format('d.m.Y H:i') }}
                    </p>
                </div>
            </div>
        @endforeach

        {{-- Polling ile gelen yeni mesajlar buraya eklenir --}}
        <template x-for="m in incoming" :key="m.id">
            <div :class="m.role === '{{ $side }}' ? 'flex justify-end' : 'flex'">
                <div :class="m.role === '{{ $side }}'
                        ? 'max-w-[80%] rounded-2xl bg-ink-900 px-4 py-3 text-sm leading-relaxed text-white'
                        : 'max-w-[80%] rounded-2xl bg-ink-50 px-4 py-3 text-sm leading-relaxed text-ink-800 ring-1 ring-ink-100'">
                    <p class="whitespace-pre-line" x-text="m.body"></p>
                    <p class="mt-1.5 text-[11px] opacity-60" x-text="(m.role === 'admin' ? 'Destek ekibi' : 'İşletme') + ' · ' + m.at"></p>
                </div>
            </div>
        </template>
    </div>

    @if ($closed)
        <div class="border-t border-ink-100 bg-ink-50 px-6 py-5 text-center text-sm text-ink-500">
            Bu görüşme kapatıldı. Yeni bir konu açabilirsiniz.
        </div>
    @else
        <form method="POST" action="{{ $replyAction }}" class="border-t border-ink-100 p-6">
            @csrf
            <textarea name="body" rows="3" class="field" required maxlength="5000"
                      placeholder="Mesajınızı yazın…"
                      @keydown.meta.enter="$el.form.requestSubmit()"
                      @keydown.ctrl.enter="$el.form.requestSubmit()"></textarea>
            <x-input-error :messages="$errors->get('body')" class="mt-2" />
            <div class="mt-3 flex items-center justify-between gap-3">
                <p class="text-xs text-ink-400">Ctrl/⌘ + Enter ile gönderebilirsiniz.</p>
                <button class="btn-primary">Gönder</button>
            </div>
        </form>
    @endif
</div>

@once
    @push('scripts')
        <script>
            // Basit polling tabanlı canlı akış. (Faz-2: Laravel Reverb / WebSocket)
            function chatThread({ pollUrl, lastId, side }) {
                return {
                    incoming: [],
                    lastId: lastId,
                    timer: null,
                    start() {
                        this.scrollDown();
                        this.timer = setInterval(() => this.poll(), 5000);
                        document.addEventListener('visibilitychange', () => {
                            if (document.visibilityState === 'visible') this.poll();
                        });
                    },
                    async poll() {
                        try {
                            const res = await fetch(pollUrl + '?after=' + this.lastId, {
                                headers: { 'Accept': 'application/json' },
                            });
                            if (!res.ok) return;

                            const data = await res.json();
                            if (!data.messages || !data.messages.length) return;

                            for (const m of data.messages) {
                                this.incoming.push(m);
                                if (m.id > this.lastId) this.lastId = m.id;
                            }
                            this.$nextTick(() => this.scrollDown());
                        } catch (e) { /* sessizce yut — bir sonraki turda tekrar dener */ }
                    },
                    scrollDown() {
                        const el = this.$refs.stream;
                        if (el) el.scrollTop = el.scrollHeight;
                    },
                };
            }
        </script>
    @endpush
@endonce
