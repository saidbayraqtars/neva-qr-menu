<?php

namespace Tests\Feature;

use App\Models\MenuVisit;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Görüntülenme ölçümü: menü HTML'i önbellekten geldiği için sayaç
 * istemciden atılan `/olcum` isteğiyle artar.
 */
class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function trackUrl(Restaurant $restaurant, array $query = []): string
    {
        return tenant_domain($restaurant, '/olcum').(
            $query ? '?'.http_build_query($query) : ''
        );
    }

    public function test_olcum_istegi_gunluk_sayaci_artirir(): void
    {
        $restaurant = Restaurant::factory()->live()->create(['subdomain' => 'olcum-a']);

        $this->get($this->trackUrl($restaurant, ['yeni' => '1']))->assertNoContent();
        $this->get($this->trackUrl($restaurant, ['yeni' => '0']))->assertNoContent();

        $visit = MenuVisit::where('restaurant_id', $restaurant->id)->firstOrFail();

        $this->assertSame(2, $visit->views);
        $this->assertSame(1, $visit->visitors, 'Yalnızca "bugün ilk kez" işaretli istek ziyaretçi sayılır.');
        $this->assertSame('', $visit->table_label);
    }

    public function test_masa_etiketi_ayri_satirda_toplanir_ve_masa_sayaci_artar(): void
    {
        $restaurant = Restaurant::factory()->live()->create(['subdomain' => 'olcum-b']);
        $table = RestaurantTable::create([
            'restaurant_id' => $restaurant->id,
            'label' => 'Teras 4',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->get($this->trackUrl($restaurant, ['masa' => 'Teras 4']))->assertNoContent();

        $this->assertSame(1, MenuVisit::where('table_label', 'Teras 4')->value('views'));
        $this->assertSame(0, MenuVisit::where('table_label', '')->count());

        $table->refresh();
        $this->assertSame(1, $table->scan_count);
        $this->assertNotNull($table->last_scanned_at);
    }

    public function test_zararli_masa_etiketi_temizlenir(): void
    {
        $restaurant = Restaurant::factory()->live()->create(['subdomain' => 'olcum-c']);

        $this->get($this->trackUrl($restaurant, ['masa' => '<script>alert(1)</script>']))->assertNoContent();

        $label = MenuVisit::where('restaurant_id', $restaurant->id)->value('table_label');

        $this->assertStringNotContainsString('<', $label);
        $this->assertLessThanOrEqual(24, mb_strlen($label));
    }

    public function test_eski_jetonlu_qr_sayaci_ikinci_kez_artirmaz(): void
    {
        $restaurant = Restaurant::factory()->live()->create(['subdomain' => 'olcum-d']);
        $table = RestaurantTable::create([
            'restaurant_id' => $restaurant->id,
            'label' => 'Masa 1',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Yönlendirme sayacı artırmaz; sayım açılan sayfadaki ölçümle yapılır.
        $this->get(tenant_domain($restaurant, '/m/'.$table->qr_token))
            ->assertRedirect('/?masa=Masa%201');

        $this->assertSame(0, $table->fresh()->scan_count);
    }

    public function test_panel_istatistik_ekrani_toplamlari_gosterir(): void
    {
        [$user, $restaurant] = $this->ownerWithPlan();

        MenuVisit::create([
            'restaurant_id' => $restaurant->id,
            'visited_on' => now()->toDateString(),
            'table_label' => 'Bahçe 2',
            'views' => 7,
            'visitors' => 3,
        ]);

        $this->actingAs($user)
            ->get(route('panel.stats'))
            ->assertOk()
            ->assertSee('Bahçe 2')
            ->assertSee('İstatistik');
    }

    public function test_baska_restoranin_sayaci_panelde_gorunmez(): void
    {
        [$user, $restaurant] = $this->ownerWithPlan();
        $other = Restaurant::factory()->live()->create(['subdomain' => 'olcum-e']);

        MenuVisit::create([
            'restaurant_id' => $other->id,
            'visited_on' => now()->toDateString(),
            'table_label' => 'GizliMasa',
            'views' => 99,
            'visitors' => 9,
        ]);

        $this->actingAs($user)
            ->get(route('panel.stats'))
            ->assertOk()
            ->assertDontSee('GizliMasa');
    }
}
