---
name: gorsel-izolasyonu
description: Yüklemeler neden özel diskte ve /gorsel/... ucundan servis ediliyor
metadata:
  type: project
---

Yüklemeler (`logo`, `kapak`, `ürün görseli`, `QR`) **özel** `uploads` diskinde
(`storage/app/uploads`) tutulur; web sunucusu doğrudan servis etmez. Erişim tek
noktadan, `MediaController` (`/gorsel/{yol}`) üzerinden olur:

- Restoran **yayındaysa** (approved + subdomain, silinmemiş) görsel herkese açık,
  `Cache-Control: public, max-age=31536000, immutable` (dosya adları rastgele
  karma olduğu için içerik değişince ad da değişir).
- Değilse yalnızca **sahibi ve admin** görebilir, `private, no-store`.
- Yol beyaz listeyle doğrulanır (`restaurants/<id>/(brand|products|qr)/…`) —
  dizin çıkışı ve rastgele dosya okuma engellenir.

**Rota hem `routes/web.php` hem `routes/tenant.php` içinde kayıtlı.** Adresler
bilerek HOST'SUZ üretilir (`media_url()`); mutlak adres kullanılsaydı kiracı
sayfasında CSP `img-src` engelleyecekti ([[csp-ve-varlik-urlleri]]).

**PDF:** `MenuPdfService::inlineImages()` artık `/gorsel/<yol>` desenini yakalayıp
dosyayı diskten okuyup data-URI'ye gömüyor (headless tarayıcı sunucuya geri istek
atmaz, kimlik doğrulaması da gerekmez).

Eski kurulumlar için taşıma komutu: `php artisan neva:uploads-tasi`.
Taşımadan sonra önbellekteki menü HTML'inde eski `/storage` adresleri kalabilir →
`php artisan cache:clear`. `storage:link` artık gerekmiyor.

İlgili: [[kalan-isler]] · [[csp-ve-varlik-urlleri]]
