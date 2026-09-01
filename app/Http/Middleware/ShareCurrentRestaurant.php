<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Panel tek kiracılıdır: giriş yapan sahibin ilk restoranını
 * container'a ("restaurant") ve tüm panel view'lerine ($restaurant) paylaşır.
 *
 * Not: slug/status kolonları Restaurant modelinde kütle atamaya kapalı olduğu
 * için oluşturma forceCreate ile yapılır.
 */
class ShareCurrentRestaurant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $restaurant = $user->restaurants()->first() ?? $this->createFor($user);

        app()->instance('restaurant', $restaurant);
        View::share('restaurant', $restaurant);

        return $next($request);
    }

    private function createFor($user): Restaurant
    {
        $base = Str::slug($user->name.' isletme') ?: 'isletme';
        $slug = $base;
        $i = 2;

        while (Restaurant::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $user->restaurants()->forceCreate([
            'name' => $user->name."'in İşletmesi",
            'slug' => $slug,
            'template' => Restaurant::DEFAULT_TEMPLATE,
            'status' => Restaurant::STATUS_DRAFT,
            'menu_version' => 1,
        ]);
    }
}
