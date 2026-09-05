<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sahipsiz alt domain → "bu adres sizin olabilir" ekranı.
 *
 * En kritik davranış DURUM KODU: sayfa satış ekranı gibi görünse de 404
 * dönmeli. Wildcard sonsuz alt domain üretiyor; 200 dönmek Google için
 * soft-404 demek ve site genelindeki tarama güvenini düşürür.
 */
class ClaimPageTest extends TestCase
{
    use RefreshDatabase;

    private function host(string $label): string
    {
        return $label.'.'.config('neva.root_domain');
    }

    public function test_unclaimed_subdomain_returns_404_but_offers_the_address(): void
    {
        $response = $this->get('http://'.$this->host('bosbirad'));

        $response->assertNotFound();
        $response->assertSee('Bu adres boşta');
        $response->assertSee('bosbirad.'.config('neva.root_domain'));
        $response->assertSee('noindex, nofollow', false);
    }

    public function test_reserved_subdomain_is_not_offered(): void
    {
        // 'admin' config'te ayrılmış; kimseye satılmamalı.
        $response = $this->get('http://'.$this->host('admin'));

        $response->assertNotFound();
        $response->assertDontSee('Bu adres boşta');
        $response->assertSee('yayında menü yok');
    }

    public function test_taken_subdomain_is_not_offered_even_when_not_live(): void
    {
        $user = User::factory()->create();
        Restaurant::factory()->create([
            'user_id' => $user->id,
            'subdomain' => 'alinmis',
            'status' => Restaurant::STATUS_DRAFT,   // yayında DEĞİL
        ]);

        $response = $this->get('http://'.$this->host('alinmis'));

        $response->assertNotFound();
        // Başkasının taslak adresi "boşta" diye pazarlanmamalı.
        $response->assertDontSee('Bu adres boşta');
    }

    /** Görsel/ölçüm uçları ve JSON istekleri satış ekranı almaz — düz 404. */
    public function test_non_html_requests_get_a_plain_404(): void
    {
        $this->getJson('http://'.$this->host('bosbirad').'/olcum')->assertNotFound();

        $this->get('http://'.$this->host('bosbirad').'/gorsel/restaurants/1/brand/x.png')
            ->assertNotFound();
    }

    public function test_register_page_echoes_the_requested_label(): void
    {
        $this->get('/register?alan=lumina')
            ->assertOk()
            ->assertSee('lumina.'.config('neva.root_domain'))
            ->assertSee('kimseye ayrılmadı');
    }

    /** Uydurma bir `alan` parametresi sayfayı bozmamalı. */
    public function test_register_page_ignores_invalid_label(): void
    {
        $this->get('/register?alan='.urlencode('<script>alert(1)</script>'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
