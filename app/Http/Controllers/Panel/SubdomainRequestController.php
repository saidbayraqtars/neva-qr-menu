<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Services\SubdomainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubdomainRequestController extends Controller
{
    /**
     * ANLIK müsaitlik kontrolü — kullanıcı yazarken çağrılır (debounce'lu).
     * İstisna fırlatmaz; durum + insan diliyle mesaj döner.
     */
    public function availability(Request $request, SubdomainService $service): JsonResponse
    {
        /** @var Restaurant $restaurant */
        $restaurant = app('restaurant');

        $raw = (string) $request->query('label', '');

        if (trim($raw) === '') {
            return response()->json([
                'status' => 'empty',
                'available' => false,
                'label' => '',
                'message' => '',
            ]);
        }

        return response()->json(
            $service->availability($raw, $restaurant->id)
        );
    }

    public function store(Request $request, SubdomainService $service): RedirectResponse
    {
        $request->validate([
            'requested_subdomain' => ['required', 'string', 'max:40'],
        ]);

        /** @var Restaurant $restaurant */
        $restaurant = app('restaurant');

        // Zaten onaya gönderilmiş veya yayında ise yeni talep alınmaz.
        if ($restaurant->isAwaitingApproval() || $restaurant->isLive()) {
            return back()->with('error', 'Talebiniz zaten işleme alınmış. Yeni bir alt domain için bizimle iletişime geçin.');
        }

        $subdomainRequest = $service->request($restaurant, (string) $request->string('requested_subdomain'));

        return back()->with('success', "\u{201C}{$subdomainRequest->requested_subdomain}.".config('neva.root_domain')."\u{201D} adresi sizin için ayrıldı. \u{201C}Onaya Gönder\u{201D} ile yayına alınmak üzere iletebilirsiniz.");
    }
}
