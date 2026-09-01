<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Yüklemeler özel diskte durur; `/gorsel/...` ucu yalnızca yayındaki
 * restoranın görselini herkese açar, gerisini sahibine/admine verir.
 */
class MediaAccessTest extends TestCase
{
    use RefreshDatabase;

    private string $path = 'restaurants/%d/products/kahve.jpg';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('neva.uploads.disk'));
    }

    private function putImage(Restaurant $restaurant): string
    {
        $path = sprintf($this->path, $restaurant->id);

        Storage::disk(config('neva.uploads.disk'))
            ->put($path, UploadedFile::fake()->image('kahve.jpg')->get());

        return $path;
    }

    public function test_yayindaki_restoranin_gorseli_herkese_acik(): void
    {
        $restaurant = Restaurant::factory()->live()->create(['subdomain' => 'gorsel-a']);
        $path = $this->putImage($restaurant);

        $response = $this->get(media_url($path))->assertOk();

        $this->assertStringContainsString('max-age', $response->headers->get('Cache-Control'));
    }

    public function test_taslak_restoranin_gorseli_disariya_kapali(): void
    {
        [$owner, $restaurant] = $this->ownerWithPlan();
        $path = $this->putImage($restaurant);

        $this->get(media_url($path))->assertNotFound();

        $this->actingAs($owner)->get(media_url($path))->assertOk();
    }

    public function test_baska_kullanici_taslak_gorseli_goremez(): void
    {
        [, $restaurant] = $this->ownerWithPlan();
        $path = $this->putImage($restaurant);

        $this->actingAs(User::factory()->create())
            ->get(media_url($path))
            ->assertNotFound();
    }

    public function test_silinen_restoranin_gorseli_kapanir(): void
    {
        $restaurant = Restaurant::factory()->live()->create(['subdomain' => 'gorsel-b']);
        $path = $this->putImage($restaurant);

        $this->get(media_url($path))->assertOk();

        $restaurant->delete();

        $this->get(media_url($path))->assertNotFound();
    }

    public function test_dizin_cikisi_denemesi_reddedilir(): void
    {
        $this->get('/gorsel/restaurants/1/products/../../../../.env')->assertNotFound();
        $this->get('/gorsel/../.env')->assertNotFound();
    }

    public function test_kalici_silmede_dosyalar_da_silinir(): void
    {
        $restaurant = Restaurant::factory()->live()->create(['subdomain' => 'gorsel-c']);
        $path = $this->putImage($restaurant);

        $restaurant->forceDelete();

        $this->assertFalse(Storage::disk(config('neva.uploads.disk'))->exists($path));
    }

    public function test_kiraci_alt_domaininden_de_servis_edilir(): void
    {
        $restaurant = Restaurant::factory()->live()->create(['subdomain' => 'gorsel-d']);
        $path = $this->putImage($restaurant);

        $this->get(tenant_domain($restaurant, media_url($path)))->assertOk();
    }
}
