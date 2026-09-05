# Performans

Ölçümler bu makinede (PHP 8.4, SQLite, önbellek dosya sürücüsü) alındı.
Üretimde OPcache açık olduğu için sayılar daha iyi olacak — buradaki amaç
mutlak değer değil, **hangi kaldıracın ne kadar iş yaptığını** göstermek.

---

## 1. Ölçülen

### Küçük menü — 3 kategori / 7 ürün

| Durum | Süre | Sorgu | Yanıt |
|---|---|---|---|
| İlk istek (soğuk önbellek) | 51 ms | 3 | 13 KB |
| Sonraki istekler (önbellek isabet) | **1,4 ms** | **1** | 13 KB |
| Önbellek kapalı (her istek render) | 8,9 ms | 3 | 13 KB |

### Büyük menü — 12 kategori / 142 ürün

| Durum | Süre | Sorgu | Yanıt |
|---|---|---|---|
| İlk istek (soğuk önbellek) | 131 ms | 3 | 125 KB |
| Sonraki istekler (önbellek isabet) | **1,5 ms** | **1** | 125 KB |
| Önbellek kapalı (her istek render) | 101 ms | 3 | 125 KB |

PHP tepe bellek: 52 MB.

---

## 2. Okunacak üç sonuç

**Önbellek isabetinde tek sorgu var ve menü boyutundan bağımsız.**
1,4 ms ile 1,5 ms arasındaki fark ölçüm gürültüsü: menü 20 kat büyüdüğünde
önbellekli yanıt süresi değişmiyor. Çalışan tek sorgu `ResolveTenant`'ın
kiracı aramasıdır ve tekil indeks üzerinden gider.

Bu, ölçeklendirme sorusunu kapatıyor: **2 vCPU'lu bir VPS bu iş için fazlasıyla
yeterli.** Darboğaz CPU değil, ağ.

**N+1 yok.** Menü boyutundan bağımsız olarak render 3 sorguda tamamlanıyor
(restoran + kategoriler + ürünler). 142 ürün için de 7 ürün için de aynı.

**Önbelleksiz maliyet menüyle doğrusal büyüyor.** 7 üründe 9 ms, 142 üründe
101 ms. Bu yüzden `CACHE_STORE` üretimde asla `array`/`null` olmamalı —
`neva:onkontrol` bunu kontrol ediyor.

---

## 3. Kaldıraçlar

### 3.1 gzip (en büyük tek kazanç)

142 ürünlük menünün HTML'i **125 KB**. nginx sıkıştırmasıyla ~18 KB'a iniyor.
Mobil bağlantıda ilk açılış farkı doğrudan buradan gelir.

`deploy/nginx/nevaqr.conf` içinde açık geliyor. Kapatmayın.

### 3.2 Menü önbelleği

`menu_version` ile anahtarlanıyor: panelde bir değişiklik olduğunda sürüm artar,
anahtar değişir, bir sonraki istek yeniden render eder. TTL (2 saat) yalnızca
üst sınırdır.

`neva:warm-menus` 2 saatte bir çalışıp önbelleği tazeliyor — böylece gerçek
ziyaretçi soğuk isteği (131 ms) neredeyse hiç görmüyor.

### 3.3 OPcache

`deploy/kurulum.sh` `opcache.validate_timestamps=0` ile kuruyor: PHP her
istekte dosya tarihi kontrol etmiyor. Karşılığı, **dağıtımda php-fpm reload
zorunlu** — `deploy/guncelle.sh` bunu yapıyor. Reload edilmezse sunucu eski
kodu çalıştırmaya devam eder.

### 3.4 Statik varlık önbelleği

Vite içerik karmalı adlar üretiyor (`app-BFPCNVBN.css`), bu yüzden `/build/`
altı `immutable` ve 1 yıl önbellekli servis ediliyor. Yüklenen görseller de
karma adlı: yayındaki bir görsel 1 yıl önbelleklenebiliyor.

---

## 4. Bilinen darboğazlar

### 4.1 Şablon CSS'i tek pakette — ~189 KB (gzip ~32 KB)

40 şablonun CSS'i tek `app.css` içinde. Bir kiracı menüsü yalnızca bir şablon
kullanıyor ama hepsinin CSS'ini indiriyor.

**Etki:** LCP'ye ~30 KB. Tarayıcı önbelleği ilk ziyaretten sonra bunu kapatıyor,
ama QR menülerde ziyaretçilerin çoğu **ilk kez** geliyor — yani önbellek işe yaramıyor.

**Çözüm:** şablon başına ayrı CSS girişi (Vite'da her skeleton için bir entry)
ve `shell.blade.php`'de yalnız aktif şablonunkini yüklemek. Yol haritasında.

### 4.2 PDF üretimi headless Chrome başlatıyor

Her istek ~250-400 MB'lık bir süreç açıyor, 70 sn zaman aşımı var. 2 GB'lık
sunucuda birkaç eşzamanlı istek makineyi OOM'a sürükler.

**Azaltım (yapıldı):** PDF ucunun kendi sıkı hız sınırı var — 3/dakika, 20/saat.
Ayrıca `nevaqr-queue` servisinde `MemoryMax=384M` tanımlı.

**Sunucu büyüdüğünde:** PDF üretimini kuyruğa taşıyıp kullanıcıya hazır olunca
bağlantı göndermek daha doğru olur.

### 4.3 Menü değişikliği sonrası ilk ziyaretçi bedeli öder

Panelde fiyat güncellenince önbellek anahtarı değişir; o kiracının bir sonraki
ziyaretçisi 131 ms'lik soğuk render'ı görür. Kabul edilebilir — alternatifi
(değişiklikte hemen ısıtmak) yazma işlemini yavaşlatırdı.

---

## 5. Ölçümü tekrarlamak

```bash
php artisan tinker
```

```php
$r = App\Models\Restaurant::whereNotNull('subdomain')->first();
$host = $r->subdomain.'.'.config('neva.root_domain');

$t = microtime(true);
for ($i = 0; $i < 20; $i++) {
    app()->handle(Illuminate\Http\Request::create('http://'.$host.'/', 'GET'));
}
printf("ortalama %.1f ms\n", (microtime(true) - $t) / 20 * 1000);
```

Gerçek sunucuda dış ölçüm için:

```bash
curl -o /dev/null -s -w 'toplam %{time_total}s · ttfb %{time_starttransfer}s · %{size_download} B\n' \
  https://lumina.nevaqr.com/
```
