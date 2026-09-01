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
     * Gönderildikten sonra panelde alt domain giriş alanı tamamen gizlenir.
     */
    public function store(): RedirectResponse
    {
        /** @var Restaurant $restaurant */
        $restaurant = app('restaurant');

        if ($restaurant->isAwaitingApproval()) {
            return back()->with('error', 'Talebiniz zaten onay bekliyor.');
        }

        if ($restaurant->isLive()) {
            return back()->with('error', 'Menünüz zaten yayında.');
        }

        $pending = $restaurant->subdomainRequests()
            ->where('status', SubdomainRequest::STATUS_PENDING)
            ->latest()
            ->first();

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

        $restaurant->forceFill([
            'status' => Restaurant::STATUS_PENDING,
            'submitted_at' => now(),
        ])->save();

        return back()->with('success', 'Talebiniz admin onayına gönderildi. Onaylandığında alt domaininiz otomatik olarak yayına geçer ve size e-posta ile haber veririz.');
    }
}
