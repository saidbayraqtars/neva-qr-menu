---
name: kvkk-ve-saklama-sureleri
description: Hukuki metinler config'ten beslenir; saklama süreleri hem metinde hem neva:veri-temizle komutunda AYNI kaynaktan okunur
metadata:
  type: project
---

2026-09-02'de eklendi. Dört hukuki sayfa + çerez bildirimi + otomatik veri temizliği.

## Rotalar ve dosyalar

| Rota | Ad | View |
|------|----|------|
| `/gizlilik-politikasi` | `legal.privacy` | `legal/gizlilik.blade.php` |
| `/kvkk-aydinlatma-metni` | `legal.kvkk` | `legal/kvkk.blade.php` |
| `/cerez-politikasi` | `legal.cookies` | `legal/cerez.blade.php` |
| `/kullanim-kosullari` | `legal.terms` | `legal/kosullar.blade.php` |
| `POST /cerez-bildirimi` | `legal.cookies.accept` | — |

Ortak iskelet: `components/legal-page.blade.php` (yapışkan içindekiler + diğer
metinlere çapraz bağlantı). Tipografi `resources/css/app.css` içinde `.legal-doc`
bloğu — Tailwind typography eklentisi projede YOK, o yüzden elle yazıldı.

## Tek kaynak kuralı (önemli)

Saklama süreleri `config/neva.php › legal.retention` altında **tek yerde** durur.
Aynı değerleri hem KVKK metnindeki tablo hem de `neva:veri-temizle` komutu okur.
**Metinde yazan süre ile fiilen silinen süre asla ayrışmasın diye böyle yapıldı** —
politika metninde 2 yıl yazıp veriyi 5 yıl tutmak KVKK m.4/2-d ihlalidir.

Varsayılanlar: iletişim formu 730 gün (IP'si 90 günde maskelenir), denetim kaydı
730, görüntülenme sayacı 395, reddedilmiş üyelik 365 gün. `0` verilirse o tür silinmez.

`neva:veri-temizle` günlük 03:20'de çalışır (`routes/console.php`), `--dry-run`
destekler. Silme `chunkById(500)` ile parça parça yapılır — tek seferde yüz binlerce
satır silmek kilidi uzun tutar. **Onaylanmış üyelik taleplerine dokunmaz** (sözleşme kaydı).

## Şirket künyesi

`config/neva.php › legal.company` — `NEVA_LEGAL_*` env değişkenleriyle doldurulur.
Boş bırakılan alan sayfada **kırmızı `[... — .env'de tanımlanmadı]` rozetiyle görünür**
(`components/legal/value.blade.php`). Sessizce boş bırakmak yerine göze batması
bilinçli: künyesiz aydınlatma metni KVKK m.10 karşısında geçersizdir.
**Yayına almadan önce doldurulmalı.**

## Çerez bildirimi

`components/cookie-notice.blade.php`, marketing-layout'ta. Tek düğme: "Anladım".
**Rıza ekranı değil** — kullanılan çerezlerin tamamı zorunlu (oturum, CSRF,
bildirimin kendisi), rıza gerektirmez. Kabul/red düğmesi koymak sahte seçim olurdu.
**QR menü sayfalarında gösterilmez** — orada hiç çerez oluşturulmuyor.

Menüdeki tek istemci kaydı: `localStorage` anahtarı `neva-visit-<yyyy-aa-gg>`
(sunucuya gönderilmez). Çerez politikasındaki tablo bu adı birebir yazar; ad
değişirse metin de güncellenmeli.

## Not

Metinler platformun gerçek veri akışlarına göre yazıldı, hukuk danışmanı onayından
geçmedi. Yayına almadan önce avukata okutulması önerilir.

İlgili: [[analitik-olcum]], [[gorsel-izolasyonu]], [[sifre-guvenlik-kurali]], [[havale-odeme-akisi]]
