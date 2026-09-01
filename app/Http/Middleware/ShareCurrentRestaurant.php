<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Panel tek kiracılıdır: giriş yapan sahibin ilk restoranını
 * container'a ("restaurant") ve tüm panel view'lerine ($restaurant) paylaşır.
 */
class ShareCurrentRestaurant
{
    public function handle(Request $request, Closure $next): Response
    {
        $restaurant = $request->user()?->restaurants()->firstOrCreate(
            [],
            [
                'name' => $request->user()->name."'in İşletmesi",
                'slug' => 'isletme-'.$request->user()->id,
                'template' => Restaurant::DEFAULT_TEMPLATE,
                'status' => Restaurant::STATUS_DRAFT,
            ]
        );

        if ($restaurant) {
            app()->instance('restaurant', $restaurant);
            View::share('restaurant', $restaurant);
        }

        return $next($request);
    }
}
