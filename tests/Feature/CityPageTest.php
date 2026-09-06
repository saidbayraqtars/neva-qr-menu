<?php

namespace Tests\Feature;

use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Şehir sayfaları — yerel arama yüzeyi.
 *
 * Bu sayfaların iki sessiz ölüm biçimi var:
 *  1. Şablonun bozulup 500 dönmesi — kimse fark etmez, sıralama gider.
 *  2. İçeriğin şehirden şehre AYNI olması — Google doorway page sayar,
 *     indekslemez, kötü ihtimalle tüm siteye güven kaybettirir.
 * İkincisi bir kod hatası olmadığı için ancak testle yakalanır.
 */
class CityPageTest extends TestCase
{
    use RefreshDatabase;

    private function cities(): array
    {
        return (array) config('neva.cities');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Sayfa paket kartlarını basıyor; boş plan tablosuyla test bir şey
        // doğrulamaz.
        $this->seed(PlanSeeder::class);

        // LocalBusiness yalnızca açık adres girilmişse üretilir. Testin
        // sonucu .env'e bağlı kalmasın diye künyeyi burada sabitliyoruz.
        config()->set('neva.legal.company.address', 'Adalet Mahallesi, Karadeniz Caddesi No: 7/11');
        config()->set('neva.legal.company.city', 'İlkadım');
        config()->set('neva.legal.company.region', 'Samsun');
    }

    public function test_every_configured_city_has_a_page(): void
    {
        foreach ($this->cities() as $slug => $city) {
            $this->get(route('city', $slug))
                ->assertOk()
                ->assertSee($city['name'], false);
        }
    }

    public function test_unknown_city_is_not_invented(): void
    {
        $this->get('/paris-qr-menu')->assertNotFound();
    }

    public function test_city_route_does_not_shadow_static_pages(): void
    {
        $this->get(route('showcase.index'))->assertOk();
        $this->get(route('pricing'))->assertOk();
        $this->get(route('faq'))->assertOk();
    }

    public function test_each_city_page_is_indexable_and_canonical(): void
    {
        foreach (array_keys($this->cities()) as $slug) {
            $this->get(route('city', $slug))
                ->assertSee('rel="canonical" href="'.route('city', $slug).'"', false)
                ->assertSee('index, follow');
        }
    }

    /**
     * Doorway page koruması: her şehrin tanıtım metni ve yerel sahne
     * anlatımı kendine ait olmalı. Kopyala-yapıştır bir şehir eklendiğinde
     * bu test düşer.
     */
    public function test_city_copy_is_unique_per_city(): void
    {
        foreach (['lead', 'scene', 'template_why'] as $field) {
            $values = collect($this->cities())->pluck($field)->filter();

            $this->assertSame(
                $values->count(),
                $values->unique()->count(),
                "config/neva.cities içinde '$field' alanı birden fazla şehirde aynı — doorway page riski."
            );
        }
    }

    public function test_every_city_carries_its_own_faq(): void
    {
        $questions = collect();

        foreach ($this->cities() as $slug => $city) {
            $this->assertNotEmpty($city['faq'], "$slug için şehre özel SSS yazılmamış.");

            foreach ($city['faq'] as $item) {
                $questions->push($item['q']);
            }
        }

        $this->assertSame(
            $questions->count(),
            $questions->unique()->count(),
            'Şehir SSS soruları tekrar ediyor — FAQPage işaretlemesi çoğaltılmış içerik üretir.'
        );
    }

    /** Önerilen şablon anahtarları gerçekten var mı — kırık iç link olmasın. */
    public function test_recommended_templates_exist(): void
    {
        $templates = (array) config('neva.templates');

        foreach ($this->cities() as $slug => $city) {
            foreach ($city['templates'] as $key) {
                $this->assertArrayHasKey($key, $templates, "$slug için tanımsız şablon: $key");
            }
        }
    }

    public function test_city_pages_are_listed_in_sitemap(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();

        foreach (array_keys($this->cities()) as $slug) {
            $response->assertSee(route('city', $slug), false);
        }
    }

    /**
     * Şubesi olmayan bir ile LocalBusiness koymak yanlış beyandır: o sayfada
     * yalnızca Service + areaServed olmalı. Merkez şehirde ise ikisi de var.
     */
    public function test_local_business_markup_only_on_the_hub_city(): void
    {
        foreach ($this->cities() as $slug => $city) {
            $response = $this->get(route('city', $slug));

            $response->assertSee('"@type":"Service"', false);

            if (($city['onsite'] ?? null) === 'hub') {
                $response->assertSee('"@type":"ProfessionalService"', false);
            } else {
                $response->assertDontSee('"@type":"ProfessionalService"', false);
            }
        }
    }

    /**
     * Adres girilmemişse hiçbir sayfada LocalBusiness basılmamalı: adressiz
     * bir yerel işletme düğümü, hiç düğüm olmamasından kötüdür.
     */
    public function test_local_business_is_omitted_when_address_is_missing(): void
    {
        config()->set('neva.legal.company.address', '');

        $this->get(route('city', 'samsun'))
            ->assertOk()
            ->assertDontSee('"@type":"ProfessionalService"', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('"@type":"ProfessionalService"', false);
    }

    public function test_cities_are_linked_from_every_marketing_page(): void
    {
        $response = $this->get(route('home'))->assertOk();

        foreach (array_keys($this->cities()) as $slug) {
            $response->assertSee(route('city', $slug), false);
        }
    }
}
