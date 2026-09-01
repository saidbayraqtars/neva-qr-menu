<?php

namespace Tests;

use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Belirli bir paketi olan işletme sahibi + restoranı üretir.
     * Paket yetkileri config/neva.php › plan_features üzerinden çalışır.
     *
     * @return array{0: User, 1: Restaurant}
     */
    protected function ownerWithPlan(string $planSlug = 'hosting-dahil', array $restaurantState = []): array
    {
        $user = User::factory()->create();

        $plan = Plan::firstOrCreate(
            ['slug' => $planSlug],
            [
                'name' => ucfirst($planSlug),
                'price' => 1000,
                'interval' => 'once',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'amount' => $plan->price,
            'currency' => 'TRY',
            'interval' => 'once',
            'provider' => 'bank_transfer',
            'current_period_starts_at' => now(),
        ]);

        $restaurant = Restaurant::factory()->for($user)->create($restaurantState);

        return [$user, $restaurant];
    }
}
