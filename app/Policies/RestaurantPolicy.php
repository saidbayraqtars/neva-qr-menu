<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;

class RestaurantPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Restaurant $restaurant): bool
    {
        return $user->id === $restaurant->user_id;
    }

    public function update(User $user, Restaurant $restaurant): bool
    {
        return $user->id === $restaurant->user_id;
    }
}
