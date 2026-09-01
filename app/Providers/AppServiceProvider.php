<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Restaurant;
use App\Policies\CategoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RestaurantPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::policy(Restaurant::class, RestaurantPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
    }
}
