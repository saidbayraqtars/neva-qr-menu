<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Services\QrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QrController extends Controller
{
    public function index(): View
    {
        $restaurant = app('restaurant');
        $selfHosted = $restaurant->isSelfHosted();

        return view('panel.qr.index', [
            'restaurant' => $restaurant,
            'selfHosted' => $selfHosted,
            'tables' => $selfHosted ? collect() : $restaurant->tables()->orderBy('sort_order')->get(),
            'mainUrl' => $selfHosted ? null : tenant_domain($restaurant),
            'qrDesigns' => config('neva.qr_designs'),
        ]);
    }

    /** QR tasarımı seçimi kaydedilir — tüm QR çıktıları bunu kullanır. */
    public function designUpdate(Request $request, QrService $qr): RedirectResponse
    {
        $validated = $request->validate([
            'qr_design' => ['required', Rule::in(array_keys(config('neva.qr_designs')))],
        ]);

        $restaurant = app('restaurant');
        $restaurant->update(['qr_design' => $validated['qr_design']]);

        // Diske yazılmış ana QR'ı da yenile (yayındaysa).
        if ($restaurant->status === Restaurant::STATUS_APPROVED && filled($restaurant->subdomain)) {
            try {
                $qr->storeMainForRestaurant($restaurant->fresh());
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', 'QR tasarımı güncellendi.');
    }

    /** Bir tasarımın canlı önizlemesi (küçük PNG) — kaydırarak seçim için. */
    public function designPreview(Request $request, QrService $qr): Response
    {
        $design = (string) $request->query('design', 'fircadarbe');
        abort_unless(array_key_exists($design, config('neva.qr_designs')), 404);

        $restaurant = app('restaurant');
        $sample = $restaurant->isSelfHosted()
            ? ($restaurant->external_menu_url ?: 'https://'.($restaurant->slug ?: 'menu').'.neva-qr.com')
            : tenant_domain($restaurant);

        return response($qr->preview($sample, $design, $restaurant, 420), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store',
        ]);
    }

    /** "Hosting Hariç" paketi — kullanıcının dış menü adresini kaydeder. */
    public function externalUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'external_menu_url' => ['required', 'url', 'max:2048'],
        ], [
            'external_menu_url.url' => 'Geçerli bir web adresi girin — örn. https://menu.isletmeniz.com',
        ]);

        app('restaurant')->update(['external_menu_url' => $validated['external_menu_url']]);

        return back()->with('success', 'Menü adresiniz kaydedildi — QR kodunuz hazır.');
    }

    /** Dış menü adresine yönlenen bağımsız statik QR (PNG). */
    public function externalPng(QrService $qr): Response
    {
        $restaurant = app('restaurant');
        $url = trim((string) $restaurant->external_menu_url);
        abort_if($url === '', 404);

        return response($qr->forExternalUrl($url, $restaurant), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.str($restaurant->name)->slug().'-qr.png"',
        ]);
    }

    /** Ana işletme QR (PNG) — alt domain köküne yönlenir. */
    public function main(QrService $qr): Response
    {
        $restaurant = app('restaurant');

        return response($qr->forRestaurant($restaurant), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.str($restaurant->name)->slug().'-ana-qr.png"',
        ]);
    }

    /** `?masa=<etiket>` QR (PNG) — hızlı masa linkleri için. */
    public function param(Request $request, QrService $qr): Response
    {
        $masa = trim((string) $request->string('masa'));
        abort_if($masa === '', 404);

        $restaurant = app('restaurant');

        return response($qr->forParam($restaurant, $masa), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="qr-masa-'.str($masa)->slug().'.png"',
        ]);
    }

    /* ---- Opsiyonel: adlandırılmış masa listesi (tarama sayısı takibi için) ---- */

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'count' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $restaurant = app('restaurant');
        $count = $validated['count'] ?? 1;
        $start = (int) $restaurant->tables()->max('sort_order');

        for ($i = 1; $i <= $count; $i++) {
            $restaurant->tables()->create([
                'label' => $count > 1 ? "{$validated['label']} {$i}" : $validated['label'],
                'sort_order' => $start + $i,
            ]);
        }

        return back()->with('success', $count > 1 ? "{$count} masa eklendi." : 'Masa eklendi.');
    }

    public function destroy(RestaurantTable $table): RedirectResponse
    {
        abort_unless($table->restaurant_id === app('restaurant')->id, 403);

        $table->delete();

        return back()->with('success', 'Masa silindi.');
    }

    public function png(RestaurantTable $table, QrService $qr): Response
    {
        abort_unless($table->restaurant_id === app('restaurant')->id, 403);

        return response($qr->forTable($table), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="qr-'.str($table->label)->slug().'.png"',
        ]);
    }
}
