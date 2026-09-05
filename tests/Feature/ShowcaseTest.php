<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Şablon vitrini + SEO yüzeyi.
 *
 * Bu sayfalar sitenin organik trafik yüzeyi: sessizce bozulurlarsa kimse fark
 * etmez, sıralama düşer. Bu yüzden hem render'ları hem de SEO etiketleri
 * (kanonik, robots, JSON-LD) teste bağlı.
 */
class ShowcaseTest extends TestCase
{
    use RefreshDatabase;

    private function keys(): array
    {
        return array_keys((array) config('neva.templates'));
    }

    public function test_gallery_lists_every_template(): void
    {
        $response = $this->get(route('showcase.index'));

        $response->assertOk();

        foreach ($this->keys() as $key) {
            $response->assertSee(route('showcase.show', $key));
        }
    }

    public function test_gallery_is_indexable_and_canonical(): void
    {
        $this->get(route('showcase.index'))
            ->assertOk()
            ->assertSee('rel="canonical" href="'.route('showcase.index').'"', false)
            ->assertSee('index, follow');
    }

    public function test_every_template_has_its_own_page(): void
    {
        foreach ($this->keys() as $key) {
            $label = config("neva.templates.$key.label");

            $this->get(route('showcase.show', $key))
                ->assertOk()
                ->assertSee($label, false)
                ->assertSee('rel="canonical" href="'.route('showcase.show', $key).'"', false);
        }
    }

    public function test_unknown_template_is_404(): void
    {
        $this->get('/qr-menu-sablonlari/olmayan-sablon')->assertNotFound();
        $this->get('/sablon-onizleme/olmayan-sablon')->assertNotFound();
    }

    /**
     * Vitrin önizlemesi dizine GİRMEMELİ: 40 adet içerikçe aynı sayfa üretir ve
     * demo menü sahte bir Restaurant işaretlemesi yayınlamış olurdu.
     */
    public function test_preview_is_noindex_and_carries_no_restaurant_schema(): void
    {
        $key = $this->keys()[0];

        $response = $this->get(route('showcase.preview', $key));

        $response->assertOk();
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $response->assertSee('noindex, nofollow');
        $response->assertDontSee('"@type": "Restaurant"', false);
        $response->assertDontSee('rel="canonical"', false);
    }

    /** Demo menü BELLEKTE kurulur; hiçbir koşulda tabloya satır düşmemeli. */
    public function test_preview_writes_nothing_to_the_database(): void
    {
        $this->get(route('showcase.preview', $this->keys()[0]))->assertOk();

        $this->assertDatabaseCount('restaurants', 0);
        $this->assertDatabaseCount('categories', 0);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_preview_renders_the_sample_menu(): void
    {
        $this->get(route('showcase.preview', 'dark-prestige'))
            ->assertOk()
            ->assertSee('Lumina Bistro')
            ->assertSee('Ocakta Antrikot');
    }

    public function test_faq_page_emits_faq_schema(): void
    {
        $first = config('neva.seo.faq.0.q');

        $this->get(route('faq'))
            ->assertOk()
            ->assertSee($first, false)
            ->assertSee('"@type":"FAQPage"', false);
    }

    public function test_sitemap_contains_showcase_and_faq_pages(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertSee(route('showcase.index'), false);
        $response->assertSee(route('faq'), false);

        foreach ($this->keys() as $key) {
            $response->assertSee(route('showcase.show', $key), false);
        }
    }

    public function test_home_emits_a_single_linked_entity_graph(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('"@graph"', false);
        $response->assertSee('"@type":"Organization"', false);
        $response->assertSee('"@type":"SoftwareApplication"', false);
    }

    public function test_error_page_is_branded_and_turkish(): void
    {
        $this->get('/boyle-bir-sayfa-yok')
            ->assertNotFound()
            ->assertSee('Sayfa bulunamadı');
    }
}
