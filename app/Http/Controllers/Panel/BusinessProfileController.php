<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Support\ImageProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * İşletme Profili — markanın MERKEZÎ kimlik bilgileri.
 * Bu alanlar şablondan bağımsızdır: hangi şablon seçilirse seçilsin
 * TemplatePresenter üzerinden header/footer/logo alanlarına otomatik akar.
 * (Tasarım stüdyosu yalnızca "nasıl göründüğü" ile ilgilenir.)
 */
class BusinessProfileController extends Controller
{
    public function edit(): View
    {
        return view('panel.business', [
            'restaurant' => app('restaurant'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var Restaurant $restaurant */
        $restaurant = app('restaurant');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:80'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:'.config('neva.uploads.logo_max_kb')],
            'remove_logo' => ['boolean'],
        ]);

        $disk = config('neva.uploads.disk');

        if ($request->boolean('remove_logo') && $restaurant->logo_path) {
            Storage::disk($disk)->delete($restaurant->logo_path);
            $validated['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($restaurant->logo_path) {
                Storage::disk($disk)->delete($restaurant->logo_path);
            }
            // Logo PNG olarak saklanır: şeffaf zemin şablonların çoğunda gerekli.
            $validated['logo_path'] = ImageProcessor::store(
                $request->file('logo'),
                "restaurants/{$restaurant->id}/brand",
                $disk,
                config('neva.uploads.max_edge.logo'),
                transparency: true,
            );
        }

        unset($validated['logo'], $validated['remove_logo']);

        $restaurant->update($validated);

        return back()->with('success', 'İşletme profili güncellendi. Tüm şablonlara anında yansıdı.');
    }
}
