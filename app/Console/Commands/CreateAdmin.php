<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AccountReady;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use function Laravel\Prompts\text;

/**
 * İlk platform yöneticisini açar.
 *
 * NEDEN GEREKLİ: kayıt akışı bir üyelik talebi üretiyor ve talebi onaylayacak
 * bir admin gerekiyor — taze bir üretim veritabanında kısır döngü. Admin
 * hesabı açmanın tek yolu `DemoSeeder` idi, o da sabit `password` şifresiyle
 * sahte demo restoranlar oluşturuyor; üretimde çalıştırılamaz.
 *
 * Projenin şifre ilkesine uyar: düz metin şifre üretilmez, saklanmaz ve
 * gösterilmez. Kullanıcı şifresini tek kullanımlık bir bağlantıyla kendisi
 * belirler. SMTP henüz kurulmamışsa bağlantı konsola basılır — ilk kurulumda
 * e-posta çalışmadan da hesaba girilebilsin diye.
 */
class CreateAdmin extends Command
{
    protected $signature = 'neva:admin-olustur
        {--email= : Yöneticinin e-posta adresi}
        {--ad= : Görünen ad}
        {--link-goster : Şifre belirleme bağlantısını konsola bas (SMTP yoksa)}';

    protected $description = 'Platform yöneticisi hesabı açar ve şifre belirleme bağlantısı gönderir';

    public function handle(): int
    {
        $email = Str::lower(trim((string) ($this->option('email') ?: text(
            label: 'Yönetici e-posta adresi',
            required: true,
        ))));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->components->error("Geçersiz e-posta: {$email}");

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();

        // Var olan bir OWNER'ı admin'e yükseltmeyi reddediyoruz: o kullanıcının
        // restoranı, aboneliği ve menüsü var; rolü değişince paket matrisi ve
        // panel yönlendirmesi tutarsız hale gelir.
        if ($existing && ! $existing->isAdmin()) {
            $this->components->error("Bu e-posta zaten bir işletme hesabına ait: {$email}");
            $this->line('  Yönetici için ayrı bir e-posta kullanın.');

            return self::FAILURE;
        }

        if ($existing) {
            $this->components->warn("Bu yönetici zaten var — yeni bir şifre belirleme bağlantısı üretiliyor.");
            $user = $existing;
        } else {
            $name = trim((string) ($this->option('ad') ?: text(
                label: 'Görünen ad',
                default: 'Platform Yöneticisi',
                required: true,
            )));

            $user = new User;
            $user->forceFill([
                'name' => $name,
                'email' => $email,
                'role' => User::ROLE_ADMIN,
                // Kullanılamaz rastgele şifre: giriş yalnızca belirleme
                // bağlantısıyla açılır, düz metin hiçbir yerde bulunmaz.
                'password' => Hash::make(Str::random(64)),
                'email_verified_at' => now(),
                'must_change_password' => true,
            ])->save();

            $this->components->info("Yönetici oluşturuldu: {$email}");
        }

        $token = $user->issuePasswordSetupToken();
        $url = route('password.setup', ['token' => $token, 'email' => $user->email]);

        $mailer = (string) config('mail.default');
        $mailWorks = ! in_array($mailer, ['log', 'array'], true);

        if ($mailWorks) {
            try {
                $user->notify(new AccountReady($token));
                $this->components->info("Şifre belirleme bağlantısı {$email} adresine gönderildi.");
            } catch (\Throwable $e) {
                report($e);
                $this->components->error('E-posta gönderilemedi: '.$e->getMessage());
                $this->showLink($url);

                return self::SUCCESS;
            }
        } else {
            $this->components->warn("E-posta sürücüsü '{$mailer}' — posta gönderilmedi.");
        }

        if (! $mailWorks || $this->option('link-goster')) {
            $this->showLink($url);
        }

        $this->newLine();
        $this->line('  Bağlantı '.User::SETUP_TOKEN_HOURS.' saat geçerli ve tek kullanımlık.');

        return self::SUCCESS;
    }

    private function showLink(string $url): void
    {
        $this->newLine();
        $this->line('  <fg=yellow>Şifre belirleme bağlantısı:</>');
        $this->line("  <fg=cyan>{$url}</>");
        $this->newLine();
        $this->line('  <fg=gray>Bu bağlantı hesabın tam yetkisini verir — paylaşmayın,</>');
        $this->line('  <fg=gray>kullandıktan sonra terminal geçmişinizden silin.</>');
    }
}
