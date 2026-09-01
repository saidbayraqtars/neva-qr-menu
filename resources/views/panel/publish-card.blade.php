{{--
    Yayına alma kartı — dört ayrı durum, tek kart:

    1) YAYINDA        : adres + doğrulama durumu (giriş alanı YOK)
    2) ONAYDA         : "talebiniz inceleniyor" (giriş alanı TAMAMEN GİZLİ)
    3) TALEP HAZIR    : ayrılan ad + "Onaya Gönder"  (ad değiştirilebilir)
    4) BOŞ            : alt domain giriş alanı + ANLIK müsaitlik kontrolü

    Paketinde alt domain olmayan kullanıcı bu kartı hiç görmez (dashboard'da elenir).
--}}
@php
    $root = config('neva.root_domain');
    $awaiting = $restaurant->isAwaitingApproval();
    $live = $restaurant->isLive();
@endphp

<div class="{{ $card }}">

    @if ($live)
        {{-- ===== 1) YAYINDA ===== --}}
        <div class="flex items-start gap-3">
            <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-100">
                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h3 class="font-display text-lg text-ink-900">Menünüz yayında</h3>
                <a href="{{ tenant_domain($restaurant) }}" target="_blank" rel="noopener"
                   class="mt-1 block truncate font-mono text-sm font-semibold text-gold-700 hover:underline">
                    {{ $restaurant->subdomain }}.{{ $root }} ↗
                </a>

                @php
                    $publish = match ($restaurant->publish_status) {
                        'live' => ['Adres doğrulandı ve çalışıyor.', 'text-emerald-600'],
                        'failed' => ['Otomatik doğrulama başarısız — ekibimiz kontrol ediyor.', 'text-amber-600'],
                        'queued', 'dns', 'verifying' => ['Yayına alma ve doğrulama sürüyor…', 'text-ink-400'],
                        default => [null, ''],
                    };
                @endphp
                @if ($publish[0])
                    <p class="mt-2 text-xs {{ $publish[1] }}">{{ $publish[0] }}</p>
                @endif

                @if ($restaurant->verified_at)
                    <p class="mt-1 text-xs text-ink-400">Son doğrulama: {{ $restaurant->last_health_check_at?->diffForHumans() ?? $restaurant->verified_at->diffForHumans() }}</p>
                @endif

                <p class="mt-3 text-sm leading-relaxed text-ink-500">
                    Panelde yaptığınız her değişiklik (ürün, fiyat, şablon) bu adrese anında yansır.
                </p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('panel.qr.index') }}" class="btn-gold">QR kodunu indir</a>
                    <a href="{{ route('panel.design.edit', ['mode' => 'edit']) }}" class="btn-ghost">Tasarımı düzenle</a>
                </div>
            </div>
        </div>

    @elseif ($awaiting)
        {{-- ===== 2) ONAYDA — giriş alanı tamamen kaldırıldı ===== --}}
        <div class="flex items-start gap-3">
            <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-full bg-amber-100">
                <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h3 class="font-display text-lg text-ink-900">Talebiniz inceleniyor</h3>
                @if ($pending)
                    <p class="mt-1 font-mono text-sm font-semibold text-ink-900">{{ $pending->requested_subdomain }}.{{ $root }}</p>
                @endif
                <p class="mt-3 text-sm leading-relaxed text-ink-500">
                    Onaylandığı anda adresiniz otomatik olarak yayına alınır, arka planda çalışıp
                    çalışmadığı test edilir ve size e-posta ile haber veririz. Bu sırada menünüzü
                    düzenlemeye devam edebilirsiniz — değişiklikler yayına birlikte çıkar.
                </p>
                <p class="mt-3 text-xs text-ink-400">
                    Gönderim: {{ $restaurant->submitted_at?->diffForHumans() ?? 'az önce' }}
                </p>
                <div class="mt-4">
                    <a href="{{ route('panel.messages.index') }}" class="btn-ghost">Ekibe mesaj yaz</a>
                </div>
            </div>
        </div>

    @else
        {{-- ===== 3-4) TALEP / GİRİŞ ===== --}}
        <h3 class="font-display text-lg text-ink-900">Yayına al</h3>
        <p class="mt-1.5 text-sm text-ink-500">
            İşletmeniz için istediğiniz alan adını yazın, onaya gönderin. Onaylandığında
            adresiniz otomatik olarak canlıya alınır.
        </p>

        <form method="POST" action="{{ route('panel.subdomain.store') }}" class="mt-5"
              x-data="subdomainCheck(@js($pending?->requested_subdomain ?? $restaurant->subdomain ?? ''))">
            @csrf
            <x-input-label :value="'Alan adı'" />

            <div class="mt-1 flex flex-wrap items-center gap-2">
                <div class="flex min-w-[220px] flex-1 items-center rounded-xl bg-white ring-1 ring-inset transition"
                     :class="ringClass">
                    <input name="requested_subdomain" placeholder="isletmeniz" autocomplete="off" spellcheck="false"
                           x-model="raw" @input.debounce.400ms="check()"
                           class="w-full border-0 bg-transparent px-3.5 py-2.5 font-mono text-sm text-ink-900 focus:outline-none focus:ring-0">
                    <span class="whitespace-nowrap pr-3.5 text-sm text-ink-400">.{{ $root }}</span>
                </div>
                <button class="btn-ghost" :disabled="state === 'checking'">Kaydet</button>
            </div>

            {{-- ANLIK durum: kullanıcı yazarken müsaitlik kontrol edilir --}}
            <p class="mt-2 min-h-[1.25rem] text-xs" x-show="message" x-cloak :class="messageClass" x-text="message"></p>

            {{-- Sunucu tarafı doğrulama hatası (JS kapalı olsa da çalışır) --}}
            <x-input-error :messages="$errors->get('requested_subdomain')" class="mt-2" />
        </form>

        @if ($pending)
            <div class="mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200">
                <span class="font-mono font-semibold">{{ $pending->requested_subdomain }}.{{ $root }}</span>
                sizin için ayrıldı. Menünüz hazırsa onaya gönderebilirsiniz.
            </div>
        @endif

        <form method="POST" action="{{ route('panel.submit') }}" class="mt-5 border-t border-ink-100 pt-5">
            @csrf
            <button class="btn-gold w-full sm:w-auto sm:px-8">Onaya Gönder</button>
            <x-input-error :messages="$errors->get('submit')" class="mt-2" />
            <p class="mt-2 text-xs text-ink-400">
                En az bir kategori ve bir ürün gerekir. Gönderdikten sonra bu alan kapanır.
            </p>
        </form>

        @if ($restaurant->status === 'rejected' && $restaurant->rejection_reason)
            <p class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-xs text-red-600 ring-1 ring-red-100">
                Reddedildi: {{ $restaurant->rejection_reason }}
            </p>
        @endif

        @once
            @push('scripts')
                <script>
                    // Alt domain anlık müsaitlik kontrolü (debounce'lu).
                    // Sunucu normalize edilmiş adı ve insan diliyle mesajı döner.
                    function subdomainCheck(initial) {
                        return {
                            raw: initial || '',
                            state: 'idle',      // idle | checking | ok | taken | reserved | invalid
                            message: '',
                            get ringClass() {
                                return {
                                    ok: 'ring-emerald-400 ring-2',
                                    taken: 'ring-red-400 ring-2',
                                    reserved: 'ring-red-400 ring-2',
                                    invalid: 'ring-amber-400 ring-2',
                                }[this.state] || 'ring-ink-200 focus-within:ring-2 focus-within:ring-gold-500';
                            },
                            get messageClass() {
                                return {
                                    ok: 'text-emerald-600',
                                    taken: 'text-red-600',
                                    reserved: 'text-red-600',
                                    invalid: 'text-amber-600',
                                }[this.state] || 'text-ink-400';
                            },
                            async check() {
                                const value = (this.raw || '').trim();
                                if (!value) { this.state = 'idle'; this.message = ''; return; }

                                this.state = 'checking';
                                this.message = 'Kontrol ediliyor…';

                                try {
                                    const url = '{{ route('panel.subdomain.availability') }}?label=' + encodeURIComponent(value);
                                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });

                                    if (res.status === 429) {
                                        this.state = 'idle';
                                        this.message = 'Çok fazla deneme — birkaç saniye bekleyin.';
                                        return;
                                    }
                                    if (!res.ok) { this.state = 'idle'; this.message = ''; return; }

                                    const data = await res.json();
                                    this.state = data.status;
                                    this.message = data.message;

                                    // Sunucunun temizlediği hali kullanıcıya göster (sessizce değiştirmeyelim).
                                    if (data.label && data.label !== value) this.raw = data.label;
                                } catch (e) {
                                    this.state = 'idle';
                                    this.message = '';
                                }
                            },
                        };
                    }
                </script>
            @endpush
        @endonce
    @endif
</div>
