---
name: gelistirme-ortami
description: PHP 8.4 + Composer 2026-09-01'de winget ile kuruldu; php.ini elle yapılandırıldı; git aynı gün başlatıldı
metadata:
  type: project
---

- **PHP 8.4.24 + Composer 2.10.3 kuruldu (2026-09-01).** winget ile, şu klasöre:
  `C:\Users\saidb\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\`
  Klasör kullanıcı PATH'inde; içinde `php.exe`, `composer.phar` ve `composer.bat` var.
- **`php.ini` elle oluşturuldu** (`php.ini-development` kopyası) ve şu eklentiler açıldı:
  curl, fileinfo, gd, intl, mbstring, openssl, pdo_sqlite, sqlite3, zip, sodium, exif.
  Kurulum sonrası PHP hiçbir ini dosyası yüklemiyordu — eklentiler bu adım olmadan kapalı.
- **PHP 8.3 winget paketi KURULMUYOR:** indirme adresi 404 döner (sürüm downloads.php.net'te
  arşive taşınmış). 8.4 kullanıldı; Laravel 12 destekliyor.
- **Composer winget'te yok.** `composer.phar` getcomposer.org'dan indirildi, sha384 imzası
  composer.github.io/installer.sig ile doğrulandı.
- PDF üretimi için Chrome mevcut: `C:\Program Files\Google\Chrome\Application\chrome.exe`.
- Proje **2026-09-01'de git'e alındı** (`chore: mevcut durumun ilk kaydi (baseline)`).
  Öncesinde sürüm kontrolü yoktu.
- Proje OneDrive klasörü altında; SQLite kilit riski var. Üretimde PostgreSQL kullanılacak.
- `storage/framework/{views,cache,sessions}` ve `storage/logs` bir kez yanlışlıkla commit'lendi,
  sonra `.gitignore`'a eklenip repodan çıkarıldı.

**Why:** Ortam sıfırdan kuruldu; hangi sürümün neden seçildiği ve php.ini adımının neden
şart olduğu tekrar araştırılmasın.

**How to apply:** Yeni terminalde `php` ve `composer` doğrudan çalışır. Sunucu:
`php artisan serve --host=127.0.0.1 --port=8000` (`.claude/launch.json` bunu tanımlar).
Test: `php artisan test --exclude-group=slow` — `slow` grubu 40 şablonun PDF'ini headless
Chrome ile üretir, tek başına ~7 dakika sürer.

İlgili: [[kalan-isler]]
