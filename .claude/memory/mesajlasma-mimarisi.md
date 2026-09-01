---
name: mesajlasma-mimarisi
description: Panel içi çift yönlü chatbox; şimdilik 5 sn polling, WebSocket (Reverb) sonraya bırakıldı
metadata:
  type: project
---

İki ayrı kanal var, karıştırma:
- **Giriş YAPMAMIŞ ziyaretçi** → `/iletisim` formu → `contact_messages` → `/admin/iletisim`
- **Giriş YAPMIŞ kullanıcı** → ad/e-posta tekrar SORULMAZ → `conversations` + `messages`
  → panel `/panel/mesajlar` ↔ admin `/admin/mesajlar`

Canlılık şimdilik **5 saniyelik polling** (`components/chat-thread.blade.php` +
`.../mesajlar/{id}/yeni?after=<son_id>`). Okunmamış sayaçları `conversations` tablosunda
(`unread_for_user`, `unread_for_admin`) tutulur; sidebar rozetini `AppServiceProvider`
içindeki view composer besler.

**Why:** Reverb/WebSocket kurulumu ek servis + supervisor gerektiriyor; polling MVP için
yeterli ve altyapısız çalışıyor. `MessagingService::post()` tek giriş noktası olduğu için
broadcasting eklendiğinde tek bir yerden yayın yapılacak.

**How to apply:** Reverb'e geçerken `MessagingService::post()` içine `broadcast()` ekle ve
`chat-thread` bileşenindeki polling'i Echo aboneliğiyle değiştir; başka yeri değiştirmeye gerek yok.

İlgili: [[kalan-isler]]
