---
name: proje-hedefi
description: Neva-QR ürün mantığı — restoranlara 40 farklı tasarımda, kendi subdomaininde QR menü
metadata:
  type: project
---

Restoranlara birbirinden farklı, özel tasarlanmış (30-40 adet) QR menü şablonu satıyoruz.
Her işletme kendi alt domaininde yayınlanır: `galya.nevaqr.com`.

Domain: **nevaqr.com** (alternatif: nevaqrmenu.com). Kod içinde `config('neva.root_domain')`
üzerinden okunur, sabit yazılmaz. Yerelde `NEVA_ROOT_DOMAIN=localhost`.

**Why:** Ürünün tamamı QR menü üzerine kurulu; alt domain sadece bir özellik değil,
satılan şeyin kendisi. Bu yüzden yayına alma akışı (talep → onay → otomatik canlıya alma)
ürünün kalbi sayılır.

**How to apply:** Yeni özellik eklerken "bu 40 şablonun hepsinde çalışır mı" ve "kiracı
izolasyonunu bozar mı" diye sor. Şablon iskeletleri `resources/views/templates/skeletons/`
altında; ortak kabuk `components/templates/shell.blade.php`.

İlgili: [[alt-domain-yayin-akisi]], [[paket-yetki-matrisi]], [[menu-onbellek-mimarisi]]
