<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Support\ImageProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DesignController extends Controller
{
    /** template_settings altında şablona özel saklanan alanlar. */
    private const TPL_SETTING_KEYS = [
        'accent_color', 'font_family', 'bg_color', 'heading_color', 'text_color',
        'bg_pattern', 'entry_anim', 'image_style', 'corner_radius', 'heading_weight', 'text_size',
    ];

    public function edit(Request $request): View
    {
        /** @var Restaurant $restaurant */
        $restaurant = app('restaurant');

        $all = config('neva.templates');
        $visual = (array) config('neva.template_focus.visual', []);
        $typographic = (array) config('neva.template_focus.typographic', []);
        $order = (array) config('neva.template_order', []);

        // KESKİN sınıflandırma: her şablon TAM OLARAK bir listede olmalı — çakışma/eksik = config hatası.
        $focusOf = function (string $k) use ($visual, $typographic) {
            $v = in_array($k, $visual, true);
            $t = in_array($k, $typographic, true);
            abort_if($v === $t, 500, "Şablon '$k' galeri odak sınıflandırmasında eksik veya çift kayıtlı (config/neva.php › template_focus).");

            return $v ? 'visual' : 'typographic';
        };

        // Galeri sırası: curated 'template_order' önce, kalanlar arkada. Her şablona keskin focus etiketi.
        $ordered = collect($order)
            ->merge(array_keys($all))
            ->unique()
            ->filter(fn ($k) => isset($all[$k]))
            ->mapWithKeys(fn ($k) => [$k => $all[$k] + ['focus' => $focusOf($k)]])
            ->all();

        return view('panel.design', [
            'restaurant' => $restaurant,
            'templates' => $ordered,
            'fonts' => config('neva.fonts'),
            'logoSizes' => config('neva.logo_sizes'),
            'variations' => config('neva.variations'),
            'startMode' => in_array($request->query('mode'), ['edit', 'browse'], true)
                ? ($request->query('mode') === 'edit' ? 'edit' : 'gallery')
                : 'gallery',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var Restaurant $restaurant */
        $restaurant = app('restaurant');

        $vOpt = fn (string $k) => Rule::in(array_keys(config("neva.variations.$k.options")));

        $validated = $request->validate([
            'template' => ['required', Rule::in(Restaurant::templateKeys())],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'bg_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'heading_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font_family' => ['required', Rule::in(array_keys(config('neva.fonts')))],
            'logo_size' => ['required', Rule::in(array_keys(config('neva.logo_sizes')))],
            'currency' => ['required', 'in:TRY,USD,EUR,GBP'],
            'show_prices' => ['boolean'],
            'show_calories' => ['boolean'],
            'bg_pattern' => ['nullable', $vOpt('bg_pattern')],
            'entry_anim' => ['nullable', $vOpt('entry_anim')],
            'image_style' => ['nullable', $vOpt('image_style')],
            'corner_radius' => ['nullable', $vOpt('corner_radius')],
            'heading_weight' => ['nullable', $vOpt('heading_weight')],
            'text_size' => ['nullable', $vOpt('text_size')],
            'cover' => ['nullable', 'image', 'max:'.config('neva.uploads.image_max_kb')],
            'remove_cover' => ['boolean'],
        ]);

        $template = $validated['template'];
        $locks = (array) config("neva.templates.$template.locks", []);
        $disk = config('neva.uploads.disk');
        $coverSupported = (bool) config("neva.templates.$template.cover");

        // Kapak görseli yalnızca destekleyen şablonlarda; marka logosu artık İşletme Profili'nde.
        if ($coverSupported) {
            if ($request->boolean('remove_cover') && $restaurant->cover_path) {
                Storage::disk($disk)->delete($restaurant->cover_path);
                $validated['cover_path'] = null;
            }
            if ($request->hasFile('cover')) {
                if ($restaurant->cover_path) {
                    Storage::disk($disk)->delete($restaurant->cover_path);
                }
                $validated['cover_path'] = ImageProcessor::store(
                    $request->file('cover'),
                    "restaurants/{$restaurant->id}/brand",
                    $disk,
                    config('neva.uploads.max_edge.cover'),
                );
            }
        }

        $validated['show_prices'] = $request->boolean('show_prices');
        $validated['show_calories'] = $request->boolean('show_calories');

        // --- Şablona özel "Gelişmiş Dokunuşlar" → template_settings[<template>] ---
        $bucket = [];
        foreach (self::TPL_SETTING_KEYS as $key) {
            $value = $validated[$key] ?? null;
            // Şablonun desteklemediği kontrol → hiç kaydetme.
            if (in_array($key, $locks, true)) {
                continue;
            }
            // Boş / 'auto' gibi nötr değerleri saklamaya gerek yok.
            if ($value === null || $value === '' || $value === 'auto') {
                continue;
            }
            $bucket[$key] = $value;
        }

        $allSettings = $restaurant->template_settings ?? [];
        if ($bucket) {
            $allSettings[$template] = $bucket;
        } else {
            unset($allSettings[$template]);
        }
        $validated['template_settings'] = $allSettings ?: null;

        // Şablona özel varyasyon anahtarları düz sütunlara YAZILMAZ (sütunları yok / kullanılmıyor).
        foreach (['bg_color', 'heading_color', 'text_color', 'bg_pattern', 'entry_anim', 'image_style', 'corner_radius', 'heading_weight', 'text_size'] as $k) {
            unset($validated[$k]);
        }
        unset($validated['cover'], $validated['remove_cover']);

        $restaurant->update($validated);

        return back()->with('success', 'Tasarım kaydedildi. Canlı menünüz güncellendi.');
    }
}
