# Neva-QR Menü — Kurulum

Lüks, çok kiracılı (multi-tenant) SaaS QR menü platformu.
**Laravel 12 · Tailwind CSS v4 · Alpine.js · SQLite**

Bu arşiv `vendor/`, `node_modules/` ve derlenmiş varlıkları **içermez** — aşağıdaki adımlarla kurulur.

> **Üretime alacaksanız** bu belge yetmez: wildcard DNS, wildcard TLS sertifikası,
> nginx vhost, kuyruk işçisi ve cron kurulumu için **[docs/URETIM.md](docs/URETIM.md)**.
> Her adımdan sonra `php artisan neva:onkontrol` çalıştırıp neyin eksik olduğunu görün.

---

## Gereksinimler

| Araç | Sürüm |
|------|-------|
| PHP | 8.2+ (`gd`, `sqlite3`, `mbstring`, `zip`, `openssl`, `curl` eklentileri açık) |
| Composer | 2.x |
| Node.js | 20+ |
| Chrome **veya** Edge | Menü PDF çıktısı için (headless `--print-to-pdf`) |

---

## Kurulum

```bash
# 1. Bağımlılıklar
composer install
npm install

# 2. Ortam dosyası (.env arşivde varsa bu adımı atla)
cp .env.example .env
php artisan key:generate

# 3. (Artık gerekmiyor) storage:link — yüklemeler özel diskte tutulur ve
#    /gorsel/... ucundan yetki kontrolüyle servis edilir.

# 4. Veritabanı
#    database/database.sqlite arşivde varsa hazır demo veriyle gelir; yoksa:
#    Windows PowerShell:  New-Item database/database.sqlite -ItemType File
touch database/database.sqlite
php artisan migrate --seed

# 5. Frontend derleme
npm run build      # üretim  (veya geliştirme için: npm run dev)

# 6. Çalıştır
php artisan serve --host=127.0.0.1 --port=8000
```

> Siteye **her zaman `http://127.0.0.1:8000`** ile girin (`localhost` değil) — aksi halde
> oturum/CSRF karışır (419).
>
> Windows'ta PDF için Chrome/Edge otomatik bulunur; farklı konumdaysa `.env` içine
> `CHROME_BINARY="C:\yol\chrome.exe"` ekleyin.
>
> Aynı anda birçok önizleme yüklendiği için sunucuyu şöyle çalıştırmak daha akıcıdır:
> `PHP_CLI_SERVER_WORKERS=6 php artisan serve ...`

---

## Alt domain (kiracı menüsü) testi

Menüler `{isletme}.{NEVA_ROOT_DOMAIN}` adresinde yayınlanır. Yerelde `.env`:

```
NEVA_ROOT_DOMAIN=localhost
```

Böylece onaylı bir işletmenin menüsü `http://lumina.localhost:8000` adresinden açılır
(`*.localhost` çoğu tarayıcıda otomatik olarak 127.0.0.1'e çözülür).

---

## Demo girişleri (seed sonrası)

| Rol | E-posta | Şifre |
|-----|---------|-------|
| Platform admin | `admin@neva-qr.com` | `password` |
| İşletme sahibi (Lumina Bistro & Lounge) | `sahip@neva-qr.com` | `password` |
| İşletme sahibi (Anadolu Ocakbaşı) | `ocakbasi@neva-qr.com` | `password` |

---

## Bu sürümde eklenenler (2026-09-01) — yapılması gerekenler

Aşağıdaki adımlar **yeni kurulumda da, mevcut kurulumu güncellerken de** gereklidir.

```bash
# 1. Yeni migration'lar (şifre sertleştirme, mesajlaşma, havale, denetim kaydı, önbellek sürümü)
php artisan migrate

# 2. Ayar/rota önbelleğini temizle (yeni middleware ve rotalar var)
php artisan optimize:clear

# 3. Testler
php artisan test
```

### Görseller özel diske taşındı (2026-09-02)

Yüklenen logo/kapak/ürün görselleri artık `storage/app/public` altında **değil**,
`storage/app/uploads` altında duruyor ve `/gorsel/...` ucundan servis ediliyor.
Böylece yayına girmemiş (taslak) bir işletmenin görselleri herkese açık olmuyor.

```bash
# Mevcut kurulumu güncelliyorsanız dosyaları bir kez taşıyın:
php artisan neva:uploads-tasi --dry-run   # önce raporla
php artisan neva:uploads-tasi             # taşı

# Menü HTML'i önbelleğinde eski /storage adresleri kalmış olabilir:
php artisan cache:clear
```

`.env` içinde: `NEVA_UPLOAD_DISK=uploads`.

### .env'e eklenmesi gerekenler

`.env.example` içindeki yeni bloklar `.env` dosyanıza kopyalanmalı. En kritikleri:

```dotenv
NEVA_ROOT_DOMAIN=nevaqr.com          # üretim; yerelde localhost

# Havale bilgileri — kayıt ekranında ve e-postada müşteriye gösterilir
NEVA_BANK_ACCOUNT_NAME="..."
NEVA_BANK_NAME="..."
NEVA_BANK_IBAN="TR.. .... .... ...."

# Kuyruk: alt domain yayına alma ve e-postalar burada çalışır
QUEUE_CONNECTION=database            # üretimde 'sync' BIRAKMAYIN

# Gerçek SMTP — hesap açılış linki bu kanaldan gider, olmadan kullanıcı giriş yapamaz
MAIL_MAILER=smtp
```

> **ÖNEMLİ — düz metin şifre kaldırıldı.** Admin artık kullanıcı şifresini göremiyor.
> Hesap açıldığında kullanıcıya tek kullanımlık "şifre belirle" bağlantısı e-posta ile
> gider. Bu yüzden **çalışan bir SMTP zorunludur**; `MAIL_MAILER=log` iken bağlantı
> yalnızca `storage/logs/laravel.log` içine yazılır.

### Üretimde çalışması gereken servisler

```bash
# Kuyruk işçisi (alt domain yayına alma + otomatik doğrulama + e-postalar)
php artisan queue:work --tries=3

# Zamanlanmış işler için tek bir cron satırı yeterli:
# * * * * * cd /proje/yolu && php artisan schedule:run >> /dev/null 2>&1
```

Zamanlanmış işler:

| Komut | Sıklık | Ne yapar |
|-------|--------|----------|
| `neva:warm-menus` | 2 saatte bir | Canlı menü önbelleğini tazeler (soğuk sayfa olmasın) |
| `neva:health-check` | Saatte bir | Yayındaki alt domainlerin gerçekten açıldığını doğrular |
| jeton temizliği | Günlük | Süresi dolmuş şifre belirleme jetonlarını siler |

### Alt domain yayına alma

Varsayılan sürücü `wildcard` — DNS'te `*.nevaqr.com` kaydının tanımlı olması yeterlidir,
sistem ayrıca bir şey yapmaz. Kiracı başına DNS kaydı gerekiyorsa:

```dotenv
NEVA_DNS_DRIVER=cloudflare
CLOUDFLARE_API_TOKEN=...
CLOUDFLARE_ZONE_ID=...
NEVA_DNS_TARGET=nevaqr.com
```

Onaydan sonra sistem adresi otomatik test eder (200 dönüyor mu + sayfada işletme adı
geçiyor mu). Başarısız olursa admin panelinin ana ekranında kırmızı kutuda listelenir.

### Önbellek davranışı

Canlı menü HTML'i önbellekten servis edilir. Panelde bir değişiklik yapıldığında
(`ürün, fiyat, kategori, şablon, renk`) `restaurants.menu_version` artar ve sayfa
**anında** yeniden üretilir. `NEVA_MENU_CACHE_TTL` (varsayılan 7200 sn = 2 saat)
yalnızca üst sınırdır.

Sorun yaşarsanız: `php artisan cache:clear`.

---

## İstatistik (görüntülenme ölçümü)

Canlı menü HTML'i önbellekten servis edildiği için sayaç sunucu render'ında
artırılamaz. Sayfa yüklendikten sonra tarayıcı `/olcum` ucuna tek bir hafif
istek atar; `menu_visits` tablosunda **gün + masa etiketi** bazında toplanır.

- Kişisel veri saklanmaz: IP, konum, cihaz kimliği yok — yalnızca sayaç.
- "Farklı cihaz" sayısı tarayıcıdaki günlük işarete (localStorage) dayanır.
- Panel: **İstatistik** sekmesi (`/panel/istatistik`).
