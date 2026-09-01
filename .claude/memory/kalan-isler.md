---
name: kalan-isler
description: 2026-09-01 oturumunda yapılanlar ve sıradaki adımlar; kapsamlı SEO bilerek sona bırakıldı
metadata:
  type: project
---

## 2026-09-01'de tamamlananlar

- Git deposu kuruldu (baseline commit).
- **Güvenlik:** düz metin şifre saklama kaldırıldı ([[sifre-guvenlik-kurali]]);
  `SecurityHeaders` middleware (CSP/HSTS/nosniff/Referrer-Policy); hız sınırları
  (kayıt, iletişim, alt domain, QR/PDF, önizleme, mesaj); iletişim formuna bot tuzağı;
  `Restaurant` modelinde yayın kolonları kütle atamaya kapatıldı; `audit_logs` tablosu.
- **Paket yetki matrisi** sunucu tarafında zorlanıyor ([[paket-yetki-matrisi]]).
- **Alt domain:** anlık çakışma kontrolü, onaya gönderdikten sonra formun gizlenmesi,
  `PublishSubdomain` işi ile DNS + otomatik doğrulama, saatlik health-check
  ([[alt-domain-yayin-akisi]]).
- **Önbellek:** `menu_version` + 2 saat TTL + `neva:warm-menus` cron ([[menu-onbellek-mimarisi]]).
- **Mesajlaşma + iletişim formu** ([[mesajlasma-mimarisi]]).
- **Havale ödeme akışı** referans kodu ile ([[havale-odeme-akisi]]).
- **E-posta bildirimleri:** hesap hazır, havale talimatı, alt domain onay/red, yeni mesaj,
  iletişim formu.
- **SEO temeli:** `<x-seo>` bileşeni, sitemap.xml (ana + kiracı), kiracı robots.txt,
  Restaurant/Menu JSON-LD, canonical + Open Graph.
- Testler: PlanAccess, SubdomainFlow, Messaging, PasswordSetup, MenuCache.

## Sıradaki adımlar (öncelik sırasıyla)

1. **Çalıştırma ve doğrulama** — PHP'li terminalde `composer install`, `php artisan migrate`,
   `php artisan test`, `npm run build`. Bu makinede yapılamadı ([[gelistirme-ortami]]).
2. **Üretim ayarları** — `QUEUE_CONNECTION=database` + `queue:work` servisi, gerçek SMTP,
   cron satırı (`schedule:run`), `APP_DEBUG=false`, PostgreSQL.
3. **KAPSAMLI SEO** (kullanıcı bilerek sona bıraktı): blog/içerik modülü, şehir + mutfak
   bazlı landing sayfaları ("Ankara QR menü", "kafe QR menü"), FAQ schema, per-şablon CSS
   bölme (şu an 40 şablonun CSS'i tek bundle'da → LCP kötü), görsel alt/lazy/width-height,
   iç link mimarisi, Search Console + sitemap gönderimi.
4. **Dosya izolasyonu** — yüklemeler `public` diskte; taslak restoranın görselleri de
   herkese açık. Private disk + imzalı URL, restoran silinince klasör temizliği.
5. **Analitik** — `scan_count` yeni `?masa=` akışında artmıyor (ölü sayaç); tarama/görüntülenme
   istatistiği yok.
6. Silinen restoranın alt domaininin serbest bırakılması (unique index kalıyor).
7. Ürünlerde sürükle-bırak sıralama (kategorilerde var).
8. KVKK: aydınlatma metni, çerez izni, veri saklama politikası.
9. Online ödeme (iyzico) — hacim büyüyünce ([[havale-odeme-akisi]]).
