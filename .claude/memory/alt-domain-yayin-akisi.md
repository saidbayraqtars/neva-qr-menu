---
name: alt-domain-yayin-akisi
description: Talep → anlık çakışma kontrolü → onaya gönder (form gizlenir) → admin onayı → DNS + otomatik doğrulama
metadata:
  type: project
---

Akış:
1. Kullanıcı panelde ad yazar → `GET panel/alt-domain/musaitlik` her tuşta (400ms debounce)
   çağrılır; sunucu normalize edilmiş adı ve insan diliyle mesajı döner
   (`ok` / `invalid` / `reserved` / `taken`).
2. "Kaydet" → `subdomain_requests` tablosuna `pending` kayıt.
3. "Onaya Gönder" → `restaurants.status = pending`. **Bu andan sonra alt domain giriş alanı
   panelden tamamen kaldırılır**, yerine "Talebiniz inceleniyor" durum kartı gelir
   (`resources/views/panel/publish-card.blade.php`).
4. Admin onaylar → `SubdomainService::approve()` alt domaini yazar, `PublishSubdomain` işini
   kuyruğa atar.
5. `PublishSubdomain` işi: DNS kaydı (wildcard ise no-op, cloudflare sürücüsü opsiyonel) →
   ana QR üretimi → **HTTP doğrulaması**: 200 dönüyor mu VE sayfada işletme adı geçiyor mu.
   Başarısızsa `publish_status = failed` + `publish_error`, 3 deneme (30/120/300 sn backoff).
6. Saatlik `neva:health-check` komutu yayındaki tüm adresleri tekrar doğrular.

Çakışma koruması iki katmanlı: `restaurants.subdomain` unique + `subdomain_requests`
üzerinde `status='pending'` için kısmi unique index (SQLite/PostgreSQL).

**Why:** "Onaylandı" demek yetmiyordu — adres gerçekten açılmış mı kimse bakmıyordu.
Sadece 200 kontrolü de yeterli değil: yanlış kiracıya düşen bir wildcard yanıtı da 200 döner,
o yüzden içerikte işletme adı aranıyor.

**How to apply:** Üretimde `QUEUE_CONNECTION=database` ve `php artisan queue:work` şart —
`sync` olursa doğrulama admin'in isteğini bekletir. Cloudflare'a geçilecekse
`NEVA_DNS_DRIVER=cloudflare` + token/zone id.

İlgili: [[proje-hedefi]], [[menu-onbellek-mimarisi]]
