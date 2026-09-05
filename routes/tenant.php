<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Kiracı (alt domain) rotaları — {tenant}.{NEVA_ROOT_DOMAIN}
|--------------------------------------------------------------------------
| Bu grubun tamamı 'tenant' middleware'inden geçer; çözümlenen restoran
| container'da app('tenant') ve view'lerde $tenant olarak erişilebilir.
| Seçilen şablona göre uygun Blade iskeleti render edilir ve sonuç
| menu_version anahtarlı önbellekten servis edilir.
*/

Route::get('/', [\App\Http\Controllers\Tenant\MenuController::class, 'show'])->name('tenant.menu');

// Masadan okutulan QR bu adrese gelir; masayı işaretleyip menüye yönlendirir.
Route::get('/m/{token}', [\App\Http\Controllers\Tenant\MenuController::class, 'fromTable'])->name('tenant.menu.table');

// Kategori, ortulu route-model baglama ile DEGIL, dogrudan kiracinin kendi
// kategorileri icinde aranir: hem alan adi parametresiyle yasanan baglama
// sorununu ortadan kaldirir hem de kiraci izolasyonunu sorgu seviyesinde garanti eder.
Route::get('/kategori/{slug}', [\App\Http\Controllers\Tenant\MenuController::class, 'category'])->name('tenant.category');

// Menü görselleri — ana domaindekiyle AYNI uç; kiracı sayfasındaki host'suz
// yollar (/gorsel/...) alt domainde de çözülsün diye burada da kayıtlı.
Route::get('/gorsel/{path}', \App\Http\Controllers\MediaController::class)
    ->where('path', '.*')
    ->name('tenant.media');

// Görüntülenme sayacı — sayfa önbellekten geldiği için ölçüm istemciden atılır.
Route::get('/olcum', \App\Http\Controllers\Tenant\TrackController::class)
    ->middleware('throttle:track')
    ->name('tenant.track');

// SEO — her kiracının kendi sitemap'i ve robots.txt'i
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'tenant'])->name('tenant.sitemap');

Route::get('/robots.txt', function () {
    $allow = config('neva.seo.index_tenants', true);

    $body = $allow
        ? "User-agent: *\nAllow: /\nSitemap: ".tenant_domain(app('tenant'), '/sitemap.xml')."\n"
        : "User-agent: *\nDisallow: /\n";

    return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('tenant.robots');
