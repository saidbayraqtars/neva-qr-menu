<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use App\Services\PlanGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rota bazlı paket kontrolü:  ->middleware('plan:subdomain')
 *
 * Birden çok yetenek verilirse HEPSİ gerekir: 'plan:subdomain,tables'.
 * 'restaurant.context' middleware'inden SONRA çalışmalıdır.
 */
class EnsurePlanFeature
{
    public function __construct(private readonly PlanGate $gate) {}

    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        /** @var Restaurant|null $restaurant */
        $restaurant = app()->bound('restaurant') ? app('restaurant') : null;

        abort_if($restaurant === null, 403);

        foreach ($features as $feature) {
            $this->gate->authorize($restaurant, $feature);
        }

        return $next($request);
    }
}
