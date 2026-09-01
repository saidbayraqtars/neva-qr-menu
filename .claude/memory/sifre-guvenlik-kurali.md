---
name: sifre-guvenlik-kurali
description: Düz metin şifre asla saklanmaz; hesap açılışında tek kullanımlık şifre belirleme linki gider
metadata:
  type: project
---

Şifreler hiçbir yerde düz metin tutulmaz. Hesap açıldığında kullanıcıya **tek kullanımlık,
72 saat geçerli** bir "şifre belirle" linki e-posta ile gider. Jetonun yalnızca `sha256`
hash'i `users.password_setup_token` alanında durur.

Kaldırılan riskler (2026-09-01 öncesi kod):
- `users.temp_password` ve `membership_requests.temp_password` kolonlarında HASH'SİZ şifre
- Admin ekranlarında şifrenin gösterilmesi ve JS'e gömülmesi
- Flash mesajla oturum dosyasına düz metin şifre yazılması
- `membership_requests.temp_password`'ün hiç temizlenmemesi (kalıcı şifre arşivi)

İlgili kod: `User::issuePasswordSetupToken()`, `User::completePasswordSetup()`,
`Auth\PasswordSetupController`, `MembershipService::sendSetupLink()`.

**Why:** Admin'in kullanıcı şifresini görebilmesi hem KVKK hem temel güvenlik ihlali;
veritabanı sızıntısında tüm hesaplar doğrudan ele geçer.

**How to apply:** "Admin şifreyi görsün / WhatsApp'tan gönderelim" talebi gelirse HAYIR.
Yerine yeni bir şifre belirleme linki gönder (`admin.users.reset-password`). Şifreyi
loglama, flash'a koyma, bildirimde yazma.

İlgili: [[havale-odeme-akisi]]
