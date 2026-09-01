<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Restaurant;
use App\Support\TemplatePresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function show(): View
    {
        return $this->render();
    }

    public function category(Category $category): View
    {
        abort_unless($category->restaurant_id === app('tenant')->id, 404);

        return $this->render($category);
    }

    /**
     * Eski (masaya özel jetonlu) QR'lar için geriye dönük uyumluluk.
     * Yeni mimari: tek ana QR + `?masa=` parametresi / `#N` hash'i.
     */
    public function fromTable(string $token): RedirectResponse
    {
        /** @var Restaurant $tenant */
        $tenant = app('tenant');

        $table = $tenant->tables()->where('qr_token', $token)->where('is_active', true)->first();

        if ($table) {
            $table->forceFill([
                'scan_count' => $table->scan_count + 1,
                'last_scanned_at' => now(),
            ])->saveQuietly();

            return redirect('/?masa='.rawurlencode($table->label));
        }

        return redirect('/');
    }

    private function render(?Category $activeCategory = null): View
    {
        /** @var Restaurant $tenant */
        $tenant = app('tenant');

        $categories = $activeCategory
            ? $tenant->categories->where('id', $activeCategory->id)->values()
            : $tenant->categories;

        // Masa bilgisi: `?masa=` parametresi (yeni) → eski session değeri (geriye uyum).
        // Hash (#1) yalnızca istemcide görülür; shell.blade JS'i onu yakalar.
        $masa = trim((string) request()->query('masa', ''));
        $tableLabel = $masa !== '' ? $this->humanizeTable($masa) : session('table');

        return view('templates.show', [
            'presenter' => new TemplatePresenter($tenant),
            'restaurant' => $tenant,
            'categories' => $categories,
            'view' => 'phone',
            'embedded' => true,
            'tableLabel' => $tableLabel ?: null,
        ]);
    }

    /** "5" → "Masa 5" ; "Teras 4" → "Teras 4" (olduğu gibi). Zararlı karakterleri temizler. */
    private function humanizeTable(string $raw): string
    {
        $clean = preg_replace('/[^\p{L}\p{N}\s.\-]/u', '', $raw) ?? '';
        $clean = trim(mb_substr($clean, 0, 24));

        if ($clean === '') {
            return '';
        }

        return preg_match('/^\d+$/', $clean) ? "Masa {$clean}" : $clean;
    }
}
