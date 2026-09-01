<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Model;

/**
 * Menü içeriği/tasarımı her değiştiğinde restaurants.menu_version artar.
 *
 * Canlı menü önbelleği anahtarında bu sürüm geçtiği için, panelde yapılan
 * değişiklik ANINDA canlıya yansır — 2 saatlik TTL'nin dolması beklenmez.
 * TTL yalnızca üst sınırdır (eski sürüm anahtarları kendiliğinden düşer).
 */
class MenuVersionObserver
{
    public function saved(Model $model): void
    {
        $this->bump($model);
    }

    public function deleted(Model $model): void
    {
        $this->bump($model);
    }

    public function restored(Model $model): void
    {
        $this->bump($model);
    }

    private function bump(Model $model): void
    {
        $restaurant = match (true) {
            $model instanceof Restaurant => $model,
            $model instanceof Category, $model instanceof Product => Restaurant::withTrashed()->find($model->restaurant_id),
            default => null,
        };

        if (! $restaurant) {
            return;
        }

        // Restoranın kendi menu_version güncellemesi sonsuz döngü yapmasın.
        if ($model instanceof Restaurant && $model->wasChanged('menu_version')) {
            return;
        }

        $restaurant->bumpMenuVersion();
    }
}
