/**
 * Tasarım Stüdyosu — şablon galerisi (filtreli ızgara) + düzenleme modu + canlı önizleme.
 *
 * İşletme kimliği (ad / slogan / logo / iletişim) artık "İşletme Profili" sayfasında —
 * bu ekran YALNIZCA görünümle ilgilenir.
 *
 * "Gelişmiş Dokunuşlar" ŞABLONA ÖZELDİR: her ayar cfg.templateSettings[<şablon>]
 * altında saklanır, şablon değişince o şablonun kendi ayarları forma yüklenir.
 * Şablonun desteklemediği kontrol panelde gizlidir (supports()).
 */
export default function designStudio(cfg) {
    const RELOAD_FIELDS = ['font_family', 'currency', 'show_prices', 'show_calories'];
    const LIVE_FIELDS = ['accent_color', 'logo_size'];
    const VARIATION_FIELDS = ['bg_pattern', 'entry_anim', 'image_style', 'corner_radius', 'heading_weight', 'text_size'];
    // renk kısayolu → { setting anahtarı, css değişkeni, meta varsayılan alanı }
    const COLORS = {
        bg: { key: 'bg_color', varName: '--t-bg', metaKey: 'bg' },
        heading: { key: 'heading_color', varName: '--t-heading', metaKey: 'headingC' },
        text: { key: 'text_color', varName: '--t-text', metaKey: 'textC' },
    };
    const DIMS = { phone: { w: 360, h: 660 }, desktop: { w: 1040, h: 720 } };
    const settingsFor = (key) => (cfg.templateSettings && cfg.templateSettings[key]) || {};

    return {
        mode: cfg.startMode || 'gallery',
        view: 'phone',
        selected: cfg.template,
        templates: cfg.templates,
        meta: cfg.meta,
        loading: true,
        scale: 1,
        accentHex: (cfg.currentAccent || cfg.templateAccents[cfg.template] || '#C8A96A'),
        coverState: 'saved',
        _timer: null,
        _ro: null,

        /* ---------- galeri filtresi + tek-kart slider ---------- */
        galleryFocus: 'all',   // all | visual | typographic
        galleryIndex: 0,       // filteredKeys içindeki konum
        get focusCounts() {
            const c = { all: this.templates.length, visual: 0, typographic: 0 };
            this.templates.forEach((k) => { c[this.meta[k].focus] = (c[this.meta[k].focus] || 0) + 1; });
            return c;
        },
        get filteredKeys() {
            if (this.galleryFocus === 'all') return this.templates;
            return this.templates.filter((k) => this.meta[k].focus === this.galleryFocus);
        },
        get currentKey() {
            return this.filteredKeys[this.galleryIndex] || this.filteredKeys[0] || this.selected;
        },
        _clampIndex() {
            const n = Math.max(0, this.filteredKeys.length - 1);
            this.galleryIndex = Math.max(0, Math.min(n, this.galleryIndex));
        },
        // Ekranda tek şablon var; sağa/sola kaydır.
        slide(dir) {
            this.galleryIndex += dir;
            this._clampIndex();
        },
        jumpTo(i) { this.galleryIndex = i; this._clampIndex(); },
        setFocus(f) {
            this.galleryFocus = f;
            // filtre değişince: seçili şablon listede varsa ona odaklan, yoksa başa dön
            const idx = this.filteredKeys.indexOf(this.selected);
            this.galleryIndex = idx >= 0 ? idx : 0;
        },
        syncGalleryToSelected() {
            const idx = this.filteredKeys.indexOf(this.selected);
            this.galleryIndex = idx >= 0 ? idx : 0;
        },

        /* ---------- şablona özel kabiliyet ---------- */
        supports(control) {
            const map = cfg.templateSupports[this.selected] || {};
            return map[control] !== false;
        },
        anySupported() {
            const map = cfg.templateSupports[this.selected] || {};
            return Object.values(map).some(Boolean);
        },

        /* ---------- özel renkler: bg / heading / text ---------- */
        colorOn: {
            bg: !!(cfg.colors && cfg.colors.bg),
            heading: !!(cfg.colors && cfg.colors.heading),
            text: !!(cfg.colors && cfg.colors.text),
        },
        colorHex: {
            bg: (cfg.colors && cfg.colors.bg) || null,
            heading: (cfg.colors && cfg.colors.heading) || null,
            text: (cfg.colors && cfg.colors.text) || null,
        },

        templateColor(name) {
            const m = this.meta[this.selected] || {};
            return m[COLORS[name].metaKey] || '#888888';
        },
        colorValue(name) { return this.colorHex[name] || this.templateColor(name); },
        effectiveColor(name) {
            return (this.colorOn[name] && this.supports(COLORS[name].key)) ? this.colorValue(name) : this.templateColor(name);
        },
        toggleColor(name) {
            if (this.colorOn[name] && !this.colorHex[name]) this.colorHex[name] = this.templateColor(name);
            this.pushColors();
        },
        onColor(name, e) { this.colorHex[name] = e.target.value; this.colorOn[name] = true; this.pushColors(); },
        pushColors() {
            const vars = {};
            Object.keys(COLORS).forEach((n) => { vars[COLORS[n].varName] = this.effectiveColor(n); });
            this.post({ vars });
        },

        /* ---------- font seçici (aranabilir) ---------- */
        fonts: cfg.fonts || [],
        fontName: cfg.currentFont || cfg.templateFonts[cfg.template] || 'Inter',
        fontOpen: false,
        fontQuery: '',
        fontType: 'all',

        get fontTypeLabels() {
            return { all: 'Tümü', sans: 'Sans-serif', serif: 'Serif', display: 'Display', script: 'El yazısı' };
        },
        get filteredFonts() {
            const q = this.fontQuery.trim().toLowerCase();
            return this.fonts.filter((f) =>
                (this.fontType === 'all' || f.type === this.fontType) &&
                (q === '' || f.name.toLowerCase().includes(q)),
            );
        },
        fontStack(name) {
            return (this.fonts.find((f) => f.name === name) || {}).stack || 'system-ui, sans-serif';
        },
        pickFont(name) {
            this.fontName = name;
            const el = this.field('font_family');
            if (el) el.value = name;
            this.fontOpen = false;
            this.fontQuery = '';
            this.reloadDebounced();
        },

        /* ---------- init ---------- */
        init() {
            const acc = this.field('accent_color'); if (acc) this.accentHex = acc.value;
            const fnt = this.field('font_family'); if (fnt && fnt.value) this.fontName = fnt.value;
            window.addEventListener('message', (e) => {
                if (e.data?.type === 'neva-preview-ready') this.pushAll();
            });
            this.$refs.frame?.addEventListener('load', () => { this.loading = false; this.pushAll(); });
            if (this.$refs.host) {
                this._ro = new ResizeObserver(() => this.fit());
                this._ro.observe(this.$refs.host);
            }
            this.syncGalleryToSelected();
            if (this.mode === 'edit') this.$nextTick(() => { this.fit(); this.reload(); });
        },

        /* ---------- galeri ---------- */
        get selectedMeta() { return this.meta[this.selected]; },
        // İZOLASYON: mini önizleme yalnızca template alır — kullanıcı ayarları geçmez.
        miniUrl(key) { return `${cfg.previewUrl}?template=${key}&view=phone&mini=1`; },

        /**
         * Şablon seç / geç. Hedef şablonun KAYITLI ayarlarını (yoksa şablon
         * varsayılanlarını) forma yükler — başka şablonun dokunuşları sızmaz.
         */
        chooseTemplate(key) {
            this.selected = key;
            const s = settingsFor(key);

            // accent
            const acc = this.field('accent_color');
            const accent = s.accent_color || cfg.templateAccents[key] || '#C8A96A';
            if (acc) acc.value = accent;
            this.accentHex = accent;

            // font
            const font = s.font_family || cfg.templateFonts[key] || 'Inter';
            this.fontName = font;
            const fnt = this.field('font_family'); if (fnt) fnt.value = font;

            // özel renkler (bg / heading / text)
            Object.keys(COLORS).forEach((n) => {
                const saved = s[COLORS[n].key];
                this.colorOn[n] = !!saved;
                this.colorHex[n] = saved || null;
            });

            // varyasyon select'leri
            VARIATION_FIELDS.forEach((f) => {
                const el = this.field(f);
                if (el) el.value = s[f] || (cfg.variationDefaults[f] ?? 'auto');
            });

            this.mode = 'edit';
            this.$nextTick(() => { this.fit(); this.reload(); });
        },

        backToGallery() {
            this.mode = 'gallery';
            this.syncGalleryToSelected();
        },

        /* ---------- düzenleme önizlemesi ---------- */
        get dims() { return DIMS[this.view]; },
        get hostHeight() { return Math.round(this.dims.h * this.scale); },

        fit() {
            const avail = this.$refs.host?.clientWidth || this.dims.w;
            this.scale = Math.min(1, avail / this.dims.w);
        },

        setView(v) {
            if (this.view === v) return;
            this.view = v;
            this.$nextTick(() => this.fit());
            this.reload();
        },

        field(name) { return this.$root.querySelector(`form [name="${name}"]`); },

        params() {
            const p = new URLSearchParams();
            p.set('view', this.view);
            p.set('template', this.selected);
            p.set('accent', (this.field('accent_color')?.value || '').replace('#', ''));
            p.set('font', this.field('font_family')?.value || '');
            p.set('logo_size', this.field('logo_size')?.value || 'medium');
            Object.keys(COLORS).forEach((n) => {
                const active = this.colorOn[n] && this.supports(COLORS[n].key) && this.colorHex[n];
                p.set(COLORS[n].key, active ? this.colorHex[n].replace('#', '') : '');
            });
            VARIATION_FIELDS.forEach((n) => p.set(n, this.supports(n) ? (this.field(n)?.value || '') : ''));
            return p;
        },

        reload() {
            if (!this.$refs.frame) return;
            this.loading = true;
            this.$refs.frame.src = `${cfg.previewUrl}?${this.params().toString()}&_=${Date.now()}`;
        },

        reloadDebounced() {
            clearTimeout(this._timer);
            this._timer = setTimeout(() => this.reload(), 260);
        },

        post(msg) {
            this.$refs.frame?.contentWindow?.postMessage({ type: 'neva-preview', ...msg }, '*');
        },

        pushLive() {
            const scale = cfg.logoScales[this.field('logo_size')?.value] ?? 1;
            this.post({ vars: {
                '--t-accent': this.field('accent_color')?.value || cfg.templateAccents[this.selected],
                '--t-logo-scale': String(scale),
            } });
        },

        // Mikro-varyasyonlar → postMessage (reload gerektirmez). Desteklenmeyen kontrol = varsayılan.
        pushVariations() {
            const v = (n) => (this.supports(n) ? (this.field(n)?.value || '') : '');
            const radiusChoice = v('corner_radius') || 'auto';
            const radius = (cfg.radiusMap && cfg.radiusMap[radiusChoice]) || cfg.templateRadii?.[this.selected] || '12px';
            this.post({
                attrs: {
                    'data-bg': (this.supports('bg_pattern') && v('bg_pattern')) || 'solid',
                    'data-entry': v('entry_anim') || 'fade',
                    'data-img': (this.supports('image_style') && v('image_style')) || 'auto',
                    'data-hw': v('heading_weight') || 'auto',
                    'data-ts': v('text_size') || 'auto',
                },
                vars: { '--t-radius': radius },
            });
        },

        pushAll() {
            this.pushLive();
            this.pushVariations();
            this.pushColors();
            if (this.coverState === 'removed') this.post({ cover: null });
            else if (this.coverState !== 'saved') this.post({ cover: this.coverState });
        },

        onField(e) {
            const name = e.target.name;
            if (name === 'accent_color') this.accentHex = e.target.value;
            if (LIVE_FIELDS.includes(name)) { this.pushLive(); return; }
            if (VARIATION_FIELDS.includes(name)) { this.pushVariations(); return; }
            if (RELOAD_FIELDS.includes(name)) this.reloadDebounced();
        },

        onImage(e, key) {
            const file = e.target.files?.[0];
            if (!file) { this.coverState = 'removed'; this.post({ [key]: null }); return; }
            const reader = new FileReader();
            reader.onload = (ev) => { this.coverState = ev.target.result; this.post({ [key]: ev.target.result }); };
            reader.readAsDataURL(file);
        },

        removeAsset(key) {
            const input = this.field(key);
            if (input) input.value = '';
            this.coverState = 'removed';
            this.post({ [key]: null });
        },
    };
}
