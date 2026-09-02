---
name: alt-domain-tls-gereksinimi
description: "Onayla → yayında" akışının elle iş istememesi için gereken üç tek-seferlik katman; en sık atlanan wildcard TLS sertifikası
metadata:
  type: project
---

Kullanıcının isteği (2026-09-02): **"onayla ve yayına al dediğim anda o subdomain
yayına girmeli"** — admin onayı KALSIN, ama onaydan sonra DNS paneline elle kayıt
girilmesin.

Kodda DNS otomasyonu zaten vardı (`DnsProvisioner` + `PublishSubdomain`).
Eksik olan **TLS sertifikasıydı** ve hiçbir yerde yazmıyordu.

## Zero-touch için gereken üç katman (üçü de kiracı başına DEĞİL, bir kez)

1. **Wildcard DNS** — `*.{root}` için tek A kaydı. `NEVA_DNS_DRIVER=wildcard`.
   (Kiracı başına kayıt gerekiyorsa `cloudflare` sürücüsü API ile kendisi açar.
   `manual` sürücüsü KULLANILMAMALI — o her kiracıda elle iş demek.)
2. **Wildcard TLS sertifikası** — `*.{root}` kapsayan sertifika.
   Let's Encrypt bunu **yalnızca DNS-01** ile verir; HTTP-01 wildcard vermez.
   `certbot certonly --dns-<saglayici> -d '{root}' -d '*.{root}'`
   Cloudflare proxy (turuncu bulut) açıksa Universal SSL bunu zaten karşılar.
3. **Wildcard vhost** — nginx `server_name {root} *.{root}` tek blok.

Ayrıntılı komutlar ve nginx örneği: `docs/URETIM.md` § 1.

## Neden bu kadar önemliydi

DNS varken sertifika yoksa adres çözülür ama HTTPS açılmaz. Eski
`SubdomainHealthChecker` bu durumda sadece **"Adrese ulaşılamadı"** diyordu —
DNS mi, sertifika mı, sunucu mu belli değildi. Üstelik hata ancak **ilk müşteri
onaylandığında**, yani müşteri beklerken ortaya çıkıyordu.

## Yapılan iki düzeltme

- `SubdomainHealthChecker` artık arızayı **sınıflandırıyor**: `dns` / `tls` /
  `timeout` / `refused` / `http` / `wrong_tenant`. Dönen dizide `reason_code`
  var ve `reason` alanına **ne yapılacağı** yazılıyor (`REMEDIES` sabiti).
  Bu metin `publish_error` kolonuna gidiyor, yani admin panelindeki kırmızı
  kutuda doğrudan çözüm görünüyor. `probe($url, $needle)` metodu Restaurant
  nesnesine bağlı değil — hiç kiracı yokken de çağrılabilir.
- **`php artisan neva:onkontrol`** (`app/Console/Commands/Preflight.php`):
  rastgele bir etiketle (`onkontrol-xxxx.{root}`) wildcard DNS çözülüyor mu,
  TLS uygulamaya ulaşıyor mu diye bakar; kuyruk `sync` mi, doğrulama açık mı
  kontrol eder. Ayrıca üretim ayarlarını (APP_DEBUG, SMTP, SESSION_DOMAIN,
  güvenli çerez, DB, derlenmiş varlıklar) denetler.
  **Hatalı kontrol varsa çıkış kodu 1** — dağıtım betiği buna bakıp durabilir.
  `--alt-domain` / `--uretim` ile daraltılır.

Ön kontrolde 404 **başarısızlık sayılmaz**: olmayan bir kiracı zaten bulunamaz;
önemli olan DNS + TLS + vhost katmanının aşılmış olmasıdır.

İlgili: [[alt-domain-yayin-akisi]], [[proje-hedefi]]
