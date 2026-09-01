<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\SubdomainService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubdomainRequestController extends Controller
{
    public function store(Request $request, SubdomainService $service): RedirectResponse
    {
        $validated = $request->validate([
            'requested_subdomain' => ['required', 'string', 'max:40'],
        ]);

        $service->request(app('restaurant'), $validated['requested_subdomain']);

        return back()->with('success', 'Alt domain talebiniz kaydedildi. “Onaya Gönder” ile yayına alınmak üzere iletebilirsiniz.');
    }
}
