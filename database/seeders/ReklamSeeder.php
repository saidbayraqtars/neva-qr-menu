<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuVisit;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Reklam/tanıtım çekimi hesabı — "Meydan Kafe & Restoran" (meydan.nevaqr.com).
 *
 * Çekimi yapan kişi bu hesapla panele girip menüyü, tasarım editörünü, QR
 * ekranını ve istatistikleri gerçek veriyle gösterebilsin diye kurgulandı:
 * fotoğraflı ürünler, 10 masa ve 30 günlük ziyaret geçmişi hazır gelir.
 *
 * ÜRETİMDE GÜVENLİ: yalnızca kendi kayıtlarına dokunur (updateOrCreate) ve
 * defalarca çalıştırılabilir. Şifre depoda TUTULMAZ; NEVA_REKLAM_SIFRE
 * ortam değişkeninden okunur, yoksa rastgele üretilip konsola basılır.
 */
class ReklamSeeder extends Seeder
{
    public const EMAIL = 'demo@nevaqr.com';

    public const SUBDOMAIN = 'meydan';

    public function run(): void
    {
        // Yayın kolonları kütle atamaya kapalı (güvenlik); demo verisi bunları
        // bilerek doldurduğu için seeder süresince korumayı kaldırıyoruz.
        Model::unguarded(fn () => $this->seed());
    }

    private function seed(): void
    {
        $password = (string) (env('NEVA_REKLAM_SIFRE') ?: Str::password(14, symbols: false));

        $owner = User::updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Meydan Kafe',
                'password' => $password,
                'role' => User::ROLE_OWNER,
                'phone' => '05321234567',
                'must_change_password' => false,
            ]
        );

        $owner->forceFill([
            'email_verified_at' => $owner->email_verified_at ?? now(),
            'password_setup_token' => null,
            'password_setup_expires_at' => null,
        ])->save();

        $this->subscription($owner);

        $restaurant = Restaurant::updateOrCreate(
            ['slug' => self::SUBDOMAIN],
            [
                'user_id' => $owner->id,
                'name' => 'Meydan Kafe & Restoran',
                'subdomain' => self::SUBDOMAIN,
                'template' => 'grid-showcase',
                'qr_design' => 'artdeco',
                'status' => Restaurant::STATUS_APPROVED,
                'tagline' => 'Sabah kahvesinden akşam sofrasına',
                'description' => 'Günlük taze ürünler, kendi kavurduğumuz çekirdek ve mevsiminde pişen tabaklar.',
                'accent_color' => '#0F766E',
                'heading_color' => '#134E4A',
                'font_family' => 'DM Sans',
                'logo_size' => 'medium',
                'currency' => 'TRY',
                'show_prices' => true,
                'show_calories' => true,
                // Şablona özel "gelişmiş dokunuşlar" artık kolon değil, JSON içinde.
                'template_settings' => ['grid-showcase' => [
                    'accent_color' => '#0F766E',
                    'heading_color' => '#134E4A',
                    'font_family' => 'DM Sans',
                    'bg_pattern' => 'solid',
                    'entry_anim' => 'fade',
                    'image_style' => 'hero',
                    'corner_radius' => 'modern',
                    'heading_weight' => 'bold',
                ]],
                'address' => 'Cumhuriyet Meydanı No:7, Kadıköy / İstanbul',
                'phone' => '02165550142',
                'whatsapp' => '905321234567',
                'instagram' => 'meydankafe',
                'opening_hours' => [
                    'pazartesi' => '08:00 - 23:00',
                    'sali' => '08:00 - 23:00',
                    'carsamba' => '08:00 - 23:00',
                    'persembe' => '08:00 - 23:00',
                    'cuma' => '08:00 - 00:00',
                    'cumartesi' => '09:00 - 00:00',
                    'pazar' => '09:00 - 22:00',
                ],
                'publish_status' => Restaurant::PUBLISH_LIVE,
                'publish_error' => null,
                'dns_provisioned_at' => now(),
                'verified_at' => now(),
                'submitted_at' => now()->subDays(31),
                'approved_at' => now()->subDays(30),
                'published_at' => now()->subDays(30),
                'rejection_reason' => null,
            ]
        );

        $this->seedMenu($restaurant);
        $this->seedBrandAssets($restaurant);
        $this->seedProductImages($restaurant);
        $this->seedTables($restaurant);
        $this->seedVisits($restaurant);

        // Menü HTML'i restaurants.menu_version ile anahtarlanıyor; seeder içerik
        // değiştirdiği için sürümü artırıp eski önbelleği geçersiz kılıyoruz.
        $restaurant->forceFill(['menu_version' => (int) $restaurant->menu_version + 1])->save();

        $this->command?->info('Reklam hesabı hazır.');
        $this->command?->line('  E-posta : '.self::EMAIL);
        $this->command?->line('  Şifre   : '.$password);
        $this->command?->line('  Menü    : https://'.self::SUBDOMAIN.'.'.config('neva.root_domain'));
    }

    private function subscription(User $user): void
    {
        $plan = Plan::where('slug', 'hosting-dahil')->first();

        Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan?->id,
                'status' => Subscription::STATUS_ACTIVE,
                'amount' => $plan?->price ?? 6000.00,
                'currency' => 'TRY',
                'interval' => 'once',
                'provider' => 'manual',
                'current_period_starts_at' => now()->subDays(30),
                'current_period_ends_at' => null,
            ]
        );
    }

    private function seedMenu(Restaurant $restaurant): void
    {
        $order = 0;

        foreach ($this->menu() as $categoryName => $items) {
            $category = Category::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'sort_order' => $order++, 'is_active' => true]
            );

            $pOrder = 0;
            foreach ($items as $item) {
                [$name, $desc, $price] = $item;

                Product::updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'slug' => Str::slug($name)],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'description' => $desc,
                        'price' => $price,
                        'discount_price' => $item[3] ?? null,
                        'tags' => $item[4] ?? null,
                        'calories' => $item[5] ?? null,
                        'is_available' => true,
                        'is_featured' => $pOrder === 0,
                        'sort_order' => $pOrder++,
                    ]
                );
            }
        }
    }

    /** Logo + kapak görseli. */
    private function seedBrandAssets(Restaurant $restaurant): void
    {
        $disk = Storage::disk(config('neva.uploads.disk'));

        // İşletmenin KENDİ monogramı — Neva logosu kullanılırsa reklamda
        // "müşterinin menüsünde Neva logosu" gibi görünür, kafa karıştırır.
        // Fotoğrafı olmayan ürün kartlarında da bu logo fallback olarak çıkar.
        $logo = database_path('seeders/assets/meydan/logo.png');
        if (is_file($logo)) {
            $path = "restaurants/{$restaurant->id}/brand/logo.png";
            $disk->put($path, file_get_contents($logo));
            $restaurant->update(['logo_path' => $path]);
        }

        $cover = public_path('img/food/salmon.jpg');
        if (is_file($cover)) {
            $path = "restaurants/{$restaurant->id}/brand/cover.jpg";
            $disk->put($path, file_get_contents($cover));
            $restaurant->update(['cover_path' => $path]);
        }
    }

    /** Ürün fotoğrafları — depodaki mevcut demo görselleri yeniden kullanılır. */
    private function seedProductImages(Restaurant $restaurant): void
    {
        $disk = Storage::disk(config('neva.uploads.disk'));

        $map = [
            'flat-white' => database_path('seeders/assets/lumina/flat-white.jpg'),
            'filtre-kahve' => database_path('seeders/assets/lumina/filtre-kahve.jpg'),
            'cortado' => database_path('seeders/assets/lumina/cortado.jpg'),
            'serpme-kahvalti' => database_path('seeders/assets/lumina/serpme-kahvalti.jpg'),
            'menemen' => database_path('seeders/assets/lumina/menemen.jpg'),
            'san-sebastian-cheesecake' => database_path('seeders/assets/lumina/san-sebastian-cheesecake.jpg'),
            'fistikli-baklava' => database_path('seeders/assets/lumina/fistikli-baklava.jpg'),
            'burrata-salata' => public_path('img/food/burrata.jpg'),
            'firin-somon' => public_path('img/food/salmon.jpg'),
            'barbeku-kaburga' => public_path('img/food/ribs.jpg'),
        ];

        foreach ($map as $slug => $source) {
            if (! is_file($source)) {
                continue;
            }

            $product = $restaurant->products()->where('slug', $slug)->first();
            if (! $product) {
                continue;
            }

            $path = "restaurants/{$restaurant->id}/products/{$product->id}.jpg";
            $disk->put($path, file_get_contents($source));
            $product->update(['image_path' => $path]);
        }
    }

    private function seedTables(Restaurant $restaurant): void
    {
        foreach ($this->tableLabels() as $label) {
            $restaurant->tables()->firstOrCreate(
                ['label' => $label],
                ['is_active' => true]
            );
        }
    }

    /**
     * 30 günlük ziyaret geçmişi — istatistik ekranı boş grafik göstermesin.
     * Sayılar deterministik (sabit tohum): seeder tekrar çalışsa da aynı tablo.
     */
    private function seedVisits(Restaurant $restaurant): void
    {
        mt_srand(20260906 + $restaurant->id);

        $labels = array_merge($this->tableLabels(), ['']); // '' = masasız (ana QR)

        for ($i = 29; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $weekendBoost = $day->isWeekend() ? 1.6 : 1.0;

            foreach ($labels as $label) {
                $views = (int) round(mt_rand(4, 22) * $weekendBoost);
                $visitors = (int) round($views * (mt_rand(60, 85) / 100));

                // updateOrCreate KULLANILMAZ: visited_on "date" cast'li olduğu için
                // arama değeri ("Y-m-d") kayıtlı değere ("Y-m-d 00:00:00") eşleşmez;
                // her çalıştırmada tekil indeksi patlatırdı. whereDate ile eşleştiriyoruz.
                $existing = MenuVisit::query()
                    ->where('restaurant_id', $restaurant->id)
                    ->where('table_label', $label)
                    ->whereDate('visited_on', $day->toDateString())
                    ->first();

                $values = ['views' => $views, 'visitors' => max(1, $visitors)];

                $existing
                    ? $existing->update($values)
                    : MenuVisit::create($values + [
                        'restaurant_id' => $restaurant->id,
                        'visited_on' => $day->toDateString(),
                        'table_label' => $label,
                    ]);
            }
        }

        mt_srand();
    }

    /** @return array<int, string> */
    private function tableLabels(): array
    {
        return ['Masa 1', 'Masa 2', 'Masa 3', 'Masa 4', 'Masa 5', 'Masa 6', 'Bahçe 1', 'Bahçe 2', 'Teras 1', 'Teras 2'];
    }

    /**
     * @return array<string, array<int, array{0:string,1:string,2:int|float,3?:float|null,4?:array<int,string>|null,5?:int|null}>>
     */
    private function menu(): array
    {
        return [
            'Kahvaltı' => [
                ['Serpme Kahvaltı', '2 kişilik, 18 çeşit, sınırsız çay', 690, null, ['şefin önerisi'], 1250],
                ['Menemen', 'Köy yumurtası, tereyağı, sivri biber', 195, null, null, 420],
                ['Bal & Kaymak', 'Süzme bal, manda kaymağı, taze ekmek', 210, null, ['vejetaryen'], 480],
                ['Avokadolu Ekmek', 'Ekşi maya ekmek, avokado, poşe yumurta', 245, 215, ['vejetaryen'], 390],
                ['Sahanda Sucuklu Yumurta', 'Kangal sucuk, iki yumurta', 185, null, null, 460],
            ],
            'Kahveler' => [
                ['Flat White', 'Çift shot espresso, ipeksi süt', 105, null, ['şefin önerisi'], 150],
                ['Filtre Kahve', 'Günün çekirdeği, V60 demleme', 90, null, null, 5],
                ['Cortado', 'Espresso ve az süt', 95, null, null, 90],
                ['Latte', 'Espresso, buharlanmış süt', 110, null, null, 190],
                ['Espresso', 'Tek shot, yoğun gövde', 75, null, null, 5],
                ['Türk Kahvesi', 'Közde pişirme, lokum ile', 70, null, null, 40],
            ],
            'Başlangıçlar' => [
                ['Burrata Salata', 'Burrata, cherry domates, taze fesleğen, pesto', 285, null, ['vejetaryen', 'şefin önerisi'], 420],
                ['Günün Çorbası', 'Mevsiminde, günlük hazırlanır', 120, null, null, 210],
                ['Humus', 'Nohut, tahin, sızma zeytinyağı', 130, null, ['vegan'], 320],
                ['Sezar Salata', 'Izgara tavuk, parmesan, kruton', 265, 235, null, 480],
            ],
            'Ana Yemekler' => [
                ['Fırın Somon', 'Limonlu tereyağı sos, mevsim sebzeleri', 620, null, ['şefin önerisi'], 610],
                ['Barbekü Kaburga', 'Düşük ısıda 8 saat, ev yapımı barbekü sos', 680, 599, ['şefin önerisi'], 980],
                ['Meydan Burger', '180 gr dana köfte, cheddar, brioche ekmek', 420, null, null, 850],
                ['Tavuk Şiş', 'Marine tavuk göğsü, közde', 340, null, null, 520],
                ['Kremalı Mantar Makarna', 'Taze mantar, krema, parmesan', 310, null, ['vejetaryen'], 720],
            ],
            'Tatlılar' => [
                ['San Sebastián Cheesecake', 'Yanık peynirli klasik', 185, null, ['şefin önerisi'], 540],
                ['Fıstıklı Baklava', '4 dilim, Antep fıstığı', 195, null, null, 620],
                ['Fırın Sütlaç', 'Ev yapımı, fırında kızarmış', 130, null, ['vejetaryen'], 380],
                ['Profiterol', 'Sıcak çikolata sos', 155, null, ['vejetaryen'], 590],
            ],
        ];
    }
}
