<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\Restaurant;
use App\Observers\MenuVersionObserver;
use App\Policies\CategoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RestaurantPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
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

        // Menü değişince canlı önbellek anahtarı yenilensin.
        Restaurant::observe(MenuVersionObserver::class);
        Category::observe(MenuVersionObserver::class);
        Product::observe(MenuVersionObserver::class);

        $this->registerRateLimiters();
        $this->shareUnreadCounts();
    }

    /**
     * Kötüye kullanıma açık uçlar için hız sınırları.
     * Rotalarda ->middleware('throttle:<isim>') ile kullanılır.
     */
    private function registerRateLimiters(): void
    {
        // Kayıt başvurusu: IP başına saatte 5
        RateLimiter::for('register', fn (Request $r) => Limit::perHour(5)->by($r->ip()));

        // İletişim formu: IP başına saatte 5
        RateLimiter::for('contact', fn (Request $r) => Limit::perHour(5)->by($r->ip()));

        // Alt domain müsaitlik sorgusu: dakikada 30 (yazarken çağrılıyor)
        RateLimiter::for('subdomain-check', fn (Request $r) => Limit::perMinute(30)->by($r->user()?->id ?: $r->ip()));

        // Alt domain talebi: saatte 10
        RateLimiter::for('subdomain-request', fn (Request $r) => Limit::perHour(10)->by($r->user()?->id ?: $r->ip()));

        // QR / PDF üretimi CPU-yoğun: dakikada 20
        RateLimiter::for('render', fn (Request $r) => Limit::perMinute(20)->by($r->user()?->id ?: $r->ip()));

        // Tasarım önizlemesi iframe'i: dakikada 120
        RateLimiter::for('preview', fn (Request $r) => Limit::perMinute(120)->by($r->user()?->id ?: $r->ip()));

        // Mesaj gönderimi: dakikada 20
        RateLimiter::for('messages', fn (Request $r) => Limit::perMinute(20)->by($r->user()?->id ?: $r->ip()));
    }

    /** Panel ve admin menüsündeki okunmamış mesaj rozetleri. */
    private function shareUnreadCounts(): void
    {
        View::composer('components.app-layout', function ($view) {
            $user = auth()->user();

            if (! $user) {
                return;
            }

            $view->with('unreadMessages', $user->isAdmin()
                ? (int) Conversation::sum('unread_for_admin')
                : (int) Conversation::where('user_id', $user->id)->sum('unread_for_user'));
        });
    }
}
