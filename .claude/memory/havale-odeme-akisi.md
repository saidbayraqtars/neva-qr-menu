---
name: havale-odeme-akisi
description: Ödeme kartla değil havale/EFT ile alınır; online ödeme iş büyüyünce eklenecek
metadata:
  type: project
---

MVP'de **kartlı/online ödeme YOK**. Müşteri kayıt olur, sistem tekil bir referans kodu
(`NQR-XXXXXX`) üretir, IBAN + kod ekranda ve e-postada gösterilir. Havale gelince admin
"Havale geldi" işaretler, sonra "Onayla" der; hesap o an açılır.

Kritik kural: **ödeme işaretlenmeden onay verilemez** (`MembershipRequestController::approve`).

Banka bilgileri `config/neva.php › payment.bank`, `.env`'den beslenir
(`NEVA_BANK_IBAN`, `NEVA_BANK_ACCOUNT_NAME`, `NEVA_BANK_NAME`).

**Why:** İş henüz küçük; sanal POS/iyzico entegrasyonu maliyeti ve doğrulama yükü
şu aşamada gereksiz. Hacim artıp elle takip yetişmez hale gelince online ödemeye geçilecek.

**How to apply:** Online ödemeye geçerken `payment.mode` = `online` dalını ekle;
`MembershipRequest` tablosu (`reference_code`, `paid_at`, `payment_note`) zaten
sağlayıcı kaydı tutacak şekilde duruyor, `Subscription.provider` alanı `bank_transfer`
yerine sağlayıcı adını alır. Mevcut havale akışını SİLME — iki yöntem yan yana yaşasın.

İlgili: [[sifre-guvenlik-kurali]], [[kalan-isler]]
