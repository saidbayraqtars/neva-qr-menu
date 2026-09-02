# Üretime Alma

Bu belge sunucuyu **bir kez** kurmak içindir. Kurulum bittiğinde admin panelinde
"Onayla" dediğin an alt domain kendiliğinden açılır — DNS paneline elle kayıt girmezsin.

Yerel geliştirme kurulumu için [KURULUM.md](../KURULUM.md).

---

## 0. Ön kontrol

Her adımdan sonra bunu çalıştır. Neyin eksik olduğunu ve ne yapılacağını satır satır söyler:

```bash
php artisan neva:onkontrol
```

Dağıtım betiğinde de kullanılabilir: hatalı kontrol varsa çıkış kodu `1` döner.

---

## 1. Alt domain otomasyonu (tek seferlik)

"Onayla → yayında" akışının elle iş istememesi için sunucuda üç katman hazır olmalı.
Üçü de **kiracı başına değil, bir kez** kurulur.

### 1.1 Wildcard DNS

Alan adının DNS bölgesine tek bir kayıt:

| Tip | Ad  | Değer                  |
|-----|-----|------------------------|
| A   | `*` | sunucunun IP adresi    |
| A   | `@` | sunucunun IP adresi    |

Bu kayıt durduğu sürece `galya.nevaqr.com`, `lumina.nevaqr.com` — hepsi kendiliğinden
çözülür. Yeni müşteri için DNS panelini açman gerekmez.

`.env`:

```dotenv
NEVA_DNS_DRIVER=wildcard
```

> **Wildcard kullanamıyorsan** (ör. her kiracı ayrı bir CDN kaydına düşecekse)
> `NEVA_DNS_DRIVER=cloudflare` yap ve `CLOUDFLARE_API_TOKEN` + `CLOUDFLARE_ZONE_ID`
> gir — sistem kiracı başına CNAME'i API ile kendisi açar. Bu da elle iş istemez.
>
> `NEVA_DNS_DRIVER=manual` **kullanma**: o sürücüde her kiracı için kaydı sen açarsın.

### 1.2 Wildcard TLS sertifikası

**En sık atlanan adım.** Wildcard DNS varken sertifika yoksa adres çözülür ama
HTTPS açılmaz; otomatik doğrulama `TLS sertifikası bu adresi kapsamıyor` diye düşer.

Tek alan adı sertifikası (`nevaqr.com`) **yetmez** — `*.nevaqr.com` gerekir ve
Let's Encrypt bunu yalnızca **DNS-01** doğrulamasıyla verir (HTTP-01 wildcard vermez).

Cloudflare DNS kullanıyorsan:

```bash
sudo certbot certonly \
  --dns-cloudflare \
  --dns-cloudflare-credentials /etc/letsencrypt/cloudflare.ini \
  -d 'nevaqr.com' -d '*.nevaqr.com'
```

Sertifika 90 günlük; certbot kendi zamanlayıcısıyla yeniler:

```bash
sudo systemctl enable --now certbot.timer
sudo certbot renew --dry-run     # yenileme çalışıyor mu, bir kez doğrula
```

> Alan adı Cloudflare üzerinden **proxy'li** (turuncu bulut) geçiyorsa Universal SSL
> `*.nevaqr.com`'u zaten kapsar; ayrıca certbot'a gerek kalmaz. Bu durumda sunucuda
> Cloudflare Origin CA sertifikası kullan.

### 1.3 Wildcard vhost

Nginx, `server_name` içinde joker ile tek bir sunucu bloğu — kiracı başına dosya yok:

```nginx
server {
    listen 443 ssl http2;
    server_name nevaqr.com *.nevaqr.com;

    ssl_certificate     /etc/letsencrypt/live/nevaqr.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/nevaqr.com/privkey.pem;

    root /var/www/nevaqr/public;
    index index.php;

    client_max_body_size 12M;   # ürün görseli 6 MB'a kadar

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}

# HTTP → HTTPS
server {
    listen 80;
    server_name nevaqr.com *.nevaqr.com;
    return 301 https://$host$request_uri;
}
```

Doğrula:

```bash
sudo nginx -t && sudo systemctl reload nginx
php artisan neva:onkontrol --alt-domain
```

`Wildcard TLS sertifikası ✓` gördüğünde alt domain tarafı bitmiştir.

---

## 2. Kuyruk işçisi

Yayına alma (DNS + QR + doğrulama) ve tüm e-postalar kuyrukta çalışır.
`QUEUE_CONNECTION=sync` kalırsa admin "Onayla"ya bastığında tarayıcı doğrulama
bitene kadar bekler ve zaman aşımına düşer.

```dotenv
QUEUE_CONNECTION=database
```

```bash
php artisan migrate      # jobs / failed_jobs tabloları
```

`/etc/systemd/system/nevaqr-queue.service`:

```ini
[Unit]
Description=Neva-QR kuyruk isçisi
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=5
WorkingDirectory=/var/www/nevaqr
ExecStart=/usr/bin/php artisan queue:work --tries=3 --max-time=3600 --sleep=3

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable --now nevaqr-queue
sudo systemctl status nevaqr-queue
```

> Her dağıtımdan sonra `php artisan queue:restart` çalıştır — işçi eski kodu
> bellekte tutar.

---

## 3. Zamanlanmış işler

Sunucuda **tek** cron satırı yeterli:

```cron
* * * * * cd /var/www/nevaqr && php artisan schedule:run >> /dev/null 2>&1
```

| Komut | Sıklık | Ne yapar |
|-------|--------|----------|
| `neva:warm-menus` | 2 saatte bir | Canlı menü önbelleğini tazeler |
| `neva:health-check` | Saatte bir | Yayındaki alt domainleri doğrular |
| `neva:veri-temizle` | Günlük | Saklama süresi dolan kayıtları siler (KVKK) |
| jeton temizliği | Günlük | Süresi geçmiş şifre belirleme jetonları |
| `queue:prune-failed` | Haftalık | Eski başarısız işler |

---

## 4. Veritabanı

MVP SQLite ile çalışır ama tek yazar kilidi vardır; eşzamanlı kullanıcıda darboğaz olur.
Üretimde PostgreSQL:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=neva_qr
DB_USERNAME=neva
DB_PASSWORD=...
```

```sql
CREATE DATABASE neva_qr;
CREATE USER neva WITH ENCRYPTED PASSWORD '...';
GRANT ALL PRIVILEGES ON DATABASE neva_qr TO neva;
```

```bash
php artisan migrate --force
php artisan db:seed --class=PlanSeeder --force   # paketler
```

> SQLite'tan taşıyorsan veriyi elle aktar — migration bunu yapmaz. Alt domain
> benzersizliği için kullanılan **kısmi unique index** hem SQLite hem PostgreSQL'de
> desteklenir, ek işlem gerekmez.

---

## 5. `.env` üretim bloğu

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://nevaqr.com
NEVA_ROOT_DOMAIN=nevaqr.com

# Oturum çerezi ALT DOMAINLERE YAYILMAMALI.
# Kiracı sayfaları kullanıcı içeriği render eder; çerez oraya giderse
# oradaki bir XSS panel oturumunu çalar. SESSION_DOMAIN'i BOŞ BIRAK.
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

QUEUE_CONNECTION=database
CACHE_STORE=file
DB_CONNECTION=pgsql

NEVA_DNS_DRIVER=wildcard
NEVA_PUBLISH_VERIFY=true

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="destek@nevaqr.com"

LOG_LEVEL=warning
```

> **SMTP zorunlu.** Düz metin şifre saklanmıyor; kullanıcı hesabına ancak
> e-postayla giden tek kullanımlık "şifre belirle" bağlantısıyla girebiliyor.
> `MAIL_MAILER=log` kalırsa bağlantı yalnızca log dosyasına yazılır.

---

## 6. Dağıtım adımları

```bash
cd /var/www/nevaqr
php artisan down

git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan optimize          # config + route + view önbelleği
php artisan queue:restart

php artisan up
php artisan neva:onkontrol    # son doğrulama
```

> `php artisan optimize` sonrası `.env` değişikliği **etkisizdir**; değiştirdiysen
> `php artisan optimize:clear && php artisan optimize` çalıştır.

---

## 7. Dosya izinleri

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

Yüklenen görseller `storage/app/uploads` altında **özel** diskte durur ve
`/gorsel/...` ucundan yetki kontrolüyle servis edilir — `public/` altına konmaz,
`storage:link` gerekmez.

---

## 8. Yedekleme

Yedeklenmesi gerekenler:

- Veritabanı (`pg_dump neva_qr`)
- `storage/app/uploads` — işletme logoları, kapaklar, ürün görselleri
- `.env`

`storage/framework/cache` yedeklenmez; menü önbelleği `menu_version` ile
kendini yeniden üretir.
