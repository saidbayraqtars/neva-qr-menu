<?php

namespace App\Notifications;

use App\Models\MembershipRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Başvuru alındı — havale/EFT talimatı ve referans kodu.
 * Ödeme geldiğinde admin onaylar, hesap açılır.
 */
class MembershipReceived extends Notification
{
    use Queueable;

    public function __construct(public MembershipRequest $membershipRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $bank = (array) config('neva.payment.bank');
        $req = $this->membershipRequest;

        return (new MailMessage)
            ->subject(config('neva.brand.name').' — başvurunuz alındı')
            ->greeting('Merhaba '.$req->name.',')
            ->line('"'.$req->business_name.'" için başvurunuzu aldık.')
            ->line('**Paket:** '.($req->plan?->name ?? '—'))
            ->line('**Tutar:** '.money($req->amount))
            ->line('**Referans kodu:** '.$req->reference_code)
            ->line('Ödemenizi aşağıdaki hesaba havale/EFT ile yapabilirsiniz. Açıklama kısmına referans kodunuzu yazmayı unutmayın.')
            ->line('**Alıcı:** '.($bank['account_name'] ?? '-'))
            ->line('**Banka:** '.($bank['bank_name'] ?? '-'))
            ->line('**IBAN:** '.($bank['iban'] ?? '-'))
            ->line('Ödemeniz hesabımıza geçtiğinde hesabınızı açıp şifre belirleme bağlantısını göndereceğiz.');
    }
}
