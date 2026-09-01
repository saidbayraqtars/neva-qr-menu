<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Restaurant;
use App\Models\SubdomainRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_bootstraps_a_draft_restaurant_and_trial(): void
    {
        $response = $this->post('/register', [
            'name' => 'Deniz',
            'business_name' => 'Lumina Bistro',
            'email' => 'deniz@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('panel.dashboard'));

        $user = User::firstWhere('email', 'deniz@example.com');
        $this->assertNotNull($user);
        $this->assertSame('Lumina Bistro', $user->restaurants()->value('name'));
        $this->assertSame('trialing', $user->subscriptions()->value('status'));
    }

    public function test_owner_can_create_a_category(): void
    {
        $user = User::factory()->create();
        Restaurant::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('panel.categories.store'), ['name' => 'Tatlılar', 'is_active' => true])
            ->assertRedirect(route('panel.categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Tatlılar']);
    }

    public function test_submission_requires_menu_content(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->for($user)->create();
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
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->for($owner)->create();
        Category::factory()->for($restaurant)->has(\App\Models\Product::factory()->count(3)->state([
            'restaurant_id' => $restaurant->id,
        ]))->create();

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
}
