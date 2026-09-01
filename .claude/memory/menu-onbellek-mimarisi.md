---
name: menu-onbellek-mimarisi
description: Canlı menü HTML'i menu_version ile anahtarlanır; değişiklik anında yansır, TTL 2 saat üst sınır
metadata:
  type: project
---

Canlı menü sayfası tamamen önbellekten servis edilir. Anahtar:
`tenant:{subdomain}:v{menu_version}:{index|cat-N}`.

`MenuVersionObserver` — Restaurant/Category/Product kaydedilince, silinince veya geri
alınınca `restaurants.menu_version` artar. Sürüm değişince anahtar değişir, sayfa
**anında** yeniden üretilir. TTL (varsayılan 7200 sn) yalnızca üst sınırdır.

`neva:warm-menus` komutu 2 saatte bir menüleri ısıtır (hiç ziyaretçi gelmese bile
kimse soğuk sayfa beklemesin).

Masa bilgisi (`?masa=1`, `#1`) **istemci tarafında** işlenir — aksi halde her masa
için ayrı önbellek üretilirdi. `shell.blade.php` içindeki JS bunu yapıyor.

`ResolveTenant` bilerek önbelleğe alınmaz: `menu_version`'ı taze okumak zorundayız,
yoksa değişiklikler gecikir.

**Why:** Müşteri şartı "en geç 2 saatte bir güncellensin, hosting dahil pakette anında
yansısın". Sürümleme ikisini birden karşılıyor — ayrı bir cron'la cache temizlemeye gerek yok.

**How to apply:** Menüyü etkileyen yeni bir model eklersen (ör. kampanya, çalışma saati)
observer'a onu da bağla; yoksa değişiklik canlıya yansımaz.

İlgili: [[alt-domain-yayin-akisi]]
