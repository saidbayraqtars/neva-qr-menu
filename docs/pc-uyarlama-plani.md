# Şablonların PC (masaüstü) uyarlaması — ilerleme defteri

Amaç: 40 şablonun tamamı geniş ekranda düzgün dursun. Mobil düzenler
bozulmadan kalsın.

Bu dosya oturumlar arası hafıza. Her şablon bitince satırı `[x]` yapıp
kısa not düşülür. Yeni oturuma başlarken önce burayı oku, ilk `[ ]` satırdan
devam et.

## Bağlam (2026-09-06 itibarıyla tamamlanan altyapı)

- Kiracı menüsü artık `view => 'auto'` ile basılıyor
  (`app/Http/Controllers/Tenant/MenuController.php`). Kök öğedeki satır içi
  betik `min-width: 1024px` üstünde `data-view`'i `desktop` yapıyor
  (`resources/views/components/templates/shell.blade.php`).
- Masaüstü sarmalayıcısı: `min(1360px, 94vw)`. Telefonda 480px.
- `.tpl` flex sütun, `.tpl-wrap` `flex: 1` → kısa menüde zemin ekranı dolduruyor.
- Yatay kategori çubukları farede tekerlek + sürükle ile kayıyor
  (`.rb-nav`, `.bgn-tabs`, `[data-hscroll]`).
- `vite.config.js`'te `emptyOutDir: false` — dokunma, sebebi hafızada.

## Üretimde kullanım (kontrol: sunucudaki sqlite)

| Kiracı | Şablon | Durum |
| --- | --- | --- |
| meydan | royal-blue | canlı |
| coban-restaurant | minimalist-kaffe | yayınlanmamış |

Kalan 38 şablon yalnızca pazarlama vitrininde görünüyor; müşteri seçince
canlıya çıkacakları için hepsi tamamlanacak.

## Her şablonda uygulanacak yöntem

1. Şablonun CSS bloğunu aç (`resources/css/app.css`, bölüm başlıkları
   numaralı: `/* --- N · ŞABLON ADI */`).
2. Mevcut `.tpl[data-view="desktop"]` kurallarını oku. Çoğunda yalnızca
   "grid'i 2 sütun yap" var; yeterli değil.
3. 1360px'te şu altı noktaya bak:
   - **Sütun sayısı**: kart listeleri 2–3 sütun, liste/satır düzenleri 2 sütun
     (`columns` veya `grid-template-columns`).
   - **Kapak/hero yüksekliği**: mobilde 170–200px olan alan geniş ekranda
     bant gibi kalıyor; 260–320px'e çıkar.
   - **Tipografi**: başlık ve fiyat mobil ölçekte kalmasın; `.tpl-h` ve isim
     boyutlarını bir kademe büyüt.
   - **Yatay boşluk**: `padding: * 14–18px` değerleri 1360px'te dar bir şeride
     yapışık duruyor; 28–40px yap.
   - **Görsel oranı**: kart fotoğrafları geniş sütunda esneyip bozuluyor mu
     (`aspect-ratio`, `object-fit`, sabit `width`).
   - **Sticky öğeler**: kategori çubuğu / kenar çubuğu geniş ekranda doğru
     yerde mi, `top` değeri doğru mu.
4. Kuralları ilgili şablon bölümünün sonuna, mevcut desktop satırlarının
   yanına ekle. Yeni bölüm açma, dosyanın sırası korunsun.
5. `npm run build` → tarayıcıda üç genişlikte doğrula: **1900** (1360'a
   kırpılır), **1100** (masaüstü eşiğinin hemen üstü), **375** (mobil
   bozulmamalı). Yerelde kiracının şablonunu geçici değiştirip test et,
   sonra geri al.
6. Şablon bitince bu dosyada işaretle, 4–6 şablonluk gruplar hâlinde commit
   at ve dağıt (`ssh nevaqr "sudo bash /var/www/nevaqr/deploy/guncelle.sh"`).

Test ederken tarayıcı eski HTML'i 2 saat tutabilir; URL'ye `?cb=1` ekle.

## Sıra ve durum

### Öncelik 1 — üretimde kullanılan

- [x] royal-blue (rb) — 2026-09-06. Hero 300px, logo 56px, başlık 38px; kenar
      çubuğu 244px; kartlar `auto-fill minmax(430px)` → 1360'ta 2 sütun (514px),
      1100'de tek sütun. minmax(330px) denendi, 3 sütunda ürün adları kırılıyordu.
- [x] minimalist-kaffe (mk) — 2026-09-06. Kartsız liste geniş ekranda incecik
      kalıyordu: `.mk-list` iki sütun (`columns: 2`, `break-inside: avoid`),
      dolgu 56/40px, başlık 28px, satır tipografisi bir kademe büyük.

### Öncelik 2 — desktop kuralı hiç olmayanlar

- [x] alpine-clean (al) — 2026-09-06. Sütun 900px'e çıktı ve ORTALANDI; eskiden
      max-width vardı ama `margin: 0 auto` yoktu, sola yapışık duruyordu.
      Şablona özel stil paketindeki 680px kuralı da güncellendi (daha özgül
      seçici olduğu için genel kuralı eziyordu). Tek sütun bilinçli: tasarımın
      kimliği İskandinav boşluk.
- [x] classic-bistro (cb) — 2026-09-06. Kâğıt 1060px ortalandı, dolgu 40/44px,
      başlık 36px; ürün ızgarası iki yerine üç sütun (307px).
- [x] minimal-mono (mm) — 2026-09-06. Liste iki sütun (`columns: 2`), dolgu
      72/44px, başlık 32px.

### Öncelik 3 — tek kuralı olanlar (yalnızca sütun sayısı ayarlı)

- [ ] artisan-crafted (ac)
- [ ] atelier-soft (at)
- [x] botanical-green (bgn) — 2026-09-06. Kart ızgarası tek sütundan üçe;
      sekmeler geniş ekranda ortalanıp sarıyor, başlık 34px, dolgular 32px.
- [ ] cinematic-dark (cnd)
- [ ] culinary-bento (cbn)
- [x] cyber-dark (cy) — 2026-09-06. Izgara 3→4 sütun, kapak 320px, başlık 38px.
- [x] dark-prestige (dp) — 2026-09-06. Kapaklı başlık 380px, başlık 40px,
      kart listesi 2→3 sütun (410px).
- [x] floating-image (fli) — 2026-09-06. Izgara 3→4 sütun; yuvarlak görsel kartı
      taştığı için satır arası boşluk 52px ve kart üst dolgusu 72px yapıldı.
- [x] glass-hero (ghr) — 2026-09-06. Izgara 3→4 sütun, cam başlık bloğu 42px
      dolgu, başlık 34px.
- [x] glassmorphism-luxury (gl) — 2026-09-06. Izgara 3→4 sütun, cam başlık
      bloğu 44px dolgu, logo 72px, başlık 36px.
- [ ] golden-hour (gh)
- [x] gourmet-masonry (gmm) — 2026-09-06. Masonry 4 sütun, boşluklar 18px,
      başlık 34px, kart tipografisi bir kademe büyük.
- [x] grid-showcase (gsh) — 2026-09-06. Izgara 3→4 sütun, kapaklı başlık
      320px, başlık 34px.
- [x] magazine-grid (mag) — 2026-09-06. Masonry 3→4 sütun, başlık 46px; manşet
      kartın görseli %46→%34 (tüm sütunları kapladığı için metne yer açıldı).
- [x] modern-grid (mg) — 2026-09-06. Izgara 3→4 sütun, kapak 200→320px,
      kart yazıları bir kademe büyük.
- [ ] neo-brutalism (nb)
- [x] onyx-lux (ox) — 2026-09-06. Fotoğraf bantları 2→3 sütun (450px), bant
      yüksekliği 260px, başlık 46px, açıklama genişliği %80 sınırı kaldırıldı.
- [x] polaroid-vibe (pol) — 2026-09-06. Masonry 4 sütun, kart dolgusu ve
      aralıkları büyütüldü, ürün adı 22px.
- [x] prime-steakhouse (ps) — 2026-09-06. Liste 2 sütun (639px), medya 132px,
      başlık 52px.
- [ ] retro-diner (rd)
- [ ] riso-pop (rp)
- [x] saffron-table (sf) — 2026-09-06. Kart listesi 2→3 sütun (420px), yuvarlak
      thumb 74px, başlık 36px.
- [x] split-card (spl) — 2026-09-06. İki sütun (640px), kart yüksekliği 160px,
      medya oranı %42→%38, başlık 32px.
- [x] stories-style (sty) — 2026-09-06. Kart ızgarası 3→4 sütun, kapaklı başlık
      320px, dolgular 28px.
- [x] sunset-orange (so) — 2026-09-06. Hero 320px, başlık 42px, thumb 132px,
      liste 2 sütun (640px).
- [x] sunset-vibes (sv) — 2026-09-06. Masonry 4 sütun kalıyor; boşluklar 18px,
      başlık 34px, kart yazıları büyütüldü.
- [x] velvet-noir (vn) — 2026-09-06. İki sütun arası 64px, fiyat sütunu 82px,
      başlık 38px, dolgu 44px.

### Öncelik 4 — iki üç kuralı olanlar (en az iş)

- [ ] carbon-mono (cm)
- [ ] coastal-breeze (cbz)
- [ ] compact-fast (cf)
- [ ] heritage-press (hp)
- [ ] kyoto-calm (ky)
- [ ] linen-note (ln)
- [ ] neon-street (ns)
- [ ] urban-chic (uc)

## Notlar

- **Yerelde test ederken her `npm run build` sonrası `php artisan cache:clear`
  şart.** Kiracı menüsü HTML'i önbellekte ve içinde eski varlık adı yazıyor;
  temizlemezsen tarayıcı bir önceki CSS'i yükler ve değişikliği göremezsin.
  Ayrıca URL'ye `?x=<zaman>` ekleyerek tarayıcı önbelleğini de atla.
- Yerel testte kiracının şablonunu geçici değiştirmek en pratiği:
  `php artisan tinker --execute="\$r=App\Models\Restaurant::where('slug','ocakbasi')->first(); \$r->template='<sablon>'; \$r->save();"`
  Bitince `sunset-orange`'a geri al.
- Yerel sunucu: `php artisan serve --host=127.0.0.1 --port=8123`, adres
  `http://ocakbasi.localhost:8123/`.
- 2026-09-06: Öncelik 1 tamamlandı ve dağıtıldı.
- 2026-09-06: Öncelik 2 tamamlandı ve dağıtıldı.
- 2026-09-06: Öncelik 3 · 3. grup (masonry ve kart düzenleri: sv, mag, sty,
  pol, fli, spl, ghr, gmm) tamamlandı ve dağıtıldı. Masonry'de sütun sayısını
  artırırken `column-gap` ve kart `margin-bottom` de artmalı, yoksa kartlar
  birbirine yapışıyor.
- 2026-09-06: Öncelik 3 · 2. grup (liste/satır düzenleri: so, dp, ox, sf, ps,
  vn) tamamlandı ve dağıtıldı. Satır düzenlerinde sütun sayısını artırmadan
  önce satır içeriğine bak: yatay kart (fotoğraf + metin) 2 sütundan fazlasını
  kaldırmıyor, salt metin satırı 3 sütuna çıkabiliyor.
- 2026-09-06: Öncelik 3 · 1. grup (fotoğraflı ızgaralar: mg, gsh, gl, cy, bgn)
  tamamlandı ve dağıtıldı. Fotoğraflı ızgaralarda 1360px'te 4 sütun iyi
  duruyor; kart içi tipografi de bir kademe büyütülmeli, yoksa kartlar
  büyüyüp yazılar minicik kalıyor.
- Dikkat: `resources/css/app.css` sonundaki "stil paketi" bölümü
  (`.tpl[data-tpl="..."]` seçicileri) şablon bazında daha özgül olduğu için
  ana bölümdeki kuralları ezebiliyor. Bir değişiklik uygulanmıyorsa orayı ara.
