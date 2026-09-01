<?php

use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\EnsurePlanFeature;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\ShareCurrentRestaurant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            // Kiracı alt domain kökü NEVA_ROOT_DOMAIN'den gelir (APP_URL host'undan DEĞİL).
            // Yerelde "localhost" -> lumina.localhost ; üretimde "neva-qr.com" -> lumina.neva-qr.com
            $root = config('neva.root_domain') ?: 'localhost';

            // Kiracı rotaları ÖNCE kaydedilir ki ana domain rotalarından öncelikli olsun.
            Route::middleware(['web', 'tenant'])
                ->domain('{tenant}.'.$root)
                ->group(base_path('routes/tenant.php'));

            Route::middleware('web')->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Güvenlik başlıkları tüm web yanıtlarına eklenir (CSP, HSTS, nosniff...).
        $middleware->appendToGroup('web', SecurityHeaders::class);

        $middleware->alias([
            'tenant' => ResolveTenant::class,
            'admin' => EnsureUserIsAdmin::class,
            'restaurant.context' => ShareCurrentRestaurant::class,
            'password.changed' => EnsurePasswordChanged::class,
            'plan' => EnsurePlanFeature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
