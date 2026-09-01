---
name: gelistirme-ortami
description: Bu makinede PHP/Composer PATH'te yok; git 2026-09-01'de kuruldu; vendor/ boş
metadata:
  type: project
---

- Bu Windows makinesinde `php` ve `composer` PATH'te **yok**; `vendor/` klasörü boş.
  Kod yazılabiliyor ama `artisan`, `migrate`, `test` buradan çalıştırılamıyor.
  README, PHP'nin `C:\Users\mehme\tools\` altında olduğunu söylüyor — o başka kullanıcı profili.
- Proje **2026-09-01'de git'e alındı** (`chore: mevcut durumun ilk kaydi (baseline)`).
  Öncesinde sürüm kontrolü yoktu.
- Proje OneDrive klasörü altında; SQLite kilit riski var. Üretimde PostgreSQL kullanılacak.
- Test/migration çalıştırmak için PHP kurulu bir terminal gerekiyor.

**Why:** Bir değişikliğin "çalıştığı" burada doğrulanamaz; ancak statik olarak yazılabilir.
Bu yüzden yapılan işin sonunda kullanıcıya çalıştırması gereken komutlar verilir.

**How to apply:** Yeni migration/servis yazdıktan sonra kullanıcıya şu sırayı hatırlat:
`composer install` → `php artisan migrate` → `php artisan test` → `npm run build`.
