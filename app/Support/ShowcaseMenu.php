<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Support\Collection;

/**
 * Şablon vitrini için VERİTABANINA YAZILMAYAN örnek menü.
 *
 * NEDEN: /qr-menu-sablonlari altındaki 40 sayfanın her biri şablonun GERÇEK
 * iskeletini render eder — ekran görüntüsü değil. Bunun için bir Restaurant +
 * kategoriler + ürünler gerekiyor ama demo verisi için tabloya satır açmak
 * yanlış olurdu: kiracı sorgularına, sayaçlara ve yedeklere karışırdı.
 *
 * Bu yüzden modeller `newInstance()` ile bellekte kurulur, ilişkiler
 * `setRelation()` ile elle bağlanır. Kayıt hiç `save()` edilmez; `exists`
 * false kalır, dolayısıyla bir kaza sonucu bile veritabanına düşemez.
 */
class ShowcaseMenu
{
    /** Demo restoranın sahte kimliği (id = 0: hiçbir gerçek kayıtla çakışmaz). */
    private const FAKE_ID = 0;

    public static function restaurant(string $templateKey): Restaurant
    {
        $restaurant = (new Restaurant)->newInstance([], false);

        $restaurant->forceFill([
            'id' => self::FAKE_ID,
            'user_id' => self::FAKE_ID,
            'name' => 'Lumina Bistro',
            'slug' => 'lumina-bistro',
            'subdomain' => 'ornek',
            'template' => $templateKey,
            'status' => Restaurant::STATUS_APPROVED,
            'tagline' => 'Akdeniz mutfağı · Kadıköy',
            'description' => 'Mevsimlik ürünlerle hazırlanan Akdeniz tabakları ve el yapımı tatlılar.',
            'address' => 'Örnek Mahallesi, Kadıköy / İstanbul',
            'phone' => '+90 555 000 00 00',
            'primary_color' => '#0F0F0F',
            'secondary_color' => '#F5F5F4',
            'accent_color' => '#C8A96A',
            'font_family' => 'Inter',
            'currency' => 'TRY',
            'locale' => 'tr',
            'show_prices' => true,
            'show_calories' => false,
            'menu_version' => 1,
            'template_settings' => [],
        ]);

        // exists=false → save()/delete() çağrılsa bile bu nesne bir satıra dokunamaz.
        $restaurant->exists = false;

        $restaurant->setRelation('categories', self::categories($restaurant));

        return $restaurant;
    }

    /**
     * Örnek kategoriler + ürünler.
     *
     * İçerik bilerek "gerçek bir menü" gibi: fiyat aralığı, indirimli ürün,
     * öne çıkan ürün ve uzun/kısa açıklama karışımı var — şablonun bütün
     * durumları (rozet, üstü çizili fiyat, taşan metin) vitrinde görünsün.
     */
    private static function categories(Restaurant $restaurant): Collection
    {
        $sections = [
            ['Başlangıçlar', 'baslangiclar', [
                ['Humus Tabağı', 'Nohut, tahin, limon; sıcak pide ile.', 180, null, false],
                ['Fırın Keçi Peyniri', 'Kekikli bal ve ceviz ile fırınlanmış.', 245, 195, true],
                ['Mevsim Salatası', 'Roka, kiraz domates, parmesan, balzamik.', 165, null, false],
            ]],
            ['Ana Yemekler', 'ana-yemekler', [
                ['Ocakta Antrikot', '250 gr dinlendirilmiş dana antrikot, tereyağlı patates.', 780, null, true],
                ['Limonlu Levrek', 'Fırınlanmış levrek fileto, kapari ve zeytinyağı sosu.', 620, 545, false],
                ['Trüflü Mantı', 'El açması hamur, trüf yağı, yoğurt.', 420, null, false],
                ['Fırın Sebze Tabağı', 'Mevsim sebzeleri, nar ekşisi, taze fesleğen.', 310, null, false],
            ]],
            ['Tatlılar', 'tatlilar', [
                ['Fıstıklı Katmer', 'Sıcak servis, kaymak eşliğinde.', 260, null, true],
                ['Tuzlu Karamel Sufle', 'Vanilyalı dondurma ile.', 235, null, false],
            ]],
            ['İçecekler', 'icecekler', [
                ['Filtre Kahve', 'Günün çekirdeği — Etiyopya.', 95, null, false],
                ['Ev Yapımı Limonata', 'Taze nane ve limon kabuğu.', 110, null, false],
                ['Türk Kahvesi', 'Orta veya sade.', 85, null, false],
            ]],
        ];

        $categories = collect();
        $categoryId = 1;
        $productId = 1;

        foreach ($sections as $order => [$name, $slug, $items]) {
            $category = (new Category)->newInstance([], false);
            $category->forceFill([
                'id' => $categoryId,
                'restaurant_id' => self::FAKE_ID,
                'name' => $name,
                'slug' => $slug,
                'sort_order' => $order,
                'is_active' => true,
            ]);
            $category->exists = false;

            $products = collect();

            foreach ($items as $i => [$productName, $description, $price, $discount, $featured]) {
                $product = (new Product)->newInstance([], false);
                $product->forceFill([
                    'id' => $productId++,
                    'restaurant_id' => self::FAKE_ID,
                    'category_id' => $category->id,
                    'name' => $productName,
                    'slug' => \Illuminate\Support\Str::slug($productName),
                    'description' => $description,
                    'price' => $price,
                    'discount_price' => $discount,
                    'is_available' => true,
                    'is_featured' => $featured,
                    'sort_order' => $i,
                ]);
                $product->exists = false;
                $product->setRelation('restaurant', $restaurant);

                $products->push($product);
            }

            $category->setRelation('products', $products);
            $category->setRelation('restaurant', $restaurant);

            $categories->push($category);
            $categoryId++;
        }

        return $categories;
    }
}
