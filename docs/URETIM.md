# Üretime Alma

Bu belge sunucuyu **bir kez** kurmak içindir. Kurulum bittiğinde admin panelinde
"Onayla" dediğin an alt domain kendiliğinden açılır — DNS paneline elle kayıt girmezsin.

Yerel geliştirme kurulumu için [KURULUM.md](../KURULUM.md).
SEO tarafı için [SEO.md](SEO.md).

---

## 0.0 Sunucu seçimi

Uygulamanın sunucudan istedikleri, seçenekleri baştan eliyor:

| İhtiyaç | Neden zorunlu |
|---|---|
| Kalıcı dosya sistemi | Logo/kapak/ürün görselleri `storage/app/uploads` altında durur |
| Sürekli çalışan işlem | `queue:work` — yayına alma, DNS, doğrulama ve e-postalar kuyrukta |
| Dakikalık cron | `schedule:run` — önbellek ısıtma, sağlık kontrolü, KVKK temizliği |
| Wildcard alt domain + TLS | `*.nevaqr.com` tek vhost, tek sertifika |
| PHP 8.2+ · ext-gd | QR üretimi ve PDF |

**Serverless platformlar (Vercel, Netlify) bu listenin ilk üçünü karşılamaz.**
Dosya sistemi her istekte sıfırlanır, arka planda sürekli çalışan bir işlem
kurulamaz. Oraya taşımak; nesne depolama, harici kuyruk, harici oturum/önbellek
ve yönetilen veritabanı gerektirir — yani mimariyi baştan yazmak.

### Önerilen: küçük VPS + Cloudflare (ücretsiz)

| Kalem | Tutar |
|---|---|
| Hetzner CX22 (2 vCPU · 4 GB · 40 GB NVMe) | ~€4,5 / ay |
| Cloudflare Free (DNS + wildcard SSL + edge cache) | 0 |
| **Yıllık toplam** | **~€55** |

Bu makine 1-2 müşteri için fazlasıyla yeterli; menü HTML'i önbellekten
servis edildiği için darboğaz CPU değil, ağdır.

**Cloudflare neden ücretsiz katmanda bile kritik:** proxy açıkken (turuncu bulut)
Universal SSL `*.nevaqr.com`'u kapsar — certbot ile wildcard sertifika uğraşı
tamamen kalkar (bkz. §1.2). Sunucuda Cloudflare Origin CA sertifikası kullanılır.

### Daha ucuz: paylaşımlı hosting

PHP 8.2+, SSH/cron ve wildcard alt domain veren bir paylaşımlı pakette
(yılda ~600-900 ₺) uygulama çalışır, ama:

- `queue:work` servis olarak kurulamaz. Yerine dakikalık cron:
  `* * * * * cd /path && php artisan queue:work --stop-when-empty --max-time=55`
- PostgreSQL genelde yoktur; SQLite ile kalınır (1-2 müşteri için sorun değil).
- Wildcard TLS'i host sağlayamaz — Cloudflare proxy **zorunlu** olur.
- OPcache/PHP-FPM ayarlarına erişemezsin.

Bütçe çok darsa başlangıç için kabul edilebilir; müşteri sayısı artınca VPS'e geçilir.

### Edge önbelleği — varsayılanı DEĞİŞTİRME

Cloudflare statik varlıkları (CSS/JS/görsel) kendiliğinden edge'de tutar; HTML'i
tutmaz. **Bu doğru varsayılandır, açmayın.**

Neden: menü adresi (`lumina.nevaqr.com/`) değişiklikte sabit kalır. Tazeleme
sunucu tarafında `menu_version` ile yapılır. HTML'i edge'de önbelleğe alırsanız
panelde fiyat güncelleyen müşteri değişikliği **saatlerce göremez** — ürünün
"anında yansır" sözü bozulur.

Kiracı sayısı büyüyüp origin gerçekten zorlanırsa iki seçenek var:

1. Cache Rule + **kısa** Edge TTL (60 sn) — gecikme kabul edilebilir seviyede kalır
2. Cache Rule + yayın/düzenleme sonrası Cloudflare **cache purge** çağrısı

Her iki durumda da kural yalnızca `*.nevaqr.com` alt domainlerini kapsamalı;
ana domain (panel/admin, oturum çerezi taşır) **kesinlikle dahil edilmemeli**.

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

Sunucu bloğu repoda hazır: [`deploy/nginx/nevaqr.conf`](../deploy/nginx/nevaqr.conf).
Elle yazmayın — `deploy/kurulum.sh` onu yerine koyar.

Bilmeniz gereken üç şey:

**Tek blok, joker `server_name`.** `nevaqr.com *.nevaqr.com` aynı bloğa düşer;
hangi kiracı olduğunu Laravel çözer. Kiracı başına dosya yoktur.

**gzip AÇIK olmalı.** 140 kalemlik bir menünün HTML'i ~125 KB; sıkıştırmayla
~18 KB'a iniyor. Mobil ilk açılış farkı doğrudan buradan gelir. Conf dosyasında
açık geliyor, kapatmayın.

**`public/` altında yalnız `index.php` çalışır.** Diğer tüm `.php` istekleri
404 döner; nokta ile başlayan yollar (`.env`, `.git`) tamamen kapalıdır.

Doğrulama:

```bash
sudo nginx -t && sudo systemctl reload nginx
php artisan neva:onkontrol --alt-domain
```

`Wildcard TLS sertifikası ✓` gördüğünüzde alt domain tarafı bitmiştir.

---

## 2. Kuyruk işçisi

Yayına alma (DNS + QR + doğrulama) ve tüm e-postalar kuyrukta çalışır.
`QUEUE_CONNECTION=sync` kalırsa admin "Onayla"ya bastığında tarayıcı doğrulama
bitene kadar bekler ve zaman aşımına düşer.

```dotenv
QUEUE_CONNECTION=database
```

Servis tanımını `deploy/kurulum.sh` kurar (`nevaqr-queue.service`). Elle
kuracaksanız kritik iki ayrıntı:

- `MemoryMax=384M` — 2 GB'lık makinede kaçak bir iş sunucuyu yere sermesin
- `Restart=always` — işçi çöktüğünde yayına alma kuyruğu sessizce durmasın

```bash
sudo systemctl enable --now nevaqr-queue
sudo systemctl status nevaqr-queue
journalctl -u nevaqr-queue -f          # canlı log
```

> Her dağıtımdan sonra `php artisan queue:restart` gerekir — işçi eski kodu
> bellekte tutar. `deploy/guncelle.sh` bunu zaten yapıyor.

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
| `neva:yedek` | Günlük 03:00 | Veritabanı + görseller + `.env` arşivi (14 gün saklar) |
| `neva:veri-temizle` | Günlük 03:20 | Saklama süresi dolan kayıtları siler (KVKK) |
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

## 6. Dağıtım

```bash
sudo bash /var/www/nevaqr/deploy/guncelle.sh
```

Betik sırayla: bakım moduna alır → kodu çeker → `composer install --no-dev` →
`npm ci && npm run build` → `migrate --force` → önbellekleri tazeler →
**php-fpm reload** → kuyruk işçisini yeniden başlatır → bakım modundan çıkar →
`neva:onkontrol` ile doğrular.

İki nokta atlanırsa üretimde sessiz hataya dönüşür, betik ikisini de yapıyor:

- **`php-fpm reload` şart.** `opcache.validate_timestamps=0` ayarlıyoruz (her
  istekte dosya tarihi kontrolü yapılmasın diye). Reload edilmezse sunucu eski
  kodu çalıştırmaya devam eder.
- **`optimize:clear` `optimize`'dan önce.** Derlenmiş eski config, yeni `.env`
  değerlerini gölgeler.

İlk kurulumda `--ilk` verin (APP_KEY üretir, SQLite dosyasını açar, paketleri seeder):

```bash
sudo bash /var/www/nevaqr/deploy/guncelle.sh --ilk
```

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

```bash
php artisan neva:yedek
```

Tek bir zip üretir: veritabanı + yüklenen görseller + `.env`. Günlük olarak
03:00'te zamanlanmış çalışır (`routes/console.php`) ve 14 günden eski arşivleri
kendisi siler.

| Seçenek | Ne yapar |
|---|---|
| `--dizin=/mnt/yedek` | Başka bir dizine yaz (harici disk / mount) |
| `--tut=30` | 30 gün sakla (`0` = hiç silme) |
| `--gorsel-yok` | Yalnız veritabanı + `.env` (hızlı, küçük) |

Neden bu üçü: veritabanı kiracıları ve menüleri, `storage/app/uploads`
işletme logolarını ve ürün görsellerini tutuyor. `.env` içindeki **APP_KEY**
kaybolursa şifrelenmiş oturum çerezleri ve imzalı bağlantılar geçersiz olur.
`storage/framework/cache` bilerek yedeklenmez — menü önbelleği `menu_version`
ile kendini yeniden üretir.

SQLite kullanıyorsanız dosya düz kopyalanmaz: WAL modunda çalışan bir veritabanını
yazma sırasında kopyalamak bozuk yedek üretir. Komut `VACUUM INTO` ile tutarlı
bir anlık görüntü alır.

> **Arşiv `.env` içerir — sunucuda bırakmayın.** Sunucu çökerse yedek de gider.
> Günlük olarak dışarı taşıyın; `rclone` ile bir nesne deposuna kopyalamak en
> ucuz yol:
>
> ```cron
> 30 3 * * * rclone copy /var/www/nevaqr/storage/app/backups uzak:nevaqr-yedek
> ```

---

## 8.5 İlk yönetici hesabı

Taze bir üretim veritabanında **hiç kullanıcı yoktur** ve kayıt akışı bir
üyelik talebi üretir — talebi onaylayacak bir yönetici gerekir. Kısır döngüyü
kıran komut:

```bash
php artisan neva:admin-olustur --email=siz@nevaqr.com --ad="Adınız"
```

Düz metin şifre üretilmez. Komut tek kullanımlık bir "şifre belirle" bağlantısı
oluşturur; SMTP kuruluysa e-posta ile gönderir, kurulu değilse **konsola basar**
(ilk kurulumda e-posta çalışmadan da hesaba girebilesiniz diye).

> `php artisan db:seed` ÇALIŞTIRMAYIN. `DemoSeeder` sabit `password` şifreli
> hesaplar ve sahte demo restoranlar oluşturur — üretim veritabanına girmemeli.
> Yalnızca `PlanSeeder` gerekli, onu da `deploy/guncelle.sh --ilk` çalıştırıyor.

---

## 9. İzleme

Projede hazır bir sağlık ucu var: `https://nevaqr.com/up`

Ücretsiz bir dış izleyici (ör. UptimeRobot) bu adresi 5 dakikada bir yoklasın —
sunucu düştüğünde haber alırsınız. Sunucuda kaynak tüketmez, kontrol paneli
kurmaya gerek bırakmaz.

Log yerleri:

```bash
tail -f /var/www/nevaqr/storage/logs/laravel.log   # uygulama
journalctl -u nevaqr-queue -f                      # kuyruk işçisi
tail -f /var/log/nginx/nevaqr-error.log            # nginx
```
