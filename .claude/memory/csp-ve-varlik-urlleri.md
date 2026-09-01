---
name: csp-ve-varlik-urlleri
description: Yüklenen görsellerin URL'i host'suz olmalı; CSP img-src kiracı alt domaininde mutlak APP_URL'i engeller
metadata:
  type: project
---

`config/filesystems.php` › `public.url` **`/storage`** olarak ayarlıdır (mutlak `APP_URL.'/storage'` DEĞİL).

Sebep: kiracı menüsü `lumina.nevaqr.com` üzerinde açılır. `SecurityHeaders` middleware'inin
gönderdiği CSP'de `img-src 'self'` bu host'u kapsar, ama `APP_URL` (nevaqr.com) host'unu
kapsamaz — mutlak URL kullanılırsa tüm ürün görselleri ve logo tarayıcı tarafından engellenir.
Host'suz `/storage/...` her kiracı alt domaininde aynı origin'e düşer, sorun ortadan kalkar.

Ek güvence: `SecurityHeaders::appOrigin()` APP_URL origin'ini img/font/style/script
yönergelerine ekler; ana domainden gelen varlıklar da çalışsın diye.

**Why:** CSP eklendiğinde canlı menüdeki bütün görseller sessizce kayboldu; konsol dışında
belirti yoktu. Kök sebep mutlak varlık URL'leriydi.

**How to apply:** Yeni bir varlık türü eklerken (video, font, ikon) mutlak URL üretme;
`Storage::url()` veya köke göre yol kullan. CSP'ye yeni dış kaynak gerekiyorsa
`SecurityHeaders` içindeki listeye ekle, `'self'` yeterli sanma.

İlgili: [[kiraci-rota-parametresi]]
