<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Karşı tarafa yeni mesaj bildirimi.
 * Admin yazdıysa kullanıcıya, kullanıcı yazdıysa admine gider.
 *
 * ADMİNE GİDEN SÜRÜM İLETİŞİM BİLGİSİ TAŞIR: destek çoğu zaman yazışmayla
 * değil telefonla çözülüyor. Bildirimde numara yoksa admin önce panele girip
 * müşteriyi bulmak zorunda kalıyor; numara e-postada olunca telefondan tek
 * dokunuşla aranabiliyor. Müşteriye giden sürümde bu bilgiler YOKTUR.
 */
class SupportMessagePosted extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public Message $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->message->isFromAdmin()
            ? $this->toCustomer()
            : $this->toAdmin();
    }

    /** Müşteriye: "size yanıt geldi" — fazlası gerekmez. */
    private function toCustomer(): MailMessage
    {
        return (new MailMessage)
            ->subject('Destek ekibinden yanıt — '.$this->conversation->subject)
            ->greeting('Merhaba,')
            ->line('"'.$this->conversation->subject.'" konulu görüşmede yeni bir mesaj var:')
            ->line('"'.Str::limit($this->message->body, 300).'"')
            ->action('Mesajı aç', route('panel.messages.show', $this->conversation));
    }

    /** Admine: kim, hangi işletme, hangi paket, hangi numara. */
    private function toAdmin(): MailMessage
    {
        $conversation = $this->conversation->loadMissing(['user', 'restaurant']);
        $user = $conversation->user;
        $restaurant = $conversation->restaurant;

        // Kişisel numara yoksa işletme numarasına düş.
        $phone = $user?->phone ?: $restaurant?->phone;

        $mail = (new MailMessage)
            ->subject('Yeni destek mesajı — '.($restaurant?->name ?: $user?->name ?: 'bilinmeyen'))
            ->greeting('Yeni destek mesajı')
            ->line('**İşletme:** '.($restaurant?->name ?: '—'))
            ->line('**Kişi:** '.($user?->name ?: '—'))
            ->line('**E-posta:** '.($user?->email ?: '—'));

        if ($phone) {
            // tel: bağlantısı — telefondan okunduğunda tek dokunuşla arama.
            $mail->line('**Telefon:** ['.$phone.'](tel:'.$this->dialable($phone).')');
        } else {
            $mail->line('**Telefon:** kayıtlı değil');
        }

        if ($restaurant?->whatsapp) {
            $mail->line('**WhatsApp:** [Sohbet aç](https://wa.me/'.$this->dialable($restaurant->whatsapp).')');
        }

        $plan = $user?->currentPlan();
        if ($plan) {
            $mail->line('**Paket:** '.$plan->name);
        }

        if ($restaurant?->isLive()) {
            $mail->line('**Adres:** '.tenant_domain($restaurant));
        }

        return $mail
            ->line('---')
            ->line('**Konu:** '.$conversation->subject)
            ->line('"'.Str::limit($this->message->body, 500).'"')
            ->action('Panelde yanıtla', route('admin.messages.show', $conversation));
    }

    /**
     * `tel:` / `wa.me` için numara: rakam dışı her şey atılır.
     * Kullanıcı "0555 123 45 67" ya da "+90 555..." girmiş olabilir; ikisi de
     * çalışır, telefon uygulaması baştaki sıfırı kendi yorumlar.
     */
    private function dialable(string $phone): string
    {
        return preg_replace('/[^0-9+]/', '', $phone) ?? $phone;
    }
}
