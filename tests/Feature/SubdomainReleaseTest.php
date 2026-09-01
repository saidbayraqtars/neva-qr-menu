<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Services\SubdomainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Silinen restoranın alt domaini serbest kalmalı; aksi halde tekil indeks
 * yüzünden ad bir daha kimseye verilemezdi.
 */
class SubdomainReleaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_silinen_restoranin_alt_domaini_serbest_kalir(): void
    {
        $restaurant = Restaurant::factory()->live()->create(['subdomain' => 'galya']);

        $restaurant->delete();
        $restaurant->refresh();

        $this->assertNull($restaurant->subdomain);
        $this->assertSame('galya', $restaurant->released_subdomain);
        $this->assertNotNull($restaurant->subdomain_released_at);

        $this->assertTrue(app(SubdomainService::class)->availability('galya')['available']);
    }

    public function test_serbest_kalan_ad_baska_isletmeye_verilebilir(): void
    {
        Restaurant::factory()->live()->create(['subdomain' => 'galya'])->delete();

        $yeni = Restaurant::factory()->live()->create(['subdomain' => 'galya']);

        $this->assertSame('galya', $yeni->fresh()->subdomain);
    }

    public function test_geri_yuklenen_restoran_adini_geri_alir(): void
    {
        $restaurant = Restaurant::factory()->live()->create(['subdomain' => 'galya']);
        $restaurant->delete();

        $restaurant->restore();

        $this->assertSame('galya', $restaurant->fresh()->subdomain);
        $this->assertNull($restaurant->fresh()->released_subdomain);
    }

    public function test_ad_baskasina_gittiyse_geri_yukleme_adi_bos_birakir(): void
    {
        $restaurant = Restaurant::factory()->live()->create(['subdomain' => 'galya']);
        $restaurant->delete();

        Restaurant::factory()->live()->create(['subdomain' => 'galya']);

        $restaurant->restore();

        $this->assertNull($restaurant->fresh()->subdomain);
        $this->assertSame('galya', $restaurant->fresh()->released_subdomain);
    }

    public function test_silinen_restoranin_alt_domaini_artik_yayinda_degil(): void
    {
        $restaurant = Restaurant::factory()->live()->create(['subdomain' => 'galya']);
        $restaurant->delete();

        $this->get('http://galya.'.config('neva.root_domain').'/')->assertNotFound();
    }
}
