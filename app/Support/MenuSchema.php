<?php

namespace App\Support;

use App\Models\Restaurant;

/**
 * schema.org JSON-LD üreticileri.
 *
 * Kiracı menüleri için Restaurant + hasMenu/Menu/MenuSection/MenuItem çıkarır;
 * Google zengin sonuçlarında menü kartı gösterebilmesi bu işaretlemeye bağlıdır.
 */
class MenuSchema
{
    public static function forRestaurant(Restaurant $restaurant, $categories): array
    {
        $sections = [];

        foreach ($categories as $category) {
            $items = [];

            foreach ($category->products as $product) {
                $item = [
                    '@type' => 'MenuItem',
                    'name' => $product->name,
                ];

                if (filled($product->description)) {
                    $item['description'] = \Illuminate\Support\Str::limit(strip_tags($product->description), 300);
                }

                if ($restaurant->show_prices && $product->price !== null) {
                    $item['offers'] = [
                        '@type' => 'Offer',
                        'price' => (string) ($product->discount_price ?? $product->price),
                        'priceCurrency' => $restaurant->currency ?: 'TRY',
                    ];
                }

                $items[] = $item;
            }

            $sections[] = array_filter([
                '@type' => 'MenuSection',
                'name' => $category->name,
                'hasMenuItem' => $items ?: null,
            ]);
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $restaurant->name,
            'url' => tenant_domain($restaurant),
        ];

        if (filled($restaurant->tagline)) {
            $data['description'] = $restaurant->tagline;
        }

        if (filled($restaurant->address)) {
            $data['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $restaurant->address];
        }

        if (filled($restaurant->phone)) {
            $data['telephone'] = $restaurant->phone;
        }

        if (filled($restaurant->logo_path)) {
            $data['image'] = asset('storage/'.$restaurant->logo_path);
        }

        $sameAs = array_values(array_filter([
            filled($restaurant->instagram) ? $restaurant->instagram : null,
            filled($restaurant->website) ? $restaurant->website : null,
        ]));

        if ($sameAs) {
            $data['sameAs'] = $sameAs;
        }

        if ($sections) {
            $data['hasMenu'] = [
                '@type' => 'Menu',
                'name' => $restaurant->name.' Menü',
                'hasMenuSection' => $sections,
            ];
        }

        return $data;
    }

    /** Pazarlama sitesi için kurumsal işaretleme. */
    public static function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('neva.brand.name'),
            'url' => url('/'),
            'logo' => asset(config('neva.brand.logo')),
            'email' => config('neva.brand.support_email'),
        ];
    }

    /** Fiyatlandırma sayfası için ürün/teklif işaretlemesi. */
    public static function offers($plans): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => config('neva.brand.name'),
            'description' => config('neva.seo.default_description'),
            'offers' => collect($plans)->map(fn ($plan) => [
                '@type' => 'Offer',
                'name' => $plan->name,
                'price' => (string) $plan->price,
                'priceCurrency' => 'TRY',
                'url' => route('pricing'),
            ])->all(),
        ];
    }
}
