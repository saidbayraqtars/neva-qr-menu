<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Sürükle-bırak sıralama uçları (kategoriler + ürünler). */
class OrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_kategoriler_yeniden_siralanir(): void
    {
        [$user, $restaurant] = $this->ownerWithPlan();

        $a = Category::factory()->for($restaurant)->create(['sort_order' => 0]);
        $b = Category::factory()->for($restaurant)->create(['sort_order' => 1]);

        $this->actingAs($user)
            ->postJson(route('panel.categories.reorder'), ['order' => [$b->id, $a->id]])
            ->assertNoContent();

        $this->assertSame(0, $b->fresh()->sort_order);
        $this->assertSame(1, $a->fresh()->sort_order);
    }

    public function test_urunler_kategori_icinde_siralanir(): void
    {
        [$user, $restaurant] = $this->ownerWithPlan();
        $category = Category::factory()->for($restaurant)->create();

        $a = Product::factory()->create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'sort_order' => 0]);
        $b = Product::factory()->create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'sort_order' => 1]);

        $before = $restaurant->fresh()->menu_version;

        $this->actingAs($user)
            ->postJson(route('panel.products.reorder'), [
                'category_id' => $category->id,
                'order' => [$b->id, $a->id],
            ])
            ->assertNoContent();

        $this->assertSame(0, $b->fresh()->sort_order);
        $this->assertSame(1, $a->fresh()->sort_order);
        $this->assertGreaterThan($before, $restaurant->fresh()->menu_version, 'Sıralama değişince menü önbelleği tazelenmeli.');
    }

    public function test_baskasinin_kategorisi_siralanamaz(): void
    {
        [$user] = $this->ownerWithPlan();
        [, $other] = $this->ownerWithPlan();

        $foreign = Category::factory()->for($other)->create();

        $this->actingAs($user)
            ->postJson(route('panel.products.reorder'), ['category_id' => $foreign->id, 'order' => [1]])
            ->assertForbidden();
    }

    public function test_baskasinin_urunu_tasinmaz(): void
    {
        [$user, $restaurant] = $this->ownerWithPlan();
        [, $other] = $this->ownerWithPlan();

        $mine = Category::factory()->for($restaurant)->create();
        $foreignCategory = Category::factory()->for($other)->create();
        $foreignProduct = Product::factory()->create([
            'restaurant_id' => $other->id,
            'category_id' => $foreignCategory->id,
            'sort_order' => 5,
        ]);

        // Kendi kategorisini gönderiyor ama başkasının ürün id'sini sıralamaya koyuyor.
        $this->actingAs($user)
            ->postJson(route('panel.products.reorder'), [
                'category_id' => $mine->id,
                'order' => [$foreignProduct->id],
            ])
            ->assertNoContent();

        $this->assertSame(5, $foreignProduct->fresh()->sort_order);
    }
}
