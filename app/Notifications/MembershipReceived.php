<?php

namespace App\Notifications;

use App\Models\MembershipRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Başvuru alındı — havale/EFT talimatı ve referans kodu.
 * Ödeme geldiğinde admin onaylar, hesap açılır.
 */
class MembershipReceived extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public MembershipRequest $membershipRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $req = $this->membershipRequest;
        $accounts = \App\Support\PaymentAccounts::active();

        $mail = (new MailMessage)
            ->subject(config('neva.brand.name').' — başvurunuz alındı')
            ->greeting('Merhaba '.$req->name.',')
            ->line('"'.$req->business_name.'" için başvurunuzu aldık.')
            ->line('**Paket:** '.($req->plan?->name ?? '—'))
            ->line('**Tutar:** '.money($req->amount))
            ->line('**Referans kodu:** '.$req->reference_code)
            ->line($accounts->count() > 1
                ? 'Ödemenizi aşağıdaki hesaplardan **herhangi birine** havale/EFT ile yapabilirsiniz. Açıklama kısmına referans kodunuzu yazmayı unutmayın.'
                : 'Ödemenizi aşağıdaki hesaba havale/EFT ile yapabilirsiniz. Açıklama kısmına referans kodunuzu yazmayı unutmayın.');

        foreach ($accounts as $account) {
            $mail->line('---')
                ->line('**Banka:** '.$account->bank_name.($account->note ? ' ('.$account->note.')' : ''))
                ->line('**Alıcı:** '.$account->account_name)
                ->line('**IBAN:** '.$account->formatted_iban);
        }

        if ($accounts->isEmpty()) {
            // Hesap tanımlı değilse müşteriyi boş bir talimatla baş başa bırakma.
            $mail->line('Ödeme bilgileri kısa süre içinde ayrıca iletilecektir.');
        }

        return $mail->line('Ödemeniz hesabımıza geçtiğinde hesabınızı açıp şifre belirleme bağlantısını göndereceğiz.');
    }
}
