<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Alt domain talebi onaylandı ya da reddedildi. */
class SubdomainDecision extends Notification
{
    use Queueable;

    public function __construct(
        public Restaurant $restaurant,
        public bool $approved,
        public ?string $note = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->approved) {
            return (new MailMessage)
                ->subject('Menünüz yayında — '.$this->restaurant->subdomain.'.'.config('neva.root_domain'))
                ->greeting('Tebrikler!')
                ->line('"'.$this->restaurant->name.'" menünüz artık canlıda.')
                ->action('Menüyü aç', tenant_domain($this->restaurant))
                ->line('Panelden yaptığınız her değişiklik anında bu adrese yansır.')
                ->line('QR kodunuzu panelin "QR & PDF" bölümünden indirebilirsiniz.');
        }

        return (new MailMessage)
            ->subject('Alt domain talebiniz hakkında')
            ->greeting('Merhaba,')
            ->line('"'.$this->restaurant->name.'" için alt domain talebiniz onaylanmadı.')
            ->line($this->note ? 'Gerekçe: '.$this->note : 'Detay için bizimle iletişime geçebilirsiniz.')
            ->action('Panele git', route('panel.dashboard'))
            ->line('Panelden yeni bir alt domain adı talep edebilirsiniz.');
    }
}
