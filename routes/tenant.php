<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Kiracı (alt domain) rotaları — {tenant}.neva-qr.com
|--------------------------------------------------------------------------
| Bu grubun tamamı 'tenant' middleware'inden geçer; çözümlenen restoran
| container'da app('tenant') ve view'lerde $tenant olarak erişilebilir.
| Seçilen şablona göre (cafe | restaurant) uygun Blade görünümü render edilir.
*/

Route::get('/', [\App\Http\Controllers\Tenant\MenuController::class, 'show'])->name('tenant.menu');

// Masadan okutulan QR bu adrese gelir; masayı işaretleyip menüye yönlendirir.
Route::get('/m/{token}', [\App\Http\Controllers\Tenant\MenuController::class, 'fromTable'])->name('tenant.menu.table');

Route::get('/kategori/{category:slug}', [\App\Http\Controllers\Tenant\MenuController::class, 'category'])->name('tenant.category');
