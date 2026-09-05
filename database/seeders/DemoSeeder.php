<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Restaurant modelinde yayın kolonları kütle atamaya kapalı (güvenlik).
        // Demo verisi bunları bilerek doldurduğu için seeder süresince korumayı kaldırıyoruz.
        Model::unguarded(fn () => $this->seed());
    }

    private function seed(): void
    {
        // Platform yöneticisi
        User::updateOrCreate(
            ['email' => 'admin@nevaqr.com'],
            ['name' => 'Neva Admin', 'password' => 'password', 'role' => User::ROLE_ADMIN]
        );

        // ---- Demo 1: Lumina Bistro & Lounge (kahve) ----
        $owner = User::updateOrCreate(
            ['email' => 'sahip@nevaqr.com'],
            ['name' => 'Lumina Sahibi', 'password' => 'password', 'role' => User::ROLE_OWNER]
        );
        $this->subscription($owner);

        $lumina = Restaurant::updateOrCreate(
            ['slug' => 'lumina'],
            [
                'user_id' => $owner->id,
                'name' => 'Lumina Bistro & Lounge',
                'subdomain' => 'lumina',
                'qr_design' => 'artdeco',
                'template' => 'dark-prestige',
                'status' => Restaurant::STATUS_APPROVED,
                'tagline' => 'Üçüncü nesil kahve ve mevsim mutfağı',
                'accent_color' => '#C8A96A',
                'font_family' => 'Playfair Display',
                'logo_size' => 'medium',
                'currency' => 'TRY',
                'show_prices' => true,
                'show_calories' => false,
                'template_settings' => ['dark-prestige' => ['accent_color' => '#C8A96A', 'font_family' => 'Playfair Display', 'heading_weight' => 'regular']],
                'approved_at' => now(),
                'published_at' => now(),
                'submitted_at' => now()->subDay(),
            ]
        );

        $this->seedMenu($lumina, [
            'Kahveler' => [
                ['Flat White', 'Çift shot espresso, ipeksi süt', 95],
                ['Filtre Kahve', 'Günün çekirdeği, V60', 85],
                ['Cortado', 'Espresso ve az süt', 90],
            ],
            'Tatlılar' => [
                ['San Sebastián Cheesecake', 'Yanık peynirli klasik', 165],
                ['Fıstıklı Baklava', 'Antep fıstığı, tel kadayıf', 145],
            ],
            'Kahvaltı' => [
                ['Serpme Kahvaltı', '2 kişilik, mevsim ürünleri', 620],
                ['Menemen', 'Köy yumurtası, tereyağı', 190],
            ],
        ]);

        // ---- Demo 2: Anadolu Ocakbaşı (kebapçı — 5 kategori × 10 ürün) ----
        $kebapOwner = User::updateOrCreate(
            ['email' => 'ocakbasi@nevaqr.com'],
            ['name' => 'Ocakbaşı Sahibi', 'password' => 'password', 'role' => User::ROLE_OWNER]
        );
        $this->subscription($kebapOwner);

        $ocakbasi = Restaurant::updateOrCreate(
            ['slug' => 'ocakbasi'],
            [
                'user_id' => $kebapOwner->id,
                'name' => 'Anadolu Ocakbaşı',
                'subdomain' => 'ocakbasi',
                'template' => 'sunset-orange',
                'status' => Restaurant::STATUS_APPROVED,
                'tagline' => 'Meşe kömüründe, ustaların elinden',
                'description' => 'Günlük taze et, el açması lavaş, közde pişen mezeler.',
                'accent_color' => '#C24E1B',
                'font_family' => 'Bitter',
                'logo_size' => 'medium',
                'currency' => 'TRY',
                'show_prices' => true,
                'show_calories' => false,
                'template_settings' => ['sunset-orange' => [
                    'accent_color' => '#C24E1B', 'font_family' => 'Bitter',
                    'bg_pattern' => 'noise', 'entry_anim' => 'slide', 'heading_weight' => 'bold',
                ]],
                'address' => 'Kebapçılar Çarşısı No:14, Gaziantep',
                'whatsapp' => '905321234567',
                'instagram' => 'anadoluocakbasi',
                'approved_at' => now(),
                'published_at' => now(),
                'submitted_at' => now()->subDays(2),
            ]
        );

        $this->seedMenu($ocakbasi, $this->kebapMenu());

        foreach (['Masa 1', 'Masa 2', 'Bahçe 1', 'Bahçe 2'] as $label) {
            $ocakbasi->tables()->firstOrCreate(['label' => $label]);
        }

        $lumina->tables()->firstOrCreate(['label' => 'Masa 1']);
        $lumina->tables()->firstOrCreate(['label' => 'Masa 2']);

        // Demo logoları — ürün fotoğrafı olmayan kartlarda akıllı fallback için.
        $this->seedLogo($lumina);
        $this->seedLogo($ocakbasi);

        // Lumina ürün fotoğrafları (görsel odaklı şablonların düzgün görünmesi için).
        $this->seedProductImages($lumina, 'lumina');
    }

    /** database/seeders/assets/{$folder}/{product-slug}.jpg → ilgili ürünün image_path'i. */
    private function seedProductImages(Restaurant $restaurant, string $folder): void
    {
        $dir = database_path("seeders/assets/{$folder}");
        if (! is_dir($dir)) {
            return;
        }

        $disk = Storage::disk(config('neva.uploads.disk'));

        foreach (glob("{$dir}/*.jpg") as $file) {
            $slug = pathinfo($file, PATHINFO_FILENAME);
            $product = $restaurant->products()->where('slug', $slug)->first();
            if (! $product) {
                continue;
            }

            $path = "restaurants/{$restaurant->id}/products/{$product->id}.jpg";
            $disk->put($path, file_get_contents($file));
            $product->update(['image_path' => $path]);
        }
    }

    private function seedLogo(Restaurant $restaurant): void
    {
        $src = public_path('img/nevalogo.png');
        if (! is_file($src)) {
            return;
        }

        $path = "restaurants/{$restaurant->id}/brand/logo.png";
        Storage::disk(config('neva.uploads.disk'))->put($path, file_get_contents($src));
        $restaurant->update(['logo_path' => $path]);
    }

    private function subscription(User $user): void
    {
        $plan = Plan::where('slug', 'hosting-dahil')->first();

        Subscription::firstOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan?->id,
                'status' => Subscription::STATUS_ACTIVE,
                'amount' => $plan?->price ?? 6000.00,
                'currency' => 'TRY',
                'interval' => 'once',
                'provider' => 'manual',
                'current_period_starts_at' => now(),
                'current_period_ends_at' => null,
            ]
        );
    }

    /**
     * @param  array<string, array<int, array{0:string,1:string,2:int|float,3?:float|null,4?:array<int,string>|null,5?:int|null}>>  $menu
     */
    private function seedMenu(Restaurant $restaurant, array $menu): void
    {
        $order = 0;
        foreach ($menu as $categoryName => $items) {
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

    /** 5 kategori × 10 ürün — temsili ocakbaşı menüsü. */
    private function kebapMenu(): array
    {
        return [
            'Mezeler & Başlangıçlar' => [
                ['Humus', 'Nohut, tahin, sızma zeytinyağı', 85, null, ['vejetaryen']],
                ['Haydari', 'Süzme yoğurt, sarımsak, nane', 75, null, ['vejetaryen']],
                ['Acılı Ezme', 'Domates, biber, ceviz, nar ekşisi', 70, null, ['acı']],
                ['Şakşuka', 'Kızarmış patlıcan, kabak, domates sos', 90],
                ['Muhammara', 'Ceviz, biber salçası, pul biber', 95, null, ['acı']],
                ['Atom', 'Yoğurt, közlenmiş acı biber, sarımsaklı tereyağı', 80, null, ['acı']],
                ['Közlenmiş Patlıcan Salatası', 'Köz patlıcan, sarımsaklı yoğurt', 85],
                ['Yoğurtlu Semizotu', 'Semizotu, yoğurt, sarımsak', 70, null, ['vejetaryen']],
                ['Zeytinyağlı Yaprak Sarma', 'İç pilavlı asma yaprağı — 6 adet', 95],
                ['İçli Köfte', 'Bulgur köftesi, kıymalı iç — 2 adet', 110],
            ],
            'Izgara & Kebaplar' => [
                ['Adana Kebap', 'El açması acılı kıyma, meşe közünde', 260, null, ['acı', 'şefin önerisi'], 640],
                ['Urfa Kebap', 'Acısız kıyma kebabı, közde', 250, null, null, 610],
                ['Beyti Sarma', 'Kıyma kebabı, yufka, domates sos, yoğurt', 320],
                ['Ali Nazik', 'Köz patlıcan püresi üzerine kuşbaşı', 340, null, ['şefin önerisi']],
                ['Kuzu Şiş', 'Marine kuzu kuşbaşı, közde', 380],
                ['Tavuk Şiş', 'Marine tavuk göğsü, közde', 240, 210],
                ['Kuzu Pirzola', '4 parça kuzu pirzola', 420, 375, ['şefin önerisi']],
                ['Çöp Şiş', 'İnce kuzu şiş — 6 adet', 300],
                ['Patlıcanlı Kebap', 'Kıyma kebabı, közlenmiş patlıcan dizme', 290],
                ['Kanat Izgara', 'Marine tavuk kanat — 6 adet', 210],
            ],
            'Pide & Lahmacun' => [
                ['Lahmacun', 'İnce hamur, kıymalı harç, maydanoz', 60],
                ['Kıymalı Pide', 'Kıyma, biber, domates', 150],
                ['Kaşarlı Pide', 'Bol kaşar', 140, null, ['vejetaryen']],
                ['Kuşbaşılı Pide', 'Kuşbaşı kuzu eti', 190],
                ['Kaşarlı Sucuklu Pide', 'Sucuk, kaşar', 175],
                ['Karışık Pide', 'Kıyma, kaşar, sucuk, yumurta', 200, 170],
                ['Kıymalı Yumurtalı Pide', 'Kıyma, yumurta', 165],
                ['Bafra Pidesi', 'Kapalı, kıymalı', 180],
                ['Ispanaklı Pide', 'Ispanak, lor peyniri', 145, null, ['vejetaryen']],
                ['Peynirli Pide', 'Beyaz peynir, maydanoz', 135, null, ['vejetaryen']],
            ],
            'Yanında & Salatalar' => [
                ['Çoban Salata', 'Domates, salatalık, biber, soğan', 65, null, ['vejetaryen']],
                ['Gavurdağı Salatası', 'Ceviz, nar ekşisi, domates', 85, null, ['vejetaryen']],
                ['Roka Salatası', 'Roka, cherry domates, limon', 70, null, ['vejetaryen']],
                ['Mevsim Salata', 'Karışık taze yeşillik', 60, null, ['vejetaryen']],
                ['Közlenmiş Biber & Domates', 'Köz sebze tabağı', 75],
                ['Sumaklı Soğan', 'Sumakla ovulmuş kıvırcık soğan', 30],
                ['Bulgur Pilavı', 'Domatesli, tereyağlı bulgur', 55],
                ['Fırın Patates', 'Tereyağlı fırın patates', 60],
                ['Közde Sarımsak', 'Bütün fırınlanmış sarımsak', 25],
                ['Turşu Tabağı', 'Karışık ev turşusu', 55],
            ],
            'Tatlı & İçecek' => [
                ['Künefe', 'Antep fıstıklı, tel kadayıf, kaymak', 140, null, ['şefin önerisi']],
                ['Fıstıklı Baklava', '4 dilim, Antep fıstığı', 150],
                ['Cevizli Kadayıf', 'Tel kadayıf, ceviz, şerbet', 120],
                ['Fırın Sütlaç', 'Ev yapımı, fırında kızarmış', 80],
                ['Kazandibi', 'Karamelize süt tatlısı', 85],
                ['Ayran', 'Taze, köpüklü — 30 cl', 25],
                ['Şalgam Suyu', 'Acılı / acısız', 30, null, ['acı']],
                ['Şıra', 'Ev yapımı üzüm şırası', 35],
                ['Türk Kahvesi', 'Közde pişirme', 40],
                ['Demleme Çay', 'İnce belli bardak', 15],
            ],
        ];
    }
}
