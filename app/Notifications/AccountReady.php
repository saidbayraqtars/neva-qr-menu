<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Hesap açıldı — kullanıcı şifresini KENDİSİ belirler.
 * Link tek kullanımlıktır ve 72 saat geçerlidir.
 */
class AccountReady extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        /** @var User $notifiable */
        $url = route('password.setup', ['token' => $this->token, 'email' => $notifiable->email]);

        return (new MailMessage)
            ->subject(config('neva.brand.name').' — hesabınız hazır')
            ->greeting('Merhaba '.$notifiable->name.',')
            ->line('QR menü paneliniz kullanıma hazır. Güvenliğiniz için şifrenizi siz belirliyorsunuz.')
            ->action('Şifremi belirle', $url)
            ->line('Bu bağlantı '.User::SETUP_TOKEN_HOURS.' saat geçerlidir ve yalnızca bir kez kullanılabilir.')
            ->line('Bağlantının süresi dolarsa giriş ekranındaki "Şifremi unuttum" ile yenisini isteyebilirsiniz.');
    }
}
