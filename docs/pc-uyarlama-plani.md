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

- [ ] alpine-clean (al)
- [ ] classic-bistro (cb)
- [ ] minimal-mono (mm)

### Öncelik 3 — tek kuralı olanlar (yalnızca sütun sayısı ayarlı)

- [ ] artisan-crafted (ac)
- [ ] atelier-soft (at)
- [ ] botanical-green (bgn)
- [ ] cinematic-dark (cnd)
- [ ] culinary-bento (cbn)
- [ ] cyber-dark (cy)
- [ ] dark-prestige (dp)
- [ ] floating-image (fli)
- [ ] glass-hero (ghr)
- [ ] glassmorphism-luxury (gl)
- [ ] golden-hour (gh)
- [ ] gourmet-masonry (gmm)
- [ ] grid-showcase (gsh)
- [ ] magazine-grid (mag)
- [ ] modern-grid (mg)
- [ ] neo-brutalism (nb)
- [ ] onyx-lux (ox)
- [ ] polaroid-vibe (pol)
- [ ] prime-steakhouse (ps)
- [ ] retro-diner (rd)
- [ ] riso-pop (rp)
- [ ] saffron-table (sf)
- [ ] split-card (spl)
- [ ] stories-style (sty)
- [ ] sunset-orange (so)
- [ ] sunset-vibes (sv)
- [ ] velvet-noir (vn)

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
