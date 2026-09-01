<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Canlı menü önbelleği menu_version ile anahtarlanır:
 * panelde değişiklik → sürüm artar → ANINDA yeni içerik (TTL beklenmez).
 */
class MenuCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_urun_eklenince_menu_surumu_artar(): void
    {
        [, $restaurant] = $this->ownerWithPlan();
        $before = $restaurant->menu_version;

        $category = Category::factory()->for($restaurant)->create();

        $this->assertGreaterThan($before, $restaurant->fresh()->menu_version);

        $mid = $restaurant->fresh()->menu_version;

        Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
        ]);

        $this->assertGreaterThan($mid, $restaurant->fresh()->menu_version);
    }

    public function test_fiyat_guncellemesi_onbellek_anahtarini_degistirir(): void
    {
        [, $restaurant] = $this->ownerWithPlan();
        $category = Category::factory()->for($restaurant)->create();
        $product = Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'price' => 100,
        ]);

        $keyBefore = $restaurant->fresh()->menuCacheKey();

        $product->update(['price' => 150]);

        $this->assertNotSame($keyBefore, $restaurant->fresh()->menuCacheKey());
    }

    public function test_urun_silinince_de_surum_artar(): void
    {
        [, $restaurant] = $this->ownerWithPlan();
        $category = Category::factory()->for($restaurant)->create();
        $product = Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
        ]);

        $before = $restaurant->fresh()->menu_version;
        $product->delete();

        $this->assertGreaterThan($before, $restaurant->fresh()->menu_version);
    }
}
