<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\Panel;
use App\Http\Controllers\ShowcaseController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ana domain rotaları (NEVA_ROOT_DOMAIN)
|--------------------------------------------------------------------------
| Pazarlama sitesi, kimlik doğrulama, sahip paneli ve admin paneli.
| Kiracıya özel (alt domain) rotalar routes/tenant.php içindedir.
|
| Paket kontrolü: ->middleware('plan:<yetenek>') — matris config/neva.php
| › plan_features. View'lerdeki @if'ler sadece görsel; gerçek engel burada.
*/

Route::get('/', [MarketingController::class, 'home'])->name('home');
Route::get('/fiyatlandirma', [MarketingController::class, 'pricing'])->name('pricing');
Route::get('/hakkimizda', [MarketingController::class, 'about'])->name('about');

/*
| Şablon vitrini — 40 tasarımın her biri kendi indekslenebilir adresinde.
| Adresler Türkçe ve anahtar kelime taşır; değiştirmeyin (bağlantılar kırılır,
| biriken sıralama sıfırlanır). Zorunlu olursa 301 yönlendirme bırakın.
*/
Route::get('/qr-menu-sablonlari', [ShowcaseController::class, 'index'])->name('showcase.index');
Route::get('/qr-menu-sablonlari/{template}', [ShowcaseController::class, 'show'])
    ->where('template', '[a-z0-9-]+')
    ->name('showcase.show');

// iframe'e gömülen ham şablon render'ı (X-Robots-Tag: noindex).
Route::get('/sablon-onizleme/{template}', [ShowcaseController::class, 'preview'])
    ->where('template', '[a-z0-9-]+')
    ->name('showcase.preview');

Route::get('/sikca-sorulan-sorular', [ShowcaseController::class, 'faq'])->name('faq');

Route::get('/iletisim', [MarketingController::class, 'contact'])->name('contact');
Route::post('/iletisim', [MarketingController::class, 'contact'])
    ->middleware('throttle:contact')
    ->name('contact.store');

// Yüklenen görseller — özel diskten yetki kontrolüyle servis edilir.
Route::get('/gorsel/{path}', MediaController::class)
    ->where('path', '.*')
    ->name('media');

// --- Hukuki metinler (KVKK) ---
Route::get('/gizlilik-politikasi', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/kvkk-aydinlatma-metni', [LegalController::class, 'kvkk'])->name('legal.kvkk');
Route::get('/cerez-politikasi', [LegalController::class, 'cookies'])->name('legal.cookies');
Route::get('/kullanim-kosullari', [LegalController::class, 'terms'])->name('legal.terms');
Route::post('/cerez-bildirimi', [LegalController::class, 'acceptCookies'])->name('legal.cookies.accept');

// SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

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
        Route::get('/onizleme', [Panel\PreviewController::class, 'show'])
            ->middleware('throttle:preview')
            ->name('preview');

        // Menü içeriği
        Route::resource('kategoriler', Panel\CategoryController::class)
            ->parameters(['kategoriler' => 'category'])
            ->names('categories');
        Route::post('/kategoriler/sirala', [Panel\CategoryController::class, 'reorder'])->name('categories.reorder');

        Route::resource('urunler', Panel\ProductController::class)
            ->parameters(['urunler' => 'product'])
            ->names('products');
        Route::post('/urunler/sirala', [Panel\ProductController::class, 'reorder'])->name('products.reorder');

        // Menü istatistikleri (görüntülenme / masa kırılımı)
        Route::get('/istatistik', Panel\StatsController::class)->name('stats');

        // QR & PDF
        Route::middleware('throttle:render')->group(function () {
            Route::get('/qr', [Panel\QrController::class, 'index'])->name('qr.index');

            // Alt domaine yönlenen ana QR — yalnızca hosting dahil paketler
            Route::middleware('plan:main_qr')->group(function () {
                Route::get('/qr/ana.png', [Panel\QrController::class, 'main'])->name('qr.main');
                Route::get('/qr/masa.png', [Panel\QrController::class, 'param'])->name('qr.param');
            });

            // "Hosting Hariç" paketi — dış menü linkine özel statik QR
            Route::middleware('plan:external_qr')->group(function () {
                Route::put('/qr/dis-link', [Panel\QrController::class, 'externalUpdate'])->name('qr.external.update');
                Route::get('/qr/dis-link.png', [Panel\QrController::class, 'externalPng'])->name('qr.external.png');
            });

            // QR tasarımı (10 stil)
            Route::put('/qr/tasarim', [Panel\QrController::class, 'designUpdate'])->name('qr.design.update');
            Route::get('/qr/tasarim-onizleme.png', [Panel\QrController::class, 'designPreview'])->name('qr.design.preview');

            // Masa yönetimi — paketinde masa olmayan kullanıcı erişemez
            Route::middleware('plan:tables')->group(function () {
                Route::post('/qr/masa', [Panel\QrController::class, 'store'])->name('qr.store');
                Route::delete('/qr/masa/{table}', [Panel\QrController::class, 'destroy'])->name('qr.destroy');
                Route::get('/qr/masa/{table}/qr.png', [Panel\QrController::class, 'png'])->name('qr.png');
            });

            // PDF headless Chrome başlatır — 'render' sınırının üstüne kendi
            // sıkı sınırı gelir (bkz. AppServiceProvider › RateLimiter 'pdf').
            Route::get('/menu.pdf', [Panel\MenuPdfController::class, 'download'])
                ->middleware(['plan:pdf', 'throttle:pdf'])
                ->name('menu.pdf');
        });

        // Alt domain talebi + onaya gönderme — yalnızca alt domain hakkı olan paketler
        Route::middleware('plan:subdomain')->group(function () {
            Route::get('/alt-domain/musaitlik', [Panel\SubdomainRequestController::class, 'availability'])
                ->middleware('throttle:subdomain-check')
                ->name('subdomain.availability');

            Route::post('/alt-domain', [Panel\SubdomainRequestController::class, 'store'])
                ->middleware('throttle:subdomain-request')
                ->name('subdomain.store');

            Route::post('/onaya-gonder', [Panel\SubmissionController::class, 'store'])->name('submit');
        });

        // Destek mesajlaşması (chatbox)
        Route::get('/mesajlar', [Panel\MessageController::class, 'index'])->name('messages.index');
        Route::post('/mesajlar', [Panel\MessageController::class, 'store'])
            ->middleware('throttle:messages')
            ->name('messages.store');
        Route::get('/mesajlar/{conversation}', [Panel\MessageController::class, 'show'])->name('messages.show');
        Route::post('/mesajlar/{conversation}/yanit', [Panel\MessageController::class, 'reply'])
            ->middleware('throttle:messages')
            ->name('messages.reply');
        Route::get('/mesajlar/{conversation}/yeni', [Panel\MessageController::class, 'poll'])->name('messages.poll');
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

        // Kayıt / üyelik talepleri (havale akışı)
        Route::get('/uyelik-talepleri', [Admin\MembershipRequestController::class, 'index'])->name('memberships.index');
        Route::post('/uyelik-talepleri/{membershipRequest}/odeme', [Admin\MembershipRequestController::class, 'togglePayment'])->name('memberships.payment');
        Route::post('/uyelik-talepleri/{membershipRequest}/onayla', [Admin\MembershipRequestController::class, 'approve'])->name('memberships.approve');
        Route::post('/uyelik-talepleri/{membershipRequest}/reddet', [Admin\MembershipRequestController::class, 'reject'])->name('memberships.reject');

        // Destek mesajları
        Route::get('/mesajlar', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::get('/mesajlar/{conversation}', [Admin\MessageController::class, 'show'])->name('messages.show');
        Route::post('/mesajlar/{conversation}/yanit', [Admin\MessageController::class, 'reply'])
            ->middleware('throttle:messages')
            ->name('messages.reply');
        Route::post('/mesajlar/{conversation}/durum', [Admin\MessageController::class, 'toggleStatus'])->name('messages.status');
        Route::get('/mesajlar/{conversation}/yeni', [Admin\MessageController::class, 'poll'])->name('messages.poll');

        // Ziyaretçi iletişim formu kayıtları
        Route::get('/iletisim', [Admin\ContactMessageController::class, 'index'])->name('contact.index');
        Route::post('/iletisim/{contactMessage}', [Admin\ContactMessageController::class, 'update'])->name('contact.update');

        // Kullanıcılar + elle hesap oluşturma + şifre belirleme bağlantısı.
        // Hassas ekran: admin şifresini yeniden onaylamadan açılmaz (3 saat geçerli).
        Route::get('/kullanicilar', [Admin\UserController::class, 'index'])
            ->middleware('password.confirm')
            ->name('users.index');
        Route::post('/kullanicilar', [Admin\UserController::class, 'store'])->name('users.store');
        Route::post('/kullanicilar/{user}/sifre-sifirla', [Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
    });

require __DIR__.'/auth.php';
