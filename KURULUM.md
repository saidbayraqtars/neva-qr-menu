# Neva-QR Menü — Kurulum

Lüks, çok kiracılı (multi-tenant) SaaS QR menü platformu.
**Laravel 12 · Tailwind CSS v4 · Alpine.js · SQLite**

Bu arşiv `vendor/`, `node_modules/` ve derlenmiş varlıkları **içermez** — aşağıdaki adımlarla kurulur.

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

# 3. Depolama sembolik bağlantısı (logo/görsel yüklemeleri)
php artisan storage:link

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
