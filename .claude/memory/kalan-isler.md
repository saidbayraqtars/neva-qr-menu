---
name: kalan-isler
description: 2026-09-01/02 oturumlarında yapılanlar ve sıradaki adımlar; kapsamlı SEO bilerek sona bırakıldı
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

## Aynı gün ayrıca yapılanlar

- Ortam kuruldu ve proje ilk kez çalıştırıldı ([[gelistirme-ortami]]): migration'lar geçti,
  48/48 test yeşil, Vite derlendi, sunucu ayağa kalktı.
- Üç kusur bulunup düzeltildi: kiracı rotalarında parametre kayması
  ([[kiraci-rota-parametresi]]), CSP'nin menü görsellerini engellemesi
  ([[csp-ve-varlik-urlleri]]) ve kayıtlı olmayan `/up` sağlık ucu.
- Şablon sayısı her yerde 40'a güncellendi (README, pazarlama metni, PlanSeeder, testler).

## 2026-09-02'de tamamlananlar

- **Dosya izolasyonu:** yüklemeler özel `uploads` diskine taşındı, `/gorsel/...`
  ucundan yetkiyle servis ediliyor; kalıcı silmede klasör de siliniyor
  ([[gorsel-izolasyonu]]). Taşıma komutu: `neva:uploads-tasi`.
- **Analitik:** `menu_visits` + istemci ölçüm ucu + panelde İstatistik ekranı
  ([[analitik-olcum]]). Ölü `scan_count` sayacı da bu akıştan besleniyor.
- **Alt domain iadesi:** restoran silinince etiket serbest kalıyor
  (`released_subdomain`), geri yüklemede boşsa iade ediliyor.
- **Sürükle-bırak sıralama:** kategoriler + ürünler (kategori içinde), kütüphanesiz;
  dokunmatik için ▲▼ düğmeleri. Sıralama `menu_version`'ı artırır.
- Testler: AnalyticsTest, MediaAccessTest, OrderingTest, SubdomainReleaseTest
  (toplam 71 test yeşil, PDF grubu dahil).

## Sıradaki adımlar (öncelik sırasıyla)

1. **Panel akışlarının elle gözden geçirilmesi** — tasarım stüdyosu ve mesajlaşma
   arayüzü hâlâ gözle kontrol edilmedi. (Ürünler, İstatistik ve canlı menü
   2026-09-02'de tarayıcıda doğrulandı.)
2. **Üretim ayarları** — `QUEUE_CONNECTION=database` + `queue:work` servisi, gerçek SMTP,
   cron satırı (`schedule:run`), `APP_DEBUG=false`, PostgreSQL.
3. **KAPSAMLI SEO** (kullanıcı bilerek sona bıraktı): blog/içerik modülü, şehir + mutfak
   bazlı landing sayfaları ("Ankara QR menü", "kafe QR menü"), FAQ schema, per-şablon CSS
   bölme (şu an 40 şablonun CSS'i tek bundle'da → LCP kötü), görsel alt/lazy/width-height,
   iç link mimarisi, Search Console + sitemap gönderimi.
4. KVKK: aydınlatma metni, çerez izni, veri saklama politikası.
5. Online ödeme (iyzico) — hacim büyüyünce ([[havale-odeme-akisi]]).
6. Görsel servisi ölçeklenirse: `/gorsel/...` için CDN/edge önbelleği ya da
   yayındaki dosyalar için imzalı URL.
