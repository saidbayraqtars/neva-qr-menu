<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_template_renders_in_the_design_preview(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->for($user)->create();
        $category = Category::factory()->for($restaurant)->create();
        Product::factory()->count(3)->for($restaurant)->for($category)->create();

        foreach (Restaurant::templateKeys() as $key) {
            $response = $this->actingAs($user)->get(route('panel.preview', [
                'template' => $key,
                'accent' => 'ff0055',
                'font' => 'Poppins',
                'view' => 'phone',
            ]));

            $response->assertOk();
            $response->assertSee($category->name);
            $response->assertSee('data-tpl="'.$key.'"', false);
            $response->assertSee('id="tpl-root"', false);
        }
    }

    public function test_preview_supports_desktop_view(): void
    {
        $user = User::factory()->create();
        Restaurant::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('panel.preview', ['view' => 'desktop', 'template' => 'modern-grid']))
            ->assertOk()
            ->assertSee('data-view="desktop"', false);
    }

    public function test_invalid_template_falls_back_to_default(): void
    {
        $user = User::factory()->create();
        Restaurant::factory()->for($user)->create(['template' => 'this-does-not-exist']);

        $this->actingAs($user)->get(route('panel.preview'))->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\Group('slow')]
    public function test_menu_pdf_builds_for_every_template(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->for($user)->create();
        $category = Category::factory()->for($restaurant)->create();
        Product::factory()->count(3)->for($restaurant)->for($category)->create();

        $service = app(\App\Services\MenuPdfService::class);

        foreach (Restaurant::templateKeys() as $key) {
            $restaurant->update(['template' => $key]);
            $pdf = $service->build($restaurant->fresh());
            $output = $this->pdfContents($pdf);
            $this->assertStringStartsWith('%PDF-', $output, "PDF üretilmedi: $key");
        }
    }

    public function test_catalog_has_forty_templates_and_fiftyfour_fonts_with_complete_flags(): void
    {
        $templates = config('neva.templates');
        $fonts = config('neva.fonts');
        $controls = array_keys(config('neva.variations'));

        $this->assertCount(40, $templates, 'Şablon sayısı 40 olmalı');
        $this->assertCount(54, $fonts, 'Font sayısı 54 olmalı');

        foreach ($templates as $key => $t) {
            foreach (['family', 'cover', 'animation', 'layout_type', 'mood', 'palette', 'tokens', 'locks'] as $flag) {
                $this->assertArrayHasKey($flag, $t, "$key şablonunda '$flag' eksik");
            }
            // NOT: 'animation' yalnızca data-anim olarak yayılır, henüz hiçbir CSS/JS tüketmiyor.
            // Gerçek giriş animasyonu kullanıcı seçimli 'entry_anim' (config/neva.php › variations).
            $this->assertContains($t['animation'], ['none', 'scale', 'glow', 'fade', 'pop', 'slide'], "$key: geçersiz animation");
            $this->assertContains($t['layout_type'], ['list', 'grid', 'masonry'], "$key: geçersiz layout_type");
            foreach ((array) $t['locks'] as $locked) {
                $this->assertContains($locked, $controls, "$key: geçersiz lock '$locked'");
            }
            $this->assertFileExists(resource_path("views/templates/skeletons/{$key}.blade.php"), "$key iskeleti yok");
        }

        foreach ($fonts as $name => $f) {
            $this->assertContains($f['type'], ['sans', 'serif', 'display', 'script'], "$name: geçersiz font türü");
            $this->assertArrayHasKey('q', $f);
            $this->assertArrayHasKey('stack', $f);
        }
    }

    public function test_micro_variations_render_and_resolve(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->for($user)->create(['template' => 'minimalist-kaffe']);
        Category::factory()->for($restaurant)->has(Product::factory()->count(2)->state(['restaurant_id' => $restaurant->id]))->create();

        // botanical-green: hepsini destekler (locks: []).
        $response = $this->actingAs($user)->get(route('panel.preview', [
            'template' => 'botanical-green',
            'bg_pattern' => 'stripes',
            'entry_anim' => 'blur',
            'image_style' => 'hidden',
            'corner_radius' => 'round',
            'bg_color' => '1e293b',
            'heading_weight' => 'bold',
            'text_size' => 'lg',
        ]));

        $response->assertOk();
        $response->assertSee('data-bg="stripes"', false);
        $response->assertSee('data-entry="blur"', false);
        $response->assertSee('data-img="hidden"', false);
        $response->assertSee('data-hw="bold"', false);
        $response->assertSee('data-ts="lg"', false);
        $response->assertSee('--t-radius: 24px', false);
        $response->assertSee('--t-bg: #1e293b', false);

        // minimalist-kaffe: corner_radius + image_style KİLİTLİ → override yok sayılır.
        $locked = $this->actingAs($user)->get(route('panel.preview', [
            'template' => 'minimalist-kaffe', 'corner_radius' => 'round', 'image_style' => 'hidden',
        ]))->getContent();
        $this->assertStringContainsString('data-img="auto"', $locked);
        $this->assertStringNotContainsString('--t-radius: 24px', $locked);
    }

    public function test_advanced_touches_are_stored_per_template(): void
    {
        \Illuminate\Support\Facades\Storage::fake(config('neva.uploads.disk'));

        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->for($user)->create(['template' => 'dark-prestige']);
        Category::factory()->for($restaurant)->has(Product::factory()->count(1)->state(['restaurant_id' => $restaurant->id]))->create();
        $restaurant->subdomainRequests()->create(['user_id' => $user->id, 'requested_subdomain' => 'x', 'status' => 'pending']);

        $this->actingAs($user)->put(route('panel.design.update'), [
            'template' => 'dark-prestige',
            'name' => 'Test', 'accent_color' => '#123456', 'font_family' => 'Inter',
            'logo_size' => 'medium', 'currency' => 'TRY',
            'bg_pattern' => 'glow', 'entry_anim' => 'bounce', 'image_style' => 'hero',
            'corner_radius' => 'sharp', 'bg_color' => '#0B0E1F', 'heading_weight' => 'bold', 'text_size' => 'lg',
        ])->assertRedirect();

        $bucket = $restaurant->fresh()->template_settings['dark-prestige'] ?? [];
        $this->assertSame('glow', $bucket['bg_pattern']);
        $this->assertSame('bounce', $bucket['entry_anim']);
        $this->assertSame('hero', $bucket['image_style']);
        $this->assertSame('sharp', $bucket['corner_radius']);
        $this->assertSame('#0B0E1F', $bucket['bg_color']);
        $this->assertSame('bold', $bucket['heading_weight']);

        // Başka şablona dokunuşlar sızmaz.
        $this->assertArrayNotHasKey('minimalist-kaffe', $restaurant->fresh()->template_settings);
        $mk = new \App\Support\TemplatePresenter($restaurant->fresh(), ['template' => 'minimalist-kaffe']);
        $this->assertSame('solid', $mk->bgPattern());
        $this->assertSame(config('neva.templates.minimalist-kaffe.tokens.bg'), $mk->bgColor());
    }

    public function test_pdf_embeds_product_images_with_logo_fallback(): void
    {
        \Illuminate\Support\Facades\Storage::fake(config('neva.uploads.disk'));
        $disk = \Illuminate\Support\Facades\Storage::disk(config('neva.uploads.disk'));

        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->for($user)->create(['template' => 'grid-showcase']);

        // Küçük gerçek PNG (1x1) — logo.
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $disk->put('brand/logo.png', $png);
        $restaurant->update(['logo_path' => 'brand/logo.png']);

        $cat = Category::factory()->for($restaurant)->create();
        $disk->put('p/photo.png', $png);
        Product::factory()->for($restaurant)->for($cat)->create(['image_path' => 'p/photo.png', 'price' => 100]);
        Product::factory()->for($restaurant)->for($cat)->create(['image_path' => null, 'price' => 90]); // → logo fallback

        $out = $this->pdfContents(app(\App\Services\MenuPdfService::class)->build($restaurant->fresh()));

        $this->assertStringStartsWith('%PDF-', $out);
        // dompdf gömülü JPEG akışı üretmeli (görseller GD ile JPEG'e çevriliyor).
        $this->assertStringContainsString('/Subtype /Image', $out, 'PDF gömülü görsel içermeli');
        $this->assertGreaterThan(20000, strlen($out), 'Görselli PDF salt-metin PDF’ten büyük olmalı');
    }

    public function test_heading_and_text_colors_are_per_template(): void
    {
        \Illuminate\Support\Facades\Storage::fake(config('neva.uploads.disk'));

        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->for($user)->create(['template' => 'dark-prestige']);
        Category::factory()->for($restaurant)->has(Product::factory()->count(1)->state(['restaurant_id' => $restaurant->id]))->create();
        $restaurant->subdomainRequests()->create(['user_id' => $user->id, 'requested_subdomain' => 'x', 'status' => 'pending']);

        $this->actingAs($user)->put(route('panel.design.update'), [
            'template' => 'dark-prestige',
            'name' => 'T', 'accent_color' => '#123456', 'font_family' => 'Inter',
            'logo_size' => 'medium', 'currency' => 'TRY',
            'heading_color' => '#FF00AA', 'text_color' => '#00FF88',
            'entry_anim' => 'elastic',
        ])->assertRedirect();

        $bucket = $restaurant->fresh()->template_settings['dark-prestige'];
        $this->assertSame('#FF00AA', $bucket['heading_color']);
        $this->assertSame('#00FF88', $bucket['text_color']);
        $this->assertSame('elastic', $bucket['entry_anim']);

        $html = $this->actingAs($user)->get(route('panel.preview', ['template' => 'dark-prestige']))->getContent();
        $this->assertStringContainsString('--t-heading: #FF00AA', $html);
        $this->assertStringContainsString('--t-text: #00FF88', $html);
        $this->assertStringContainsString('data-entry="elastic"', $html);

        // Diğer şablon etkilenmez.
        $mk = new \App\Support\TemplatePresenter($restaurant->fresh(), ['template' => 'minimalist-kaffe']);
        $this->assertSame(config('neva.templates.minimalist-kaffe.palette.heading'), $mk->heading());
    }

    public function test_unsupported_control_is_locked_and_ignored(): void
    {
        $user = User::factory()->create();
        // minimalist-kaffe corner_radius'u kilitler.
        $restaurant = Restaurant::factory()->for($user)->create([
            'template' => 'minimalist-kaffe',
            'template_settings' => ['minimalist-kaffe' => ['corner_radius' => 'round']],
        ]);

        $p = new \App\Support\TemplatePresenter($restaurant);
        $this->assertFalse($p->supports('corner_radius'));
        // Kilitli → şablon token'ı, kullanıcı seçimi yok sayılır.
        $this->assertSame(config('neva.templates.minimalist-kaffe.tokens.radius'), $p->radius());
    }

    public function test_invalid_micro_variation_is_rejected(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->for($user)->create();
        Category::factory()->for($restaurant)->has(Product::factory()->count(1)->state(['restaurant_id' => $restaurant->id]))->create();
        $restaurant->subdomainRequests()->create(['user_id' => $user->id, 'requested_subdomain' => 'x', 'status' => 'pending']);

        $this->actingAs($user)->put(route('panel.design.update'), [
            'template' => 'dark-prestige',
            'name' => 'Test', 'accent_color' => '#123456', 'font_family' => 'Inter',
            'logo_size' => 'medium', 'currency' => 'TRY',
            'bg_pattern' => 'rainbow-sparkle',
        ])->assertSessionHasErrors('bg_pattern');
    }

    public function test_featured_and_discounted_products_are_marked_in_every_template(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->for($user)->create();
        $category = Category::factory()->for($restaurant)->create();
        Product::factory()->for($restaurant)->for($category)->create(['name' => 'Şef Spesiyali', 'is_featured' => true, 'price' => 200, 'discount_price' => null]);
        Product::factory()->for($restaurant)->for($category)->create(['name' => 'Kampanya Ürünü', 'is_featured' => false, 'price' => 100, 'discount_price' => 75]);

        foreach (Restaurant::templateKeys() as $key) {
            $html = $this->actingAs($user)->get(route('panel.preview', ['template' => $key]))->assertOk()->getContent();

            $this->assertStringContainsString('data-feat="1"', $html, "$key: öne çıkan işareti yok");
            $this->assertStringContainsString('data-disc="1"', $html, "$key: indirim işareti yok");
            // %25 indirim (100 -> 75)
            $this->assertMatchesRegularExpression('/%25\s*İndirim/u', $html, "$key: indirim yüzdesi rozeti yok");
        }
    }

    public function test_featured_shortcut_section_appears_for_royal_blue(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->for($user)->create(['template' => 'royal-blue']);
        $category = Category::factory()->for($restaurant)->create();
        Product::factory()->for($restaurant)->for($category)->create(['is_featured' => true, 'price' => 120]);
        Product::factory()->for($restaurant)->for($category)->create(['price' => 90, 'discount_price' => 60]);

        $html = $this->actingAs($user)->get(route('panel.preview', ['template' => 'royal-blue']))->assertOk()->getContent();

        $this->assertStringContainsString('Öne Çıkanlar', $html);
        $this->assertStringContainsString('Fırsatlar', $html);
        $this->assertStringContainsString("active === 'feat'", $html);
    }

    public function test_cover_upload_is_ignored_for_non_supporting_templates(): void
    {
        \Illuminate\Support\Facades\Storage::fake(config('neva.uploads.disk'));

        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->for($user)->create(['template' => 'minimalist-kaffe']);
        Category::factory()->for($restaurant)->has(Product::factory()->count(1)->state(['restaurant_id' => $restaurant->id]))->create();
        $restaurant->subdomainRequests()->create(['user_id' => $user->id, 'requested_subdomain' => 'x', 'status' => 'pending']);

        $this->actingAs($user)->put(route('panel.design.update'), [
            'template' => 'minimalist-kaffe',
            'name' => 'Test', 'accent_color' => '#123456', 'font_family' => 'Inter',
            'logo_size' => 'medium', 'currency' => 'TRY',
            'cover' => \Illuminate\Http\UploadedFile::fake()->image('c.jpg'),
        ])->assertRedirect();

        $this->assertNull($restaurant->fresh()->cover_path, 'Kapak desteklemeyen şablonda kaydedilmemeli');
    }

    /**
     * MenuPdfService::build() headless Chrome ile üretilen dosyayı BinaryFileResponse
     * olarak döner (eski dompdf nesnesi değil) — içeriği diskten okuruz.
     */
    private function pdfContents(\Symfony\Component\HttpFoundation\BinaryFileResponse $response): string
    {
        return (string) file_get_contents($response->getFile()->getPathname());
    }
}
