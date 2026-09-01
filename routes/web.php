<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Panel;
use App\Http\Controllers\MarketingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ana domain rotaları (neva-qr.com)
|--------------------------------------------------------------------------
| Pazarlama sitesi, kimlik doğrulama, sahip paneli ve admin paneli.
| Kiracıya özel (alt domain) rotalar routes/tenant.php içindedir.
*/

Route::get('/', [MarketingController::class, 'home'])->name('home');
Route::get('/fiyatlandirma', [MarketingController::class, 'pricing'])->name('pricing');
Route::get('/hakkimizda', [MarketingController::class, 'about'])->name('about');
Route::match(['get', 'post'], '/iletisim', [MarketingController::class, 'contact'])->name('contact');

// --- Sahip (restoran) paneli ---
Route::middleware(['auth', 'password.changed', 'restaurant.context'])
    ->prefix('panel')
    ->name('panel.')
    ->group(function () {
        Route::get('/', Panel\DashboardController::class)->name('dashboard');

        // Merkezî işletme kimliği (şablondan bağımsız — her şablona otomatik akar)
        Route::get('/isletme', [Panel\BusinessProfileController::class, 'edit'])->name('business.edit');
        Route::put('/isletme', [Panel\BusinessProfileController::class, 'update'])->name('business.update');

        // Şablon seçimi + görünüm + canlı önizleme (tek ekran)
        Route::get('/tasarim', [Panel\DesignController::class, 'edit'])->name('design.edit');
        Route::put('/tasarim', [Panel\DesignController::class, 'update'])->name('design.update');
        Route::get('/onizleme', [Panel\PreviewController::class, 'show'])->name('preview');

        // Menü içeriği
        Route::resource('kategoriler', Panel\CategoryController::class)
            ->parameters(['kategoriler' => 'category'])
            ->names('categories');
        Route::post('/kategoriler/sirala', [Panel\CategoryController::class, 'reorder'])->name('categories.reorder');

        Route::resource('urunler', Panel\ProductController::class)
            ->parameters(['urunler' => 'product'])
            ->names('products');

        // QR & PDF — tek ana QR + hızlı masa linkleri
        Route::get('/qr', [Panel\QrController::class, 'index'])->name('qr.index');
        Route::get('/qr/ana.png', [Panel\QrController::class, 'main'])->name('qr.main');
        Route::get('/qr/masa.png', [Panel\QrController::class, 'param'])->name('qr.param');
        // "Hosting Hariç" paketi — dış menü linkine özel statik QR
        Route::put('/qr/dis-link', [Panel\QrController::class, 'externalUpdate'])->name('qr.external.update');
        Route::get('/qr/dis-link.png', [Panel\QrController::class, 'externalPng'])->name('qr.external.png');
        // QR tasarımı (10 stil) — kaydırarak seç
        Route::put('/qr/tasarim', [Panel\QrController::class, 'designUpdate'])->name('qr.design.update');
        Route::get('/qr/tasarim-onizleme.png', [Panel\QrController::class, 'designPreview'])->name('qr.design.preview');
        Route::post('/qr/masa', [Panel\QrController::class, 'store'])->name('qr.store');
        Route::delete('/qr/masa/{table}', [Panel\QrController::class, 'destroy'])->name('qr.destroy');
        Route::get('/qr/masa/{table}/qr.png', [Panel\QrController::class, 'png'])->name('qr.png');
        Route::get('/menu.pdf', [Panel\MenuPdfController::class, 'download'])->name('menu.pdf');

        // Alt domain talebi + onaya gönderme
        Route::post('/alt-domain', [Panel\SubdomainRequestController::class, 'store'])->name('subdomain.store');
        Route::post('/onaya-gonder', [Panel\SubmissionController::class, 'store'])->name('submit');
    });

// --- Admin paneli ---
Route::middleware(['auth', 'password.changed', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        // Alt domain onay kuyruğu
        Route::get('/talepler', [Admin\SubdomainRequestController::class, 'index'])->name('requests.index');
        Route::post('/talepler/{subdomainRequest}/onayla', [Admin\SubdomainRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/talepler/{subdomainRequest}/reddet', [Admin\SubdomainRequestController::class, 'reject'])->name('requests.reject');

        // Kayıt / üyelik talepleri
        Route::get('/uyelik-talepleri', [Admin\MembershipRequestController::class, 'index'])->name('memberships.index');
        Route::post('/uyelik-talepleri/{membershipRequest}/odeme', [Admin\MembershipRequestController::class, 'togglePayment'])->name('memberships.payment');
        Route::post('/uyelik-talepleri/{membershipRequest}/onayla', [Admin\MembershipRequestController::class, 'approve'])->name('memberships.approve');
        Route::post('/uyelik-talepleri/{membershipRequest}/reddet', [Admin\MembershipRequestController::class, 'reject'])->name('memberships.reject');

        // Kullanıcılar + elle hesap oluşturma + şifre sıfırlama
        Route::get('/kullanicilar', [Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/kullanicilar', [Admin\UserController::class, 'store'])->name('users.store');
        Route::post('/kullanicilar/{user}/sifre-sifirla', [Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
    });

require __DIR__.'/auth.php';
