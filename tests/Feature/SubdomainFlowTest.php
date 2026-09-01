<?php

namespace Tests\Feature;

use App\Jobs\PublishSubdomain;
use App\Models\Category;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\SubdomainRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SubdomainFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_musaitlik_endpointi_bos_ad_icin_uyarir(): void
    {
        [$user] = $this->ownerWithPlan();

        $this->actingAs($user)
            ->getJson(route('panel.subdomain.availability', ['label' => 'ab']))
            ->assertOk()
            ->assertJson(['status' => 'invalid', 'available' => false]);
    }

    public function test_musaitlik_endpointi_alinmis_adi_bildirir(): void
    {
        [$owner] = $this->ownerWithPlan();

        // Başka bir işletme "galya" adını almış
        Restaurant::factory()->live()->create(['subdomain' => 'galya']);

        $response = $this->actingAs($owner)
            ->getJson(route('panel.subdomain.availability', ['label' => 'galya']))
            ->assertOk()
            ->assertJson(['status' => 'taken', 'available' => false]);

        $this->assertStringContainsString('farklı bir alt domain', $response->json('message'));
    }

    public function test_musaitlik_endpointi_rezerve_adi_reddeder(): void
    {
        [$user] = $this->ownerWithPlan();

        $this->actingAs($user)
            ->getJson(route('panel.subdomain.availability', ['label' => 'admin']))
            ->assertOk()
            ->assertJson(['status' => 'reserved', 'available' => false]);
    }

    public function test_musaitlik_endpointi_adi_normalize_eder(): void
    {
        [$user] = $this->ownerWithPlan();

        $this->actingAs($user)
            ->getJson(route('panel.subdomain.availability', ['label' => 'Galya Cafe!']))
            ->assertOk()
            ->assertJson(['status' => 'ok', 'available' => true, 'label' => 'galya-cafe']);
    }

    public function test_alinmis_ad_talep_edilemez(): void
    {
        [$user] = $this->ownerWithPlan();
        Restaurant::factory()->live()->create(['subdomain' => 'galya']);

        $this->actingAs($user)
            ->from(route('panel.dashboard'))
            ->post(route('panel.subdomain.store'), ['requested_subdomain' => 'galya'])
            ->assertSessionHasErrors('requested_subdomain');
    }

    public function test_onaya_gonderdikten_sonra_alt_domain_formu_gizlenir(): void
    {
        [$user, $restaurant] = $this->ownerWithPlan();

        $category = Category::factory()->for($restaurant)->create();
        Product::factory()->create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id]);

        $restaurant->subdomainRequests()->create([
            'user_id' => $user->id,
            'requested_subdomain' => 'galya',
            'status' => SubdomainRequest::STATUS_PENDING,
        ]);

        // Gönderim öncesi: form görünür
        $this->actingAs($user)
            ->get(route('panel.dashboard'))
            ->assertOk()
            ->assertSee('name="requested_subdomain"', false);

        $this->actingAs($user)->post(route('panel.submit'))->assertRedirect();

        // Gönderim sonrası: giriş alanı TAMAMEN yok, yerinde durum kartı var
        $this->actingAs($user)
            ->get(route('panel.dashboard'))
            ->assertOk()
            ->assertDontSee('name="requested_subdomain"', false)
            ->assertSee('Talebiniz inceleniyor');
    }

    public function test_onayda_bekleyen_restoran_yeni_talep_gonderemez(): void
    {
        [$user, $restaurant] = $this->ownerWithPlan();
        $restaurant->forceFill(['status' => Restaurant::STATUS_PENDING, 'submitted_at' => now()])->save();

        $this->actingAs($user)
            ->post(route('panel.subdomain.store'), ['requested_subdomain' => 'baskaad'])
            ->assertRedirect();

        $this->assertDatabaseCount('subdomain_requests', 0);
    }

    public function test_admin_onayi_yayin_isini_kuyruga_atar(): void
    {
        Queue::fake();
        Notification::fake();

        $admin = User::factory()->admin()->create();
        [$owner, $restaurant] = $this->ownerWithPlan();

        $request = $restaurant->subdomainRequests()->create([
            'user_id' => $owner->id,
            'requested_subdomain' => 'galya',
            'status' => SubdomainRequest::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.requests.approve', $request))
            ->assertRedirect();

        $restaurant->refresh();

        $this->assertSame('galya', $restaurant->subdomain);
        $this->assertTrue($restaurant->isLive());
        $this->assertSame(Restaurant::PUBLISH_QUEUED, $restaurant->publish_status);

        Queue::assertPushed(PublishSubdomain::class);
        Notification::assertSentTo($owner, \App\Notifications\SubdomainDecision::class);
    }

    public function test_admin_islemi_denetim_kaydina_yazilir(): void
    {
        Queue::fake();
        Notification::fake();

        $admin = User::factory()->admin()->create();
        [$owner, $restaurant] = $this->ownerWithPlan();

        $request = $restaurant->subdomainRequests()->create([
            'user_id' => $owner->id,
            'requested_subdomain' => 'galya',
            'status' => SubdomainRequest::STATUS_PENDING,
        ]);

        $this->actingAs($admin)->post(route('admin.requests.approve', $request));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'subdomain.approve',
        ]);
    }
}
