<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Zamanlanmış işler
|--------------------------------------------------------------------------
| Sunucuda tek bir cron satırı yeterlidir:
|   * * * * * cd /path/to/proje && php artisan schedule:run >> /dev/null 2>&1
*/

// Menü önbelleğini en geç 2 saatte bir tazele (soğuk sayfa olmasın).
Schedule::command('neva:warm-menus')
    ->everyTwoHours()
    ->withoutOverlapping()
    ->runInBackground();

// Yayındaki alt domainlerin çalıştığını saatte bir doğrula.
Schedule::command('neva:health-check')
    ->hourly()
    ->withoutOverlapping();

// KVKK: saklama süresi dolan kayıtları sil (iletişim formu, denetim kaydı, sayaçlar).
Schedule::command('neva:veri-temizle')
    ->dailyAt('03:20')
    ->withoutOverlapping();

// Süresi dolmuş şifre belirleme jetonlarını temizle.
Schedule::call(function () {
    User::whereNotNull('password_setup_token')
        ->where('password_setup_expires_at', '<', now())
        ->update(['password_setup_token' => null, 'password_setup_expires_at' => null]);
})->daily()->name('purge-expired-setup-tokens');

// Günlük yedek — veritabanı + yüklenen görseller + .env.
// Saat 03:00: veri temizliğinden (03:20) ÖNCE çalışır, böylece silinen
// kayıtların son hali de bir yedekte durur.
Schedule::command('neva:yedek --adet=7 --tut=30 --azami=8G')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onFailure(fn () => logger()->error('Günlük yedek alınamadı.'));

// Başarısız kuyruk işlerini ve eski oturumları temizle.
Schedule::command('queue:prune-failed --hours=168')->weekly();
