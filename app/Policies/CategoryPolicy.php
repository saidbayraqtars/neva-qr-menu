<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function update(User $user, Category $category): bool
    {
        return $user->id === $category->restaurant->user_id;
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->update($user, $category);
    }
}
