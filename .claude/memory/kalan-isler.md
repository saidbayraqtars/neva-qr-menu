---
name: kalan-isler
description: 2026-09-01/02 oturumlarında yapılanlar ve sıradaki adımlar; SEO'nun büyük kısmı hâlâ açık, CSS bölme yarım kaldı
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

## 2026-09-02 · ikinci oturum (limit nedeniyle yarıda kesildi)

Kullanıcı paneli elle gözden geçirdi, **hata yok** — o madde kapandı.
Ardından "kalanları hallet" dendi. Yapılanlar:

- **Alt domain zero-touch** ([[alt-domain-tls-gereksinimi]]): sağlık kontrolü artık
  arıza sebebini sınıflandırıyor (dns/tls/timeout/refused/wrong_tenant) ve çözümü
  `publish_error`'a yazıyor. Yeni `neva:onkontrol` komutu wildcard DNS + TLS + kuyruk +
  üretim ayarlarını denetliyor, hatada çıkış kodu 1.
- **KVKK** ([[kvkk-ve-saklama-sureleri]]): 4 hukuki sayfa, çerez bildirimi,
  footer'da "Yasal" sütunu, `neva:veri-temizle` komutu + günlük cron.
- **Dinamik robots.txt**: `SitemapController@robots`. Statik `public/robots.txt`
  SİLİNDİ — içinde `https://nevaqr.com` sabit yazılıydı, yerelde/staging'de yanlıştı.
  Sitemap'e hukuki sayfalar eklendi.
- **`docs/URETIM.md`**: tek seferlik sunucu kurulumu — wildcard DNS, wildcard TLS
  (certbot DNS-01), nginx wildcard vhost, systemd kuyruk işçisi, cron, PostgreSQL,
  dağıtım adımları, yedekleme.

Bu oturumda **test yazılmadı** — yeni komutlar ve hukuki rotalar test kapsamı dışında.
Mevcut 71 test, sağlık kontrolü değişikliğinden sonra yeşil doğrulandı.

## Sıradaki adımlar (öncelik sırasıyla)

1. **CSS/JS bölme** — keşif bitti, uygulama yarım kaldı. Tam plan, ölçümler ve
   satır aralıkları: [[css-bolme-plani]]. Menü sayfası şu an 202 KB CSS + 117 KB JS
   yüklüyor; LCP'nin asıl sebebi bu.
2. **Yeni eklenenler için test** — `neva:onkontrol`, `neva:veri-temizle`
   (saklama sınırının iki yanı), hukuki rotaların 200 dönmesi, çerez bildirimi ucu.
3. **KAPSAMLI SEO'nun kalanı** (hiç başlanmadı): blog/içerik modülü,
   şehir + mutfak bazlı landing sayfaları ("Ankara QR menü", "kafe QR menü"),
   FAQ schema, görsel alt/lazy/width-height, iç link mimarisi,
   Search Console + sitemap gönderimi.
4. **Üretim ayarlarının sunucuda uygulanması** — kod ve doküman tarafı bitti
   (`docs/URETIM.md` + `neva:onkontrol`); geriye gerçek sunucuda SMTP, PostgreSQL,
   queue:work servisi, cron ve wildcard sertifikanın kurulması kaldı. Bu iş sunucuda
   yapılır, kodda değil.
5. **Şirket künyesini doldur** — `NEVA_LEGAL_*` env değişkenleri boşken hukuki
   sayfalarda kırmızı "tanımlanmadı" rozeti görünür. Yayına almadan önce şart.
   Metinlerin avukata okutulması önerilir.
6. Online ödeme (iyzico) — hacim büyüyünce ([[havale-odeme-akisi]]). Kullanıcı bu
   maddeyi bilerek erteledi, dokunulmadı.
7. Görsel servisi ölçeklenirse: `/gorsel/...` için CDN/edge önbelleği ya da
   yayındaki dosyalar için imzalı URL. Koşullu; henüz gerek yok.
