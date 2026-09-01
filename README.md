# Neva-QR Menü

Lüks, çok kiracılı (multi-tenant) SaaS QR menü platformu.
**Laravel 12 · Tailwind CSS · Alpine.js · Wildcard alt domain**

Her işletme kendi alt domaininde yayınlanır: `lumina.neva-qr.com`

---

## Gereksinimler

| Araç | Sürüm | Not |
|------|-------|-----|
| PHP | 8.2+ | `php-sqlite3`, `php-mbstring`, `php-gd`, `php-zip` eklentileri açık olmalı |
| Composer | 2.x | |
| Node.js | 20+ | Vite + Tailwind derlemesi için (bu makinede v24 kurulu) |
| SQLite | — | MVP varsayılanı. Üretimde PostgreSQL önerilir |

> ℹ️ **Bu makineye taşınabilir PHP 8.3 + Composer kuruldu:** `C:\Users\mehme\tools\`
> (`tools\php\php.exe`, `tools\composer.phar`). `tools\php` ve `tools\bin` **User PATH**'e eklendi —
> yeni açılan terminallerde `php` ve `composer` doğrudan çalışır.
>
> **PowerShell'de `npm` script hatası** (`npm.ps1 cannot be loaded ... running scripts is disabled`):
> ```powershell
> Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser   # kalıcı çözüm
> # veya sadece o oturum için:  Set-ExecutionPolicy RemoteSigned -Scope Process
> # veya komutu şöyle çağır:    npm.cmd install
> ```
>
> Wildcard alt domain (`lumina.neva-qr.test`) testi için [Laravel Herd](https://herd.laravel.com/windows)
> önerilir; `php artisan serve` ile ana panel/site `http://127.0.0.1:8000` üzerinde çalışır.

### “419 Page Expired” alıyorsanız

Bu bir oturum/CSRF cookie sorunudur. Sırayla:

```powershell
php artisan optimize:clear      # önbelleğe alınmış eski config'i temizler
```

1. Siteye **`http://127.0.0.1:8000`** ile girin — `localhost:8000` ile **değil** (farklı host, cookie taşınmaz).
2. Tarayıcıda bu site için çerezleri temizleyin / gizli sekmede deneyin (eski bozuk oturum kalmış olabilir).
3. `.env` içinde `SESSION_DRIVER=file`, `CACHE_STORE=file`, `SESSION_DOMAIN` satırı **yok** olmalı (proje OneDrive klasöründe olduğu için SQLite kilidi riskine karşı).
4. Birden fazla sekmede açık eski panel sayfalarını kapatın.

---

## Kurulum

```bash
# 1. Bağımlılıklar
composer install
npm install

# 2. Ortam
cp .env.example .env
php artisan key:generate

# 3. Veritabanı (SQLite)
#    Windows PowerShell: New-Item database/database.sqlite -ItemType File
touch database/database.sqlite
php artisan migrate --seed

# 4. Depolama sembolik bağlantısı (logo/görsel yüklemeleri)
php artisan storage:link

# 5. Frontend derleme (Vite + Tailwind v4 + Alpine)
npm run dev        # geliştirme
# npm run build    # üretim

# 6. Çalıştır
php artisan serve   # veya Herd ile http://neva-qr.test
```

> Kimlik doğrulama (kayıt/giriş/şifre sıfırlama) elle kuruldu — Breeze kurulumu **gerekmez**.
> QR üretimi `endroid/qr-code` (GD, PNG), menü PDF'i `barryvdh/laravel-dompdf` ile yapılır;
> PHP'de `ext-gd` açık olmalıdır.

### Wildcard alt domain (yerel)

- **Herd/Valet:** proje klasörünü `park` edin → `neva-qr.test` ve `*.neva-qr.test` otomatik çalışır.
- `.env` içinde `APP_URL=http://neva-qr.test`, `NEVA_ROOT_DOMAIN=neva-qr.test`, `SESSION_DOMAIN=.neva-qr.test`.
- Manuel: `C:\Windows\System32\drivers\etc\hosts` dosyasına `127.0.0.1 lumina.neva-qr.test` ekleyin.

### Demo hesaplar (seed sonrası)

| Rol | E-posta | Şifre |
|-----|---------|-------|
| Platform admini | `admin@neva-qr.com` | `password` |
| Restoran sahibi (kahve) | `sahip@neva-qr.com` | `password` |
| Restoran sahibi (kebapçı) | `ocakbasi@neva-qr.com` | `password` |

Demo menüler: `http://lumina.neva-qr.test` (Lumina Bistro & Lounge) · `http://ocakbasi.neva-qr.test`
(kebapçı demosu — 5 kategori × 10 ürün, sunset-orange şablonu)

---

## Dizin yapısı

```
site/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Panel/      # Sahip paneli (şablon, marka, kategori, ürün, QR, PDF)
│   │   │   ├── Admin/      # Alt domain onay kuyruğu
│   │   │   └── Tenant/     # Canlı menü render (alt domain)
│   │   └── Middleware/
│   │       ├── ResolveTenant.php      # {tenant}.neva-qr.com -> Restaurant
│   │       └── EnsureUserIsAdmin.php
│   ├── Models/             # Restaurant, Category, Product, Plan, Subscription,
│   │                       # SubdomainRequest, RestaurantTable, User
│   └── Services/           # QrService, MenuPdfService, SubdomainService (Faz-1 devam)
├── bootstrap/app.php       # web + wildcard tenant rota kaydı, middleware alias
├── config/neva.php         # marka, kök alan adı, rezerve subdomainler, şablonlar
├── database/
│   ├── migrations/         # ▼ aşağıya bakın
│   └── seeders/            # PlanSeeder, DemoSeeder
├── resources/views/
│   ├── marketing/          # pazarlama sitesi
│   ├── panel/  admin/      # panel arayüzleri
│   └── templates/
│       ├── cafe/           # Şablon 1 — kompakt dikey liste
│       └── restaurant/     # Şablon 2 — görsel odaklı, yatay sekmeler
└── routes/
    ├── web.php             # ana domain (pazarlama + panel + admin)
    └── tenant.php          # alt domain (canlı menü)
```

### Migrationlar (`database/migrations/`)

| Dosya | Tablo | Amaç |
|-------|-------|------|
| `0001_01_01_000000_create_users_table` | `users`, `sessions`, `password_reset_tokens` | Kimlik + `role` (owner/admin) |
| `0001_01_01_000001_create_cache_table` | `cache`, `cache_locks` | Önbellek |
| `0001_01_01_000002_create_jobs_table` | `jobs`, `job_batches`, `failed_jobs` | Kuyruk |
| `2026_08_30_000100_create_restaurants_table` | `restaurants` | **Kiracı.** `subdomain`, `template`, `status`, marka renkleri, onay izleri |
| `2026_08_30_000200_create_categories_table` | `categories` | Menü kategorileri, sıralama |
| `2026_08_30_000300_create_products_table` | `products` | Ürünler, fiyat/indirim, etiket & alerjen (JSON) |
| `2026_08_30_000400_create_subscriptions_table` | `plans`, `subscriptions` | Abonelik (Cashier'a geçişe hazır: `provider` + `external_id`) |
| `2026_08_30_000500_create_subdomain_requests_table` | `subdomain_requests` | Admin onay kuyruğu |
| `2026_08_30_000600_create_restaurant_tables_table` | `restaurant_tables` | Masalar + tekil `qr_token` |

---

## Kullanıcı akışı (MVP Faz-1)

1. Kullanıcı kayıt olur → abonelik (trial/manuel) → panele girer.
2. Panelde alt domain adı yazar → `subdomain_requests` tablosuna `pending` kayıt.
3. Şablon seçer (canlı önizleme), logo + renk + ürünleri girer.
4. Masalar için QR PNG ve tüm menüyü PDF olarak indirir.
5. **"Onaya Gönder"** → `restaurants.status = pending`, admin paneline düşer.
6. Admin onaylar → `subdomain` yazılır, `status = approved`, `published_at = now()` → alt domain **anında canlı**.

---

## Yol haritası

- [x] **Adım 1 — Altyapı:** migrationlar, modeller, wildcard rota + tenant middleware, config, seed.
- [x] **Adım 2 — Auth + Panel:** elle kurulan kimlik doğrulama, sahip paneli CRUD (kategori/ürün),
      tek ekran tasarım formu (şablon + marka + renk + logo) ve telefon maketinde **canlı önizleme**.
- [x] **Adım 3 — QR + PDF:** `endroid/qr-code` ile masa başına QR PNG indirme, `laravel-dompdf` ile lüks menü PDF.
- [x] **Adım 4 — Onay akışı:** "Onaya Gönder" → `subdomain_requests` kuyruğu → admin onay/red ekranı → anında yayın.
- [x] **Adım 5 — Şablonlar:** **40 mimari olarak ayrı şablon** — her birinin kendi Blade iskeleti
      (`resources/views/templates/skeletons/*.blade.php`) ve kendi CSS bloğu var (sadece renk farkı değil).
      1–20: Minimalist Kaffe, Dark Prestige, Neon Street, Botanical Green, Classic Bistro, Modern Grid,
      Sunset Orange, Royal Blue, Compact Fast, Artisan Crafted, Glassmorphism Luxury, Neo Brutalism,
      Minimal Mono, Cyber Dark, Golden Hour, Retro Diner, Alpine Clean, Sunset Vibes, Urban Chic, Velvet Noir.
      21–30 (görsel odaklı): Culinary Bento, Cinematic Dark, Magazine Grid, Stories Style, Polaroid Vibe,
      Floating Image, Split Card, Glass Hero, Grid Showcase, Gourmet Masonry.
      31–40: Heritage Press, Coastal Breeze, Carbon Mono, Onyx Lux, Saffron Table, Atelier Soft,
      Riso Pop, Linen Note, Prime Steakhouse, Kyoto Calm. Hepsi responsive.
      Galeri sınıflandırması (`template_focus`): 23 görsel odaklı + 17 tipografik = 40.
      Bir şablon sınıflandırmada eksik veya çift kayıtlıysa `DesignController` 500 fırlatır.
- [x] **Şablon özellik bayrakları** (`config/neva.php`): `family` (list/rich/grid/classic/compact),
      `cover`, `animation` (none/scale/glow/fade), `layout_type` (list/grid/masonry), `mood`,
      `hide_desc` (minimalist/mono şablonlarda ürün açıklamasını gizler), `locks` (bu şablonun iskeletinde
      karşılığı olmayan "Gelişmiş Dokunuşlar" kontrolleri — panelde tamamen gizlenir).
      `TemplatePresenter` bunları `data-*` olarak yayar; `supports($control)` ile panel dinamik çalışır.
- [x] **Akıllı görsel fallback:** `TemplatePresenter::productImage()` → ürün fotoğrafı ?? işletme logosu ??
      null (kart saf tipografik moda geçer). 40 şablonun tamamında güvenli.
- [x] **Tasarım stüdyosu:** **tekli odaklı galeri** (ortada tek şablon kartı, ◀▶ oklar, 40 nokta,
      canlı önizleme) → "Bu Şablonu Seç ve Düzenle" / "Bu Şablonu Düzenle". Galeri mini önizlemeleri
      kullanıcının hiçbir rengini/dokunuşunu ALMAZ (`?mini=1` → yalnızca `template`); her şablon kendi
      orijinal paletiyle görünür. Düzenleme ekranı: iframe önizleme + 📱/💻 geçişi. **50 Google Font**
      — arama + kategori filtresi olan combobox.
- [x] **Gelişmiş Dokunuşlar (accordion) — ŞABLONA ÖZEL:** her ayar `restaurants.template_settings`
      (JSON, `{ "<şablon>": { … } }`) altında saklanır; başka şablona geçince o şablonun kendi ayarları
      gelir. Alanlar: **3 renk seçici** — arka plan (`--t-bg`), başlık (`--t-heading`), gövde metni
      (`--t-text`); **arka plan dokusu** (düz / noktalı / diagonal çizgi / noise / soft glow);
      **16 giriş animasyonu** (smooth fade, slide up, slide-in, scale pop, zoom in, staggered bounce,
      elastic drop, blur reveal, flip X, fold down, swing in, skew slide, rise, drop in, neon pulse, glow in);
      **görsel stili** (auto / küçük / hero / gizli); **köşe yuvarlaklığı**; **başlık kalınlığı**; **yazı
      boyutu**. Şablonun iskeletinde karşılığı olmayan kontrol (`config.locks`) panelde tamamen gizli
      (`TemplatePresenter::supports()`), backend'de yok sayılır. Hepsi önizlemeye postMessage ile anında yansır.
- [x] **Şablona özel kısıtlamalar:** `cover` (kapak görseli) desteği + `hide_desc` (açıklama gizle) +
      `locks` (gelişmiş dokunuş kilitleri) — hepsi `config/neva.php`'de, panelde dinamik.
- [x] **PDF şablona uyarlı + görselli:** `MenuPdfService` şablonun ailesine, dark/light temasına, token
      renklerine (bg/heading/text dahil) göre PDF üretir. **Ürün görselleri GD ile merkezden kırpılıp
      küçültülerek (JPEG) gömülür** — ürün fotoğrafı yoksa işletme logosu, o da yoksa kompakt tipografik
      mod. grid ailesi fotoğraflı kart, rich/list/classic/compact minik thumb. Türkçe + ₺ → DejaVu.
- [x] **Öne çıkan & indirimli ürünler her şablonda farklı:** ürün kartı kök elemanına `flag_attrs()`
      (`data-feat` / `data-disc`), içine `<x-tpl.flags>` rozeti (`%X İndirim` / `★ Öne Çıkan` — lüks
      şablonlarda "★ Özel Seçim"). Grup bazlı görsel dil: **lüks** (dark-prestige/velvet-noir/royal-blue/
      glassmorphism) altın hâle + zarif seçki rozeti; **grid** (modern-grid/botanical/cyber/neon-street/
      sunset-vibes) en üste sıralanır + köşe rozeti; **brutal** (neo-brutalism/retro-diner) kart zemini
      tamamen değişir; **minimalist** (minimalist-kaffe/minimal-mono/alpine-clean) rozet yok, tipografik
      kontrast; **hızlı filtre** (sunset-orange üstte "Öne Çıkanlar/Fırsatlar" sekmesi, royal-blue sol
      nav'da sabit bölümler).
- [ ] **Faz-2:** Stripe/iyzico ödeme (Cashier), tarama analitiği, çoklu dil, sürükle-bırak sıralama, e-posta bildirimleri.

### Ekranlar

| Alan | Yol |
|------|-----|
| Pazarlama | `/` · `/hakkimizda` · `/fiyatlandirma` · `/iletisim` |
| Kimlik | `/login` · `/register` · `/forgot-password` |
| Sahip paneli | `/panel` · `/panel/tasarim` · `/panel/kategoriler` · `/panel/urunler` · `/panel/qr` |
| Admin | `/admin` · `/admin/talepler` |
| Canlı menü | `http://{subdomain}.neva-qr.test` · masa QR: `/m/{token}` |
