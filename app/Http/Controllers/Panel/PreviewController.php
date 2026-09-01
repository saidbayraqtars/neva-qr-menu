<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Support\TemplatePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Tasarım stüdyosundaki iframe önizlemeleri (galeri mini kartları + düzenleme
 * ekranı). Gerçek kiracı şablonunu (templates.show) render eder; kaydedilmemiş
 * taslak değerler query string ile gelir.
 */
class PreviewController extends Controller
{
    public function show(Request $request): View
    {
        /** @var Restaurant $restaurant */
        $restaurant = app('restaurant')->load([
            'categories' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'categories.products' => fn ($q) => $q->where('is_available', true)->orderBy('sort_order'),
        ]);

        $categories = $restaurant->categories;
        $mini = $request->boolean('mini');
        if ($mini) {
            $categories = $categories->take(3);
        }

        // İZOLASYON: galeri mini önizlemeleri (?mini=1) kullanıcının hiçbir
        // rengini / gelişmiş dokunuşunu ALMAZ — her şablon kendi orijinal paletiyle görünür.
        $overrides = $mini
            ? array_filter(['template' => $request->string('template')->value() ?: null])
            : array_filter([
                'template' => $request->string('template')->value() ?: null,
                'accent_color' => $request->filled('accent') ? '#'.ltrim($request->string('accent'), '#') : null,
                'bg_color' => $request->filled('bg_color') ? '#'.ltrim($request->string('bg_color'), '#') : null,
                'heading_color' => $request->filled('heading_color') ? '#'.ltrim($request->string('heading_color'), '#') : null,
                'text_color' => $request->filled('text_color') ? '#'.ltrim($request->string('text_color'), '#') : null,
                'font_family' => $request->string('font')->value() ?: null,
                'logo_size' => $request->string('logo_size')->value() ?: null,
                'bg_pattern' => $request->string('bg_pattern')->value() ?: null,
                'entry_anim' => $request->string('entry_anim')->value() ?: null,
                'image_style' => $request->string('image_style')->value() ?: null,
                'corner_radius' => $request->string('corner_radius')->value() ?: null,
                'heading_weight' => $request->string('heading_weight')->value() ?: null,
                'text_size' => $request->string('text_size')->value() ?: null,
            ], fn ($v) => $v !== null);

        $view = $request->string('view')->value() === 'desktop' ? 'desktop' : 'phone';

        return view('templates.show', [
            'presenter' => new TemplatePresenter($restaurant, $overrides),
            'restaurant' => $restaurant,
            'categories' => $categories,
            'view' => $view,
            'embedded' => false,
            'tableLabel' => null,
        ]);
    }
}
