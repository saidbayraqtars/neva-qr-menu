<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pazarlama sayfaları yalnızca kök alan adında yayınlanır.
 *
 * Wildcard alt domain kurulumunda bu sessiz bir çoğaltma kaynağı: kiracı
 * rotalarında eşleşmeyen her yol pazarlama rotalarına düşer ve aynı sayfa
 * kiracı sayısı kadar adreste 200 döner. Kimse fark etmez; yalnızca sıralama
 * düşer. Bu yüzden davranış teste bağlı.
 */
class RootDomainOnlyTest extends TestCase
{
    use RefreshDatabase;

    private function tenantHost(string $label = 'lumina'): string
    {
        return $label.'.'.config('neva.root_domain');
    }

    private function liveTenant(string $label = 'lumina'): Restaurant
    {
        return Restaurant::factory()->create([
            'user_id' => User::factory()->create()->id,
            'subdomain' => $label,
            // `Restaurant::live()` kapsamı STATUS_APPROVED + alt domain arar.
            'status' => Restaurant::STATUS_APPROVED,
        ]);
    }

    public function test_marketing_pages_redirect_from_a_tenant_subdomain(): void
    {
        $this->liveTenant();

        foreach (['/fiyatlandirma', '/hakkimizda', '/qr-menu-sablonlari', '/samsun-qr-menu'] as $path) {
            $this->get('http://'.$this->tenantHost().$path)
                ->assertRedirect('http://'.config('neva.root_domain').$path);
        }
    }

    public function test_redirect_is_permanent(): void
    {
        $this->liveTenant();

        $this->get('http://'.$this->tenantHost().'/fiyatlandirma')
            ->assertStatus(301);
    }

    public function test_query_string_survives_the_redirect(): void
    {
        $this->liveTenant();

        $this->get('http://'.$this->tenantHost().'/register?alan=lumina')
            ->assertRedirect('http://'.config('neva.root_domain').'/register?alan=lumina');
    }

    /** 301 gövdeyi düşürür; form gönderimi sessizce kaybolmaktansa 404 olmalı. */
    public function test_unsafe_methods_are_not_redirected(): void
    {
        $this->liveTenant();

        $this->post('http://'.$this->tenantHost().'/iletisim', [
            'name' => 'Test', 'email' => 't@example.com', 'message' => 'yeterince uzun bir mesaj',
        ])->assertNotFound();
    }

    /** Kiracının kendi rotaları etkilenmemeli — onlar zaten alt domainde yaşıyor. */
    public function test_tenant_routes_still_work_on_the_subdomain(): void
    {
        $this->liveTenant();

        $this->get('http://'.$this->tenantHost().'/')->assertOk();
        $this->get('http://'.$this->tenantHost().'/sitemap.xml')->assertOk();
        $this->get('http://'.$this->tenantHost().'/robots.txt')->assertOk();
    }

    public function test_root_domain_is_untouched(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('pricing'))->assertOk();
        $this->get(route('city', 'samsun'))->assertOk();
    }

    /** Sahipsiz alt domain hâlâ "bu adres boşta" ekranını 404 ile vermeli. */
    public function test_claim_page_still_answers_on_an_unclaimed_subdomain(): void
    {
        $this->get('http://'.$this->tenantHost('bosbirad'))
            ->assertNotFound()
            ->assertSee('Bu adres boşta');
    }
}
