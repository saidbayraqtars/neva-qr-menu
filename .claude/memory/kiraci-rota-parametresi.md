---
name: kiraci-rota-parametresi
description: Kiracı rotalarında alan adındaki {tenant} parametresi ilk sırada gelir; controller metotlarında BİLDİRİLMELİ
metadata:
  type: project
---

`routes/tenant.php` içindeki her rota `Route::domain('{tenant}.'.$root)` altında tanımlıdır.
Laravel rota parametrelerini controller metoduna **sırayla** geçirir ve `{tenant}` her zaman
ilk sıradadır.

Bu yüzden skaler parametre alan her kiracı controller metodu `$tenant`'ı ilk argüman olarak
bildirmek zorundadır:

```php
public function category(string $tenant, string $slug): Response
public function fromTable(string $tenant, string $token): RedirectResponse
```

Bildirilmezse alt domain adı (`lumina`) ikinci parametreye düşer. Bu sessiz bir hatadır:
`/m/{token}` uzun süre yanlış çalıştı (her zaman `/` adresine yönlendirdi), `/kategori/{slug}`
ise 500 verdi.

Ortülü (implicit) route-model binding de bu grupta güvenilir değil — kategori artık
`app('tenant')->categories()->active()->where('slug', $slug)->firstOrFail()` ile aranıyor.
Yan fayda: kiracı izolasyonu sorgu seviyesinde garanti (başka restoranın slug'ı 404 döner).

**Why:** Alan adı parametresi olan rota gruplarında bu davranış belgelenmemiş bir tuzak;
iki ayrı hata aynı kökten çıktı.

**How to apply:** `routes/tenant.php`'ye yeni rota eklerken parametre sırasını kontrol et,
mümkünse ortülü binding yerine tenant üzerinden açık sorgu kullan.

İlgili: [[proje-hedefi]], [[menu-onbellek-mimarisi]]
