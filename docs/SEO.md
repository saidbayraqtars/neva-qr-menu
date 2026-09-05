# SEO

Bu belge iki bölümdür: **kodun kendiliğinden yaptıkları** (dokunmanız gerekmeyen)
ve **yayına alırken sizin yapmanız gerekenler** (kodun yapamayacağı).

Her dağıtımdan sonra kontrol:

```bash
php artisan neva:onkontrol --seo
```

---

## 1. Kodun otomatik yaptıkları

### 1.1 Yayınlanan sayfa envanteri

| Adres | Ne | Kaç sayfa |
|---|---|---|
| `/` | Ana sayfa | 1 |
| `/qr-menu-sablonlari` | Şablon galerisi | 1 |
| `/qr-menu-sablonlari/{şablon}` | Şablon tanıtımı | **40** |
| `/fiyatlandirma` | Paketler | 1 |
| `/sikca-sorulan-sorular` | S.S.S. | 1 |
| `/hakkimizda`, `/iletisim` | Kurumsal | 2 |
| Hukuki metinler | KVKK, gizlilik, çerez, koşullar | 4 |
| `{kiracı}.{kök}` | Her yayındaki müşteri menüsü | müşteri sayısı |

Toplam **50 sabit sayfa** + müşteri başına 1 menü.

Şablon sayfalarının içeriği `config/neva.php › templates` ve
`app/Support/ShowcaseCopy.php` üzerinden **şablonun kendi özelliklerinden** üretilir.
Yeni bir şablon eklediğinizde vitrin sayfası, sitemap kaydı ve iç linkler
kendiliğinden oluşur — elle iş yoktur.

### 1.2 Teknik işaretleme

- **Kanonik adres**: her sayfada, `APP_URL` üzerinden. Kiracı menüleri kendi alt
  domainlerini kanonikler.
- **robots**: ana domainde `/robots.txt`, her kiracıda kendi `/robots.txt`'i.
  Panel, admin ve kimlik uçları taramaya kapalı.
- **sitemap**: `/sitemap.xml` (pazarlama + 40 şablon + yayındaki kiracılar),
  her kiracıda `/sitemap.xml` (menü + kategoriler). 1 saat önbellekli.
- **Open Graph + Twitter Card**: `resources/views/components/seo.blade.php`.
- **Şablon önizlemeleri** (`/sablon-onizleme/...`) `X-Robots-Tag: noindex` ile
  servis edilir: iframe'e gömülü oldukları için ayrı sayfa olarak indekslenmemeliler.

### 1.3 Yapısal veri (JSON-LD)

`app/Support/MenuSchema.php` tek kaynaktır.

| Sayfa | Düğümler |
|---|---|
| Ana sayfa | `Organization` + `WebSite` + `SoftwareApplication` + `FAQPage` (ilk 4 soru) |
| Şablon galerisi | `CollectionPage` + `ItemList` (40 öğe) + `BreadcrumbList` |
| Şablon sayfası | `CreativeWork` + `BreadcrumbList` |
| Fiyatlandırma | `ItemList` › paket başına `Offer` |
| S.S.S. | `FAQPage` (10 soru) |
| Kiracı menüsü | `Restaurant` › `hasMenu` › `MenuSection` › `MenuItem` + `priceRange` |

Düğümler sabit `@id`'lerle (`#organization`, `#website`, `#app`) birbirine bağlıdır;
Google bunları tek bir varlık grafiği olarak okur.

### 1.4 İç link yapısı

Hiçbir sayfa 2 tıktan derinde değil:

- Üst menü → Şablonlar, Fiyatlar, S.S.S., Hakkımızda
- Alt bilgi (her sayfada) → 8 öne çıkan şablon + "40 şablonun tümü"
- Şablon sayfası → 3 benzer şablon + önceki/sonraki + galeriye dönüş

---

## 2. Yayına alırken yapılacaklar

Bunlar kodla halledilemez; hesap açmayı ve alan adı sahipliğini gerektirir.

### 2.1 Zorunlu

1. **`.env` doldurun** — `neva:onkontrol --seo` çıktısındaki her satır yeşil olana kadar:
   - `APP_URL=https://nevaqr.com` (http kalırsa site kendini http'ye kanonikler)
   - `NEVA_ROOT_DOMAIN=nevaqr.com`
   - `NEVA_LEGAL_TITLE`, `NEVA_LEGAL_ADDRESS` — `Organization` işaretlemesinin kaynağı
   - `NEVA_SEO_INSTAGRAM` vb. — yalnızca **gerçekten var olan** profiller

2. **Tek kanonik host seçin.** `www.nevaqr.com` ve `nevaqr.com` ikisi de yanıt
   veriyorsa biri diğerine **301** yönlendirmeli. Aksi halde aynı içerik iki
   adreste görünür ve sıralama ikiye bölünür. Nginx tarafında:

   ```nginx
   server {
       listen 443 ssl http2;
       server_name www.nevaqr.com;
       return 301 https://nevaqr.com$request_uri;
   }
   ```

3. **Google Search Console** — `nevaqr.com` için *Domain* türünde mülk açın
   (alt domainleri de kapsar: her müşteri menüsünü ayrıca eklemenize gerek kalmaz).
   Doğrulamayı DNS TXT kaydıyla yapın, sonra `https://nevaqr.com/sitemap.xml`
   adresini gönderin.

4. **Bing Webmaster Tools** — Search Console'dan içe aktarma ile 2 dakikada biter.
   Türkiye'de yok sayılabilir bir trafik değil.

### 2.2 Önerilen

5. **Google Business Profile** — şirketin kendi kaydı. `sameAs` ile eşleşince
   marka aramalarında kurumsal panel çıkar.

6. **Analitik.** Şu an sitede üçüncü taraf izleyici **yok** — bu bilinçli bir
   karar (çerez bildirimi ve KVKK metni buna göre yazıldı). Analitik eklerseniz:
   - Çerez politikası ve KVKK metnini güncelleyin
   - `SecurityHeaders` içindeki CSP'ye ilgili host'u ekleyin (aksi halde script
     sessizce engellenir)
   - Onay öncesi çerez yazmayan bir kurulum tercih edin

7. **Müşteri menülerini Search Console'a bildirmek gerekmez.** Kiracı alt
   domainleri ana `sitemap.xml` içinde listelenir ve her kiracının kendi
   `sitemap.xml`'i vardır.

---

## 3. İçerik: sıradaki adım

Şu an site **ürün sayfalarında** güçlü, **bilgi aramalarında** yok. Türkiye'de
"qr menü" etrafındaki aramaların büyük kısmı satın alma niyeti taşımayan
sorulardır ve bunları karşılayan sayfamız yok.

Eklenecek en değerli sayfalar (etki sırasıyla):

1. `qr menü nasıl yapılır` — adım adım rehber, ürüne doğal geçiş
2. `qr menü fiyatları` — karşılaştırmalı, dürüst bir fiyat yazısı
3. `restoran menü tasarımı nasıl olmalı` — şablon sayfalarına iç link deposu
4. `dijital menü vs basılı menü` — maliyet karşılaştırması
5. Şehir bazlı sayfalar (`istanbul qr menü`) — **yalnızca** gerçekten o şehirde
   müşteriniz varsa; içi boş şehir sayfaları zarar verir

Bu sayfalar bir blog altyapısı gerektirir (şu an yok). Basit bir başlangıç:
`resources/views/marketing/rehber/` altında Blade sayfaları + `routes/web.php`'de
sabit rotalar. Veritabanı gerektirmez, 5 yazıya kadar bu yeterlidir.

---

## 4. Ölçüm

İlk 3 ay için gerçekçi beklenti:

| Süre | Beklenen |
|---|---|
| 0-4 hafta | İndeksleme. Search Console'da "Sayfalar" bölümünde 50 sayfanın çoğu "Dizine eklendi" olmalı. |
| 1-3 ay | Uzun kuyruk sorgularda görünme ("dark prestige qr menü", "koyu tema dijital menü"). |
| 3-6 ay | "qr menü" gibi rekabetli aramalarda ilk 3 sayfa — ancak içerik bölümü (§3) yapılırsa. |

İndeksleme sorunlarında ilk bakılacak yer Search Console › Sayfalar › "Dizine
eklenmedi" nedenleri. `neva:onkontrol --seo` yeşilse teknik taraf sağlamdır.
