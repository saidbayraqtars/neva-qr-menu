<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var Restaurant $restaurant */
        $restaurant = app('restaurant')->loadCount(['categories', 'products', 'tables']);

        $pending = $restaurant->subdomainRequests()->where('status', 'pending')->latest()->first();

        return view('panel.dashboard', [
            'restaurant' => $restaurant,
            'pending' => $pending,
            'selfHosted' => $restaurant->isSelfHosted(),
        ]);
    }
}
