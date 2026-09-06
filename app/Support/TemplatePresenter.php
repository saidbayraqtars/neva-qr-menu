<?php

namespace App\Support;

use App\Models\Restaurant;
use Illuminate\Support\Facades\Storage;

/**
 * Bir restoranı + (opsiyonel) taslak override'ları alıp şablonun render
 * bağlamını üretir: layout, hero/kart stilleri, CSS token'ları, font linkleri.
 *
 * Hem canlı kiracı menüsü hem panel canlı önizlemesi bunu kullanır — böylece
 * ikisi BİREBİR aynıdır.
 */
class TemplatePresenter
{
    public array $config;

    public string $key;

    public function __construct(
        public Restaurant $restaurant,
        public array $overrides = [],
    ) {
        $templates = config('neva.templates');
        $key = $overrides['template'] ?? $restaurant->template;
        $this->key = isset($templates[$key]) ? $key : Restaurant::DEFAULT_TEMPLATE;
        $this->config = $templates[$this->key];
    }

    public function disk()
    {
        return Storage::disk(config('neva.uploads.disk'));
    }

    /**
     * Ürün görseli. Akıllı fallback:
     *   1) ürünün kendi fotoğrafı  →  2) işletme logosu  →  3) null (kart saf tipografik moda geçer).
     * "Gizli" görsel stili seçiliyse her koşulda null.
     */
    public function productImage($product): ?string
    {
        if ($this->imageStyle() === 'hidden') {
            return null;
        }

        if (! empty($product->image_path)) {
            return media_url($product->image_path);
        }

        return $this->logoUrl();
    }

    /** productImage() bir ürün fotoğrafı değil de logo fallback'i mi döndürüyor? (stil ipucu için) */
    public function productImageIsFallback($product): bool
    {
        return empty($product->image_path) && $this->imageStyle() !== 'hidden' && $this->logoUrl() !== null;
    }

    public function get(string $field, $default = null)
    {
        return $this->overrides[$field] ?? $this->restaurant->{$field} ?? $default;
    }

    /**
     * ŞABLONA ÖZEL "Gelişmiş Dokunuşlar" değeri.
     * Öncelik: taslak override (canlı önizleme) → kayıtlı template_settings[aktif şablon] → varsayılan.
     * Başka şablonların ayarları ASLA sızmaz.
     */
    public function setting(string $key, $default = null)
    {
        if (array_key_exists($key, $this->overrides) && $this->overrides[$key] !== null && $this->overrides[$key] !== '') {
            return $this->overrides[$key];
        }

        $saved = $this->restaurant->template_settings[$this->key][$key] ?? null;

        return ($saved !== null && $saved !== '') ? $saved : $default;
    }

    /** Bu şablonun iskeleti bu "Gelişmiş Dokunuşlar" kontrolünü destekliyor mu? */
    public function supports(string $control): bool
    {
        return ! in_array($control, $this->config['locks'] ?? [], true);
    }

    public function layout(): string
    {
        return $this->config['layout'];
    }

    public function accent(): string
    {
        // İZOLASYON: kayıtlı şablona özel accent yoksa ŞABLONUN kendi paleti gelir
        // (restoranın düz "son kullanılan" rengi başka şablona sızmaz).
        $c = $this->setting('accent_color') ?: $this->config['palette']['accent'];

        return $this->safeHex($c, $this->config['palette']['accent']);
    }

    /** Başlık rengi — şablona özel seçim varsa o, yoksa şablon paletinin başlığı. */
    public function heading(): string
    {
        if (! $this->supports('heading_color')) {
            return $this->config['palette']['heading'];
        }

        return $this->safeHex($this->setting('heading_color'), $this->config['palette']['heading']);
    }

    /** Gövde metni rengi — şablona özel seçim varsa o, yoksa token ink. */
    public function textColor(): string
    {
        if (! $this->supports('text_color')) {
            return $this->config['tokens']['ink'];
        }

        return $this->safeHex($this->setting('text_color'), $this->config['tokens']['ink']);
    }

    private function pickedFont(): ?string
    {
        // İZOLASYON: yalnızca bu şablona kayıtlı font uygulanır; yoksa şablonun kendi fontu.
        return $this->resolveFont($this->setting('font_family'));
    }

    public function fontDisplay(): string
    {
        return $this->pickedFont() ?? $this->config['font_display'];
    }

    public function fontBody(): string
    {
        // Kullanıcı bir font seçtiyse gövde de onu kullanır — ANCAK display / el yazısı
        // fontları gövde metni için okunaksız olduğundan bu durumda şablonun gövde fontuna düşeriz.
        $picked = $this->pickedFont();
        if ($picked) {
            $type = config('neva.fonts.'.$picked.'.type', 'sans');

            return in_array($type, ['display', 'script'], true) ? $this->config['font_body'] : $picked;
        }

        return $this->config['font_body'];
    }

    private function resolveFont(?string $name): ?string
    {
        return ($name && isset(config('neva.fonts')[$name])) ? $name : null;
    }

    /** <head> için Google Fonts URL'i. */
    public function fontsHref(): string
    {
        $fonts = config('neva.fonts');
        $names = array_unique(array_filter([$this->fontDisplay(), $this->fontBody(), 'Inter']));
        $families = [];
        foreach ($names as $n) {
            if (isset($fonts[$n])) {
                $families[] = 'family='.$fonts[$n]['q'];
            }
        }

        return 'https://fonts.googleapis.com/css2?'.implode('&', $families).'&display=swap';
    }

    public function fontStack(string $name): string
    {
        return config('neva.fonts.'.$name.'.stack') ?? "'Inter', system-ui, sans-serif";
    }

    public function logoUrl(): ?string
    {
        if (! empty($this->overrides['logo_data'])) {
            return $this->overrides['logo_data']; // panelden yeni seçilen (data URI)
        }

        return media_url($this->restaurant->logo_path);
    }

    public function supportsCover(): bool
    {
        return (bool) ($this->config['cover'] ?? false);
    }

    public function family(): string
    {
        return $this->config['family'] ?? 'list';
    }

    /* ---- Şablon içi mikro-varyasyonlar ("Gelişmiş Dokunuşlar") ---- */

    private function variation(string $field): string
    {
        $default = config("neva.variations.$field.default", 'auto');

        // Şablon bu kontrolü desteklemiyorsa DAİMA varsayılan (panelde de gizli).
        if (! $this->supports($field)) {
            return $default;
        }

        $value = $this->setting($field, $default) ?: $default;
        $allowed = array_keys(config("neva.variations.$field.options", []));

        return in_array($value, $allowed, true) ? $value : $default;
    }

    /** solid | dots | stripes | noise | glow */
    public function bgPattern(): string
    {
        return $this->variation('bg_pattern');
    }

    /** fade | slide | pop | bounce | blur */
    public function entryAnim(): string
    {
        return $this->variation('entry_anim');
    }

    /** auto | small | hero | hidden */
    public function imageStyle(): string
    {
        return $this->variation('image_style');
    }

    /** auto | light | regular | bold */
    public function headingWeight(): string
    {
        return $this->variation('heading_weight');
    }

    /** auto | sm | md | lg */
    public function textSize(): string
    {
        return $this->variation('text_size');
    }

    /** Köşe yarıçapı — kullanıcı seçimi varsa onu, yoksa şablon token'ını döndürür. */
    public function radius(): string
    {
        $choice = $this->variation('corner_radius');

        return config("neva.radius_map.$choice") ?? ($this->config['tokens']['radius'] ?? '12px');
    }

    /**
     * Sayfa arka plan rengi: kullanıcı özel bir renk seçtiyse o, yoksa şablonun token'ı.
     * (Kapak/hero görseli varsa o ilgili alanda görsel öncelikli kalır — bu yalnızca zemin.)
     */
    public function bgColor(): string
    {
        if (! $this->supports('bg_color')) {
            return $this->config['tokens']['bg'];
        }

        return $this->safeHex($this->setting('bg_color'), $this->config['tokens']['bg']);
    }

    /** none | scale | glow | fade */
    public function animation(): string
    {
        return $this->config['animation'] ?? 'none';
    }

    /** list | grid | masonry */
    public function layoutType(): string
    {
        return $this->config['layout_type'] ?? 'list';
    }

    /**
     * Bu şablon ürün kısa açıklamasını gösterir mi?
     * Minimalist / mono şablonlar tasarımın saflığı için gizler (config: 'hide_desc' => true).
     * Boş açıklama zaten iskelette @if ile hiç basılmaz.
     */
    public function showsDescription(): bool
    {
        return ! ($this->config['hide_desc'] ?? false);
    }

    public function coverUrl(): ?string
    {
        // Şablon kapak görselini desteklemiyorsa hiçbir koşulda render etme.
        if (! $this->supportsCover()) {
            return null;
        }

        if (! empty($this->overrides['cover_data'])) {
            return $this->overrides['cover_data'];
        }

        return media_url($this->restaurant->cover_path);
    }

    public function logoScale(): float
    {
        $size = $this->overrides['logo_size'] ?? $this->restaurant->logo_size ?: 'medium';

        return (float) (config('neva.logo_sizes.'.$size.'.scale') ?? 1.0);
    }

    /** :root'a basılacak CSS custom property'leri. */
    public function cssVars(): array
    {
        $t = $this->config['tokens'];

        return [
            '--t-bg' => $this->bgColor(),
            '--t-surface' => $t['surface'],
            '--t-surface-2' => $t['surface2'],
            '--t-ink' => $this->textColor(),
            '--t-text' => $this->textColor(),
            '--t-ink-soft' => $t['ink_soft'],
            '--t-border' => $t['border'],
            '--t-radius' => $this->radius(),
            '--t-accent' => $this->accent(),
            '--t-accent-ink' => $this->readableOn($this->accent()),
            '--t-heading' => $this->heading(),
            '--t-logo-scale' => $this->logoScale(),
            '--t-shadow' => $this->config['mood'] === 'dark' ? '0 24px 60px -24px rgba(0,0,0,.7)' : '0 20px 44px -22px rgba(20,20,30,.22)',
            '--t-shadow-sm' => $this->config['mood'] === 'dark' ? '0 8px 24px -12px rgba(0,0,0,.6)' : '0 8px 20px -12px rgba(20,20,30,.16)',
            '--t-font-display' => $this->fontStack($this->fontDisplay()),
            '--t-font-body' => $this->fontStack($this->fontBody()),
        ];
    }

    public function cssVarString(): string
    {
        return collect($this->cssVars())->map(fn ($v, $k) => "$k: $v;")->implode(' ');
    }

    public function dataAttrs(string $view = 'phone'): array
    {
        return [
            'data-tpl' => $this->key,
            'data-mood' => $this->config['mood'],
            'data-upper' => $this->config['uppercase'] ? '1' : '0',
            'data-anim' => $this->animation(),
            'data-layout-type' => $this->layoutType(),
            'data-bg' => $this->bgPattern(),
            'data-entry' => $this->entryAnim(),
            'data-img' => $this->imageStyle(),
            'data-hw' => $this->headingWeight(),
            'data-ts' => $this->textSize(),
            // 'auto': sunucu telefon düzeniyle basar, istemci geniş ekranda 'desktop'a çevirir.
            'data-view' => $view === 'desktop' ? 'desktop' : 'phone',
            'data-auto-view' => $view === 'auto' ? '1' : '0',
        ];
    }

    public function dataAttrString(string $view = 'phone'): string
    {
        return collect($this->dataAttrs($view))->map(fn ($v, $k) => "$k=\"$v\"")->implode(' ');
    }

    public function label(): string
    {
        return $this->config['label'];
    }

    /* --------------------------------------------------------------------- */

    private function safeHex(?string $value, string $fallback): string
    {
        return (is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) ? $value : $fallback;
    }

    /** Verilen arka plan renginde okunur metin rengi (siyah/beyaz). */
    private function readableOn(string $hex): string
    {
        [$r, $g, $b] = [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.62 ? '#141414' : '#FFFFFF';
    }
}
