<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\SubdomainRequest;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'pending' => SubdomainRequest::pending()->count(),
                'live' => Restaurant::live()->count(),
                'owners' => User::where('role', User::ROLE_OWNER)->count(),
                'drafts' => Restaurant::where('status', Restaurant::STATUS_DRAFT)->count(),
            ],
            'recent' => SubdomainRequest::with('restaurant.owner')->latest()->limit(8)->get(),
        ]);
    }
}
