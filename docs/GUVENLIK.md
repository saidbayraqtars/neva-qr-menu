# Güvenlik Denetimi

Uçtan uca inceleme — 2026-09-05. Kapsam: kimlik doğrulama, yetkilendirme,
kiracı izolasyonu, dosya yüklemeleri, enjeksiyon yüzeyleri, oturum yönetimi,
hız sınırları ve HTTP başlıkları.

Sonuç: **kritik açık bulunamadı.** Beş düzeltme yapıldı, üç konu bilinçli
kabul edilen risk olarak kayda geçti.

---

## 1. Doğrulanan — sağlam bulunan alanlar

### Kiracı izolasyonu (IDOR)

Çok kiracılı bir sistemde en sık açık burasıdır: rota model bağlama ile gelen
bir kaydın sahibi kontrol edilmezse, başka bir işletmenin verisi id değiştirerek
okunur veya silinir. Bağlanan **her** model için sahiplik kontrolü var:

| Kayıt | Koruma |
|---|---|
| `Category` | `CategoryPolicy` — `restaurant.user_id` eşleşmesi |
| `Product` | `ProductPolicy` + `assertCategoryOwned()` (başka kategoriye taşıma engelli) |
| `RestaurantTable` | `abort_unless($table->restaurant_id === app('restaurant')->id, 403)` |
| `Conversation` | `abort_unless($conversation->user_id === $request->user()->id, 403)` |

Sürükle-bırak sıralama uçları da güvenli: gönderilen id listesi ham çalıştırılmaz,
`app('restaurant')->products()->where('category_id', ...)->whereKey($id)` ile
kiracıya **ve** kategoriye kapsanır.

### Kütle atama (mass assignment)

`Restaurant` modelinde yayın ve sahiplik kolonları (`user_id`, `subdomain`,
`status`, `publish_status`, `approved_at`, …) `$guarded` içinde. Bunlar yalnızca
servis katmanından `forceFill` ile yazılıyor. `Product` ve `Category`
güncellemeleri `$request->all()` değil, açıkça yazılmış alan listeleriyle yapılıyor.

### Dosya yüklemeleri

- Laravel 12'de `image` doğrulama kuralı **SVG kabul etmiyor** (`allow_svg`
  verilmedikçe). Doğrulandı: `validateImage()` → `jpg, jpeg, png, gif, bmp, webp`.
  SVG bir belgedir, içine `<script>` gömülebilir; kabul edilseydi saklı XSS olurdu.
- Dosya adları `store()` tarafından içerik türünden türetilen karma adlarla yazılıyor;
  kullanıcı uzantı seçemiyor.
- Servis noktası (`/gorsel/...`) katı bir yol deseninden geçiyor: dizin çıkışı
  (`../`) ve rastgele dosya okuma mümkün değil.
- Yayına girmemiş restoranın görseli yalnızca sahibine ve admin'e açılıyor;
  yanıt `private, no-store` ile işaretleniyor.

### Kimlik doğrulama

- **Şifre belirleme jetonu**: 48 karakter rastgele, veritabanında yalnızca
  SHA-256 karması, karşılaştırma `hash_equals` ile sabit zamanlı, 72 saat
  geçerli, tek kullanımlık.
- Düz metin geçici şifre hiçbir yerde saklanmıyor; admin kullanıcının şifresini
  göremiyor, yalnızca yeni bir belirleme bağlantısı gönderebiliyor.
- Giriş 5 denemede kilitleniyor, başarılı girişte oturum kimliği yenileniyor,
  çıkışta oturum geçersiz kılınıyor.
- Admin'in şifresi `users.reset-password` ucundan sıfırlanamıyor.

### Yetki yükseltme

Kayıt formunda rol veya tutar alanı yok; rol sunucuda `ROLE_OWNER` olarak
sabitleniyor, ödenecek tutar `MembershipRequest::computeAmount()` ile
sunucu tarafında hesaplanıyor. Var olan e-posta ile kayıt/hesap açma engelli —
bir admin hesabının `updateOrCreate` ile owner'a düşürülmesi mümkün değil.

### Enjeksiyon

- Ham SQL yalnızca üç yerde ve hepsi parametreli:
  `whereRaw('LOWER(label) = ?', [...])`, `selectRaw` ile sabit toplama ifadeleri.
- `DB::raw('visitors + '.$visitor)` — `$visitor` `int` olarak tip zorlanmış (0/1).
- Blade'de kaçışsız çıktı (`{!! !!}`) yalnızca kullanıcı girdisi taşımayan
  yardımcılarla kullanılıyor (`flag_attrs()` sabit `data-*` üretir; ana sayfadaki
  dekoratif QR deseni tohumlanmış bir döngüden gelir).

### Oturum çerezi kapsamı

`SESSION_DOMAIN` bilerek tanımsız. `.nevaqr.com` yapılsaydı çerez tüm kiracı
alt domainlerine gönderilirdi ve kullanıcı içeriği render eden bir kiracı
sayfasındaki XSS panel oturumunu çalabilirdi. `neva:onkontrol` bunu kontrol ediyor.

### HTTP başlıkları

`SecurityHeaders` middleware'i tüm web yanıtlarına: CSP, `X-Content-Type-Options`,
`X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`,
`Cross-Origin-Opener-Policy` ve HTTPS'te `Strict-Transport-Security` ekliyor.

### Hız sınırları

Kayıt, iletişim formu, alt domain kontrolü/talebi, önizleme, PDF, mesajlaşma,
ölçüm ucu ve tüm parola akışları ayrı ayrı sınırlı.

---

## 2. Bu denetimde yapılan düzeltmeler

### 2.1 PDF ucu ayrı ve sıkı sınırlandı — *erişilebilirlik*

Her PDF isteği headless Chrome süreci başlatıyor (~250-400 MB, 70 sn zaman aşımı).
Uç, diğer QR uçlarıyla aynı `throttle:render` (20/dk) sınırını paylaşıyordu.
2 GB RAM'li bir sunucuda tek kullanıcı bunu tetikleyerek makineyi OOM'a
sürükleyebilirdi.

Artık kendi sınırı var: **3/dakika ve 20/saat**. Menü PDF'i saatte birkaç kez
indirilen bir çıktı; bu sınır gerçek kullanımı etkilemiyor.

### 2.2 SVG servis edilemez — *derinlemesine savunma*

`MediaController` yol deseni `.svg` uzantısını kabul ediyordu. Yükleme
doğrulaması SVG'yi zaten reddettiği için sömürülebilir bir durum yoktu; desen
yine de daraltıldı. İleride bir yükleme yolu gevşerse dosya servis edilmeyecek.

### 2.3 Açık yönlendirme kapatıldı — *419 sayfası*

Oturum zaman aşımı sayfasındaki "geri dön" bağlantısı `url()->previous()`
kullanıyordu; bu değer son çare olarak saldırganın belirleyebildiği `Referer`
başlığından gelir. Artık yalnızca aynı origin'e ait adres kabul ediliyor,
değilse ana sayfaya düşüyor.

### 2.4 Dış menü adresi http/https ile sınırlandı

`external_menu_url` alanı `url` kuralıyla doğrulanıyordu. `javascript:` ve
`data:` şemaları bu kuralca zaten reddediliyor (test edildi), ancak `ftp://`
gibi şemalar geçiyordu. Adres panelde tıklanabilir bağlantı olarak basıldığı
için `url:http,https` yapıldı.

### 2.5 Parola akışlarına eksik hız sınırları eklendi

`reset-password` ve `sifre-olustur` POST uçlarında hız sınırı yoktu. Jeton
entropisi kaba kuvveti zaten imkânsız kılıyor; sınır ikinci savunma hattı ve
bu uçların sayım aracına dönüşmesini engelliyor. İkisi de `throttle:6,1`.

---

## 3. Kabul edilen riskler

### 3.1 CSP `unsafe-inline` + `unsafe-eval` içeriyor

Alpine.js ifade değerlendirmesi için `unsafe-eval`, şablon iskeletlerinin satır
içi `<style>` blokları için `unsafe-inline` gerekli. Bu, CSP'nin XSS'e karşı
koruma değerini büyük ölçüde düşürüyor.

**Neden kabul edilebilir:** kaçışsız çıktı yüzeyi denetlendi, kullanıcı girdisi
hiçbir yerde ham basılmıyor. CSP burada birincil değil, ikincil savunma.

**Kaldırmak için:** Alpine'ın CSP uyumlu derlemesine geçmek ve 40 şablonun satır
içi stillerini derlenmiş CSS'e taşımak gerekir — ayrı ve büyük bir iş.

### 3.2 PDF üretimi `--no-sandbox` ile çalışıyor

Headless Chrome, kullanıcı içeriği barındıran bir `file://` sayfasını sandbox
kapalı açıyor. İçerik Blade tarafından kaçırıldığı için HTML enjeksiyonu
mümkün değil; risk teorik.

**Azaltım:** süreç `www-data` olarak çalışıyor, 70 sn zaman aşımı ve yeni sıkı
hız sınırı var. Sertleştirmek isterseniz PDF üretimini ayrı ve daha kısıtlı
bir kullanıcıya taşıyın.

### 3.3 Ölçüm ucu şişirilebilir

`/olcum` kimlik doğrulaması olmayan bir GET ucu (sayfa önbellekten geldiği için
sayaç istemciden artırılıyor). Bir saldırgan bir kiracının görüntülenme sayısını
şişirebilir.

**Neden kabul edilebilir:** yalnızca analitik sayaç; kişisel veri yazılmıyor,
IP saklanmıyor, başka hiçbir davranışı etkilemiyor. Uç IP başına 60/dk sınırlı.

---

## 4. Yayına almadan önce

Kod tarafı hazır; kalan maddeler yapılandırma:

```bash
php artisan neva:onkontrol
```

Çıktıdaki her satır yeşil olana kadar yayına almayın. Özellikle:

- `APP_DEBUG=false` — açık kalırsa hata sayfaları `.env` değerlerini gösterir
- `SESSION_SECURE_COOKIE=true` — HTTPS'te çerez düz bağlantıya sızmasın
- `SESSION_DOMAIN` **boş** — nokta ile başlayan değer vermeyin (§1)
- Gerçek SMTP — `log` kalırsa kullanıcı hesabına hiç giremez
- `QUEUE_CONNECTION=database` + `nevaqr-queue` servisi çalışıyor

Sunucu tarafı sertleştirme `deploy/kurulum.sh` içinde: ufw (yalnız 22/80/443),
fail2ban, `expose_php=Off`, `public/` altında yalnız `index.php` çalıştırılabilir,
nokta ile başlayan yollar kapalı.
