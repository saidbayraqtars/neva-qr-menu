<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\SubdomainRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class SubmissionController extends Controller
{
    /**
     * "Onaya Gönder": restoranı admin onay kuyruğuna alır.
     */
    public function store(): RedirectResponse
    {
        /** @var Restaurant $restaurant */
        $restaurant = app('restaurant');

        $pending = $restaurant->subdomainRequests()->where('status', SubdomainRequest::STATUS_PENDING)->latest()->first();

        if (! $pending && ! $restaurant->subdomain) {
            throw ValidationException::withMessages([
                'submit' => 'Önce bir alt domain adı talep edin.',
            ]);
        }

        if ($restaurant->categories()->count() === 0 || $restaurant->products()->count() < 1) {
            throw ValidationException::withMessages([
                'submit' => 'Yayına göndermeden önce en az bir kategori ve bir ürün ekleyin.',
            ]);
        }

        $restaurant->update([
            'status' => Restaurant::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        return back()->with('success', 'Talebiniz admin onayına gönderildi. Onaylandığında alt domaininiz anında yayına geçer.');
    }
}
