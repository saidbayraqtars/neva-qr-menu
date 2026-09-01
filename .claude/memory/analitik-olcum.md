---
name: analitik-olcum
description: Görüntülenme sayacı neden istemciden atılıyor ve neden gün+masa bazlı toplanıyor
metadata:
  type: project
---

Canlı menü HTML'i `menu_version` anahtarlı önbellekten servis ediliyor; çoğu istekte
sunucu render'ı hiç çalışmıyor. Bu yüzden **sayaç sunucuda artırılamaz**: sayfa
yüklendikten sonra tarayıcı `/olcum?yeni=…&masa=…` ucuna tek bir hafif GET atar.

- **GET, POST değil:** istemci CSRF jetonu taşımadan `fetch(..., {keepalive:true})`
  ile çağırabilsin diye. Uç kalıcı kullanıcı verisi yazmaz, yalnızca sayaç artırır.
- **`menu_visits` gün + masa etiketi bazında toplanır.** Ham istek kaydı YOK
  (KVKK): IP, konum, cihaz kimliği saklanmaz. Tablo istek sayısıyla değil gün
  sayısıyla büyür.
- **`table_label` NULL değil boş string.** NULL bazı veritabanlarında tekil indekste
  eşleşmediği için gün satırı çoğalırdı.
- **Yazma Eloquent değil query builder ile.** `visited_on` bir DATE kolonu; model
  cast'i değeri `Y-m-d H:i:s` olarak yazıp WHERE eşleşmesini bozuyordu (SQLite'ta
  değer birebir saklanır). Okurken de gün anahtarı `substr($v, 0, 10)` ile normalize
  edilir — sürücüye göre `Y-m-d` ya da `Y-m-d 00:00:00` dönebilir.
- **`/m/{token}` (eski jetonlu QR) sayacı ARTIRMAZ**; yönlendirdiği sayfa zaten
  ölçüm isteğini atıyor, iki kez sayılmasın diye kaldırıldı.
- "Farklı cihaz" sayısı tarayıcıdaki günlük localStorage işaretine dayanır; özel
  sekmede yalnızca görüntülenme sayılır.

İlgili: [[menu-onbellek-mimarisi]] · [[kalan-isler]]
