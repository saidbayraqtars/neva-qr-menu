---
name: paket-yetki-matrisi
description: Paket yetkileri config/neva.php plan_features içinde; view değil sunucu zorlar
metadata:
  type: project
---

Yetki matrisi `config/neva.php › plan_features` içinde, anahtar = `plans.slug`.
Paketi olmayan kullanıcı `default` satırına düşer.

- Yetenek kontrolü: `->middleware('plan:subdomain')` (`EnsurePlanFeature`)
- Sayısal limit: `PlanGate::authorizeLimit($restaurant, 'products', $mevcut, $eklenen, $alan)`
- Model kısayolları: `$restaurant->planAllows('tables')`, `$restaurant->planLimit('products')`

**Why:** Önceden yetki yalnızca Blade `@if`'leri ile gizleniyordu; kullanıcı doğrudan POST
atarak paketinde olmayan özelliği kullanabiliyordu (alt domain talebi, masa ekleme).
View'deki `@if`'ler artık sadece görsel; gerçek engel sunucuda.

**How to apply:** Yeni bir paket özelliği eklerken önce matrise bir yetenek adı ekle,
`feature_labels`'a insan dilinde karşılığını yaz, sonra rotayı `plan:<ad>` ile sar.
Testler: `tests/Feature/PlanAccessTest.php` — yeni yetenek için oraya da sızma testi ekle.

Not: Paketler tek seferlik satın alma (`interval = once`, bitiş tarihi yok), bu yüzden
"abonelik süresi doldu" kontrolü bilerek yazılmadı. Aylık abonelik gelirse `Subscription::isValid()`
zaten hazır — panel grubuna bir middleware ile bağlanması yeterli.

İlgili: [[proje-hedefi]], [[havale-odeme-akisi]]
