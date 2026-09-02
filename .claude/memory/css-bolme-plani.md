---
name: css-bolme-plani
description: Menü sayfası 202 KB CSS + 117 KB JS yüklüyor; app.css/menu.css ayrımı için keşif tamam, uygulama 2026-09-02'de yarım kaldı
metadata:
  type: project
---

**DURUM: keşif bitti, hiçbir dosyaya dokunulmadı. Devam edilecek nokta burası.**

## Sorun

Canlı menü sayfası (`components/templates/shell.blade.php`) `@vite(['resources/css/app.css',
'resources/js/app.js'])` çağırıyor. Yani QR ile açılan her menü:

- **202 KB CSS** (gzip 34 KB) — içinde pazarlama sitesinin tüm Tailwind utility'leri,
  bento/hero/landing CSS'i, panel stilleri, hukuki metin tipografisi
- **117 KB JS** (gzip 42 KB) — Alpine + panel scriptleri

indiriyor. Menünün ihtiyacı olan bunun küçük bir parçası. LCP'nin kötü olmasının
asıl sebebi bu; "40 şablonun CSS'i tek bundle'da" tespiti eksikti — **as
büyük yük Tailwind utility'leri ve pazarlama CSS'i.**

## Keşif sonuçları (doğrulandı)

`resources/css/app.css` banner yorumlarıyla **zaten temiz bölünmüş**
(satır numaraları f6a43c7 + bu oturumun eklemeleri itibarıyla; banner metinleri
satır numarasından daha güvenilir referans):

| Satır | Banner | Ait olduğu taraf |
|-------|--------|------------------|
| 1–107 | `@import 'tailwindcss'` + `@theme` + `@layer base/components` | site |
| **108–1755** | `NEVA — 20 TENANT ŞABLONU` ... `NEVA — DİNAMİK MASA ROZETİ` | **menü** |
| 1756–2402 | `NEVA — PAZARLAMA / LANDING` | site |
| 2403–son | `Hukuki metinler` (bu oturumda eklendi) | site |

Doğrulanan gerçekler:

- 108–1755 aralığında **`@apply` yok** → Tailwind derleyicisi gerekmiyor.
- Şablon iskeletleri **Tailwind utility class'ı kullanmıyor**; yalnızca kendi
  önekli sınıfları (`mg-`, `ns-`, `rb-` ...) + paylaşılan `.tpl-*`.
- `.tpl*` sınıfları `templates/` ve `components/tpl/` dışında **hiçbir view'da geçmiyor**.
  Pazarlama sayfasındaki menü maketi ayrı sınıflar kullanıyor (`nv-menu*`).
- Menü CSS'inin tema token bağımlılığı sadece şunlar:
  `--color-ink-{100,300,400,500,600,700,800,900,950}`,
  `--color-gold-{300,400,500,700}`, `--font-display`, `--font-sans`.
- Satır ~2397'deki `@media print { .table-badge { display: none !important; } }`
  pazarlama bloğunda ama **menüye ait** — bölerken menu.css'e taşınmalı
  (zaten 1754'teki `.tpl-print .table-badge` ile mükerrer).
- Alpine (`x-data`) şablonlarda **yalnızca 3 tanesinde** kullanılıyor:
  `botanical-green`, `royal-blue`, `sunset-orange`. Diğer 37'sinde JS gereksiz.

## Yapılacak plan

1. `resources/css/menu.css` oluştur = 108–1755 + kaçak `@media print` kuralı.
2. `resources/css/palette.css` — düz `:root { }` bloğu, yukarıdaki token'lar.
   menu.css bunu `@import` eder (Tailwind `@theme`'i yok, kendi kopyası lazım).
   **Drift riski:** app.css'teki `@theme` ile aynı değerleri tutmalı →
   iki dosyadaki ortak token'ları karşılaştıran bir test yazılması önerilir.
3. app.css'ten 108–1755 çıkar.
4. `vite.config.js`'e `resources/css/menu.css` girdisini ekle.
5. `shell.blade.php`: `@vite(['resources/css/menu.css'])`.
   **PDF yolu da güncellenmeli** — aynı dosyada `build/manifest.json`'dan
   `resources/css/app.css` okuyup inline gömen `$__css` bloğu var, o da
   menu.css'e bakmalı (PDF çıktısı da küçülür).
6. Alpine'ı menü sayfasından düşür; 3 şablon için ya küçük bir vanilla JS yaz
   ya da yalnız o şablonlarda koşullu yükle.
7. **Sonra ölç.** menu.css tek başına küçükse (~8 KB gzip) şablon başına ayrı
   ayrı bölmeye gerek kalmayabilir — CSS render-blocking ama küçük.
   Şablon başına bölme, önek → şablon anahtarı eşlemesi gerektirir ve
   40 şablonda regresyon riski yüksek; ancak ölçüm gerektirdiğini gösterirse yapılmalı.

## Dikkat

Panel canlı önizlemesi de (`panel/design.blade.php` iframe) `templates.show`
render ediyor → shell'i o da kullanıyor. CSS değişirse önizleme de etkilenir,
40 şablon `TemplateRenderTest` ile korunuyor (`php artisan test`, PDF grubu
~185 sn sürüyor).

İlgili: [[menu-onbellek-mimarisi]], [[kalan-isler]]
