<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\SubdomainRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OwnerPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_category(): void
    {
        [$user] = $this->ownerWithPlan();

        $this->actingAs($user)
            ->post(route('panel.categories.store'), ['name' => 'Tatlılar', 'is_active' => true])
            ->assertRedirect(route('panel.categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Tatlılar']);
    }

    public function test_submission_requires_menu_content(): void
    {
        [$user, $restaurant] = $this->ownerWithPlan();

        $restaurant->subdomainRequests()->create([
            'user_id' => $user->id,
            'requested_subdomain' => 'lumina',
            'status' => SubdomainRequest::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->from(route('panel.dashboard'))
            ->post(route('panel.submit'))
            ->assertSessionHasErrors('submit');
    }

    public function test_admin_approval_publishes_the_subdomain(): void
    {
        Queue::fake();
        Notification::fake();

        $admin = User::factory()->admin()->create();
        [$owner, $restaurant] = $this->ownerWithPlan();

        $category = Category::factory()->for($restaurant)->create();
        Product::factory()->count(3)->create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
        ]);

        $request = $restaurant->subdomainRequests()->create([
            'user_id' => $owner->id,
            'requested_subdomain' => 'lumina',
            'status' => SubdomainRequest::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.requests.approve', $request))
            ->assertRedirect();

        $restaurant->refresh();
        $this->assertSame('lumina', $restaurant->subdomain);
        $this->assertSame(Restaurant::STATUS_APPROVED, $restaurant->status);
        $this->assertTrue($restaurant->isLive());
    }

    public function test_panel_kullanicisi_baska_restoranin_masasini_silemez(): void
    {
        [$user] = $this->ownerWithPlan();
        [, $otherRestaurant] = $this->ownerWithPlan();

        $table = $otherRestaurant->tables()->create(['label' => 'Masa 1', 'sort_order' => 1]);

        $this->actingAs($user)
            ->delete(route('panel.qr.destroy', $table))
            ->assertForbidden();

        $this->assertDatabaseHas('restaurant_tables', ['id' => $table->id]);
    }
}
