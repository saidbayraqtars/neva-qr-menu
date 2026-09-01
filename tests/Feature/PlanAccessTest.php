<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Paket bazlı yetki matrisi SUNUCU TARAFINDA zorlanıyor mu?
 * View'lerdeki @if'ler gizler; bu testler doğrudan HTTP isteğiyle sızmayı dener.
 */
class PlanAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_hosting_haric_paketi_alt_domain_talep_edemez(): void
    {
        [$user] = $this->ownerWithPlan('hosting-haric');

        $this->actingAs($user)
            ->post(route('panel.subdomain.store'), ['requested_subdomain' => 'galya'])
            ->assertForbidden();

        $this->assertDatabaseCount('subdomain_requests', 0);
    }

    public function test_hosting_haric_paketi_onaya_gonderemez(): void
    {
        [$user] = $this->ownerWithPlan('hosting-haric');

        $this->actingAs($user)
            ->post(route('panel.submit'))
            ->assertForbidden();
    }

    public function test_hosting_haric_paketi_masa_ekleyemez(): void
    {
        [$user] = $this->ownerWithPlan('hosting-haric');

        $this->actingAs($user)
            ->post(route('panel.qr.store'), ['label' => 'Masa', 'count' => 3])
            ->assertForbidden();

        $this->assertDatabaseCount('restaurant_tables', 0);
    }

    public function test_hosting_dahil_paketi_alt_domain_talep_edebilir(): void
    {
        [$user] = $this->ownerWithPlan('hosting-dahil');

        $this->actingAs($user)
            ->post(route('panel.subdomain.store'), ['requested_subdomain' => 'galya'])
            ->assertRedirect();

        $this->assertDatabaseHas('subdomain_requests', [
            'requested_subdomain' => 'galya',
            'status' => 'pending',
        ]);
    }

    public function test_paketsiz_kullanici_urun_limitine_takilir(): void
    {
        // 'default' matrisi: en fazla 30 ürün, 5 kategori
        [$user, $restaurant] = $this->ownerWithPlan('paketsiz-deneme');

        $category = Category::factory()->for($restaurant)->create();
        Product::factory()->count(30)->create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($user)
            ->from(route('panel.products.create'))
            ->post(route('panel.products.store'), [
                'category_id' => $category->id,
                'name' => 'Fazladan Ürün',
                'price' => 50,
            ])
            ->assertSessionHasErrors('name');

        $this->assertSame(30, $restaurant->products()->count());
    }

    public function test_fiziksel_qr_paketi_sinirsiz_masa_ekleyebilir(): void
    {
        [$user, $restaurant] = $this->ownerWithPlan('fiziksel-qr');

        $this->actingAs($user)
            ->post(route('panel.qr.store'), ['label' => 'Masa', 'count' => 40])
            ->assertRedirect();

        $this->assertSame(40, $restaurant->tables()->count());
    }
}
