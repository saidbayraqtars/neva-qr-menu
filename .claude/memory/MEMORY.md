# Neva-QR Menü — Proje Hafızası

Bu klasör projeye özel kalıcı notları tutar. Her dosya tek bir konuyu anlatır.
Kod okunarak anlaşılabilen şeyler burada TEKRARLANMAZ; yalnızca "neden böyle" bilgisi durur.

- [Proje hedefi ve ürün mantığı](proje-hedefi.md) — 40 farklı tasarım, işletmeye özel subdomain (galya.nevaqr.com)
- [Ödeme: havale/EFT akışı](havale-odeme-akisi.md) — kartlı ödeme YOK, referans kodu + admin onayı
- [Paket bazlı yetki matrisi](paket-yetki-matrisi.md) — config'ten okunur, sunucu tarafında zorlanır
- [Şifre güvenliği kuralı](sifre-guvenlik-kurali.md) — düz metin şifre asla saklanmaz, tek kullanımlık link
- [Alt domain yayına alma akışı](alt-domain-yayin-akisi.md) — talep → onay → DNS → otomatik doğrulama
- [Canlı menü önbellek mimarisi](menu-onbellek-mimarisi.md) — menu_version + 2 saat TTL
- [Mesajlaşma (chatbox) mimarisi](mesajlasma-mimarisi.md) — polling şimdilik, Reverb sonra
- [Kiracı rota parametresi tuzağı](kiraci-rota-parametresi.md) — {tenant} ilk argümandır, bildirilmeli
- [CSP ve varlık URL'leri](csp-ve-varlik-urlleri.md) — görsel yolları host'suz olmalı
- [Geliştirme ortamı notları](gelistirme-ortami.md) — PHP 8.4 + Composer kurulumu ve php.ini ayarı
- [Görüntülenme ölçümü (analitik)](analitik-olcum.md) — sayaç istemciden atılır, gün + masa bazlı toplanır
- [Görsel izolasyonu](gorsel-izolasyonu.md) — yüklemeler özel diskte, /gorsel ucundan yetkiyle servis
- [GitHub deposu](github-deposu.md) — private repo saidbayraqtars/neva-qr-menu
- [Alt domain TLS gereksinimi](alt-domain-tls-gereksinimi.md) — "onayla → yayında" için wildcard DNS + wildcard sertifika + vhost; `neva:onkontrol`
- [KVKK ve saklama süreleri](kvkk-ve-saklama-sureleri.md) — hukuki metinler ve `neva:veri-temizle` aynı config'ten beslenir
- [CSS bölme planı](css-bolme-plani.md) — menü sayfası 202 KB CSS + 117 KB JS yüklüyor; keşif bitti, uygulama yarım
- [Kalan işler ve sıradaki adımlar](kalan-isler.md) — SEO'nun büyük kısmı açık, CSS bölme yarım
