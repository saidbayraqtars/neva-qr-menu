---
name: github-deposu
description: Projenin GitHub deposu (private) ve hangi hesapla açıldığı
metadata:
  type: reference
---

Depo: https://github.com/saidbayraqtars/neva-qr-menu — **private**, varsayılan dal `master`.
2026-09-02'de `gh repo create ... --source=. --push` ile açıldı; gh oturumu
`saidbayraqtars` hesabı (GH_TOKEN) ile.

- `.env` ve `database/*.sqlite` izlenmiyor; `storage/app/uploads` da `.gitignore` kapsamında
  (kullanıcı görselleri depoya gitmez) — bkz. [[gorsel-izolasyonu]].
- README'deki ekran görüntüleri `docs/ekran-goruntuleri/` altında, demo veriden headless
  Chrome ile üretildi (`chrome --headless=new --screenshot`).

İlgili: [[kalan-isler]]
