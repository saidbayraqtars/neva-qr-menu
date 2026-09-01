<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * TEK SEFERLİK paketler (abonelik / deneme yok).
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Hosting Hariç Paket',
                'slug' => 'hosting-haric',
                'tagline' => 'Menü tasarımı ve kurulum — barındırma size ait',
                'price' => 3000.00,
                'interval' => 'once',
                'max_restaurants' => 1,
                'max_products' => null,
                'setup_table_limit' => null,
                'extra_table_price' => null,
                'features' => [
                    '30+ premium şablon + canlı önizleme editörü',
                    'Menü tasarımınız hazırlanıp size teslim edilir',
                    'Kendi menü linkiniz için statik QR kod üretimi',
                    'Sınırsız indirme — QR’ı doğrudan masaya bastırın',
                    'Masa/bahçe takibi ve alt domain bu pakette yoktur',
                ],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Hosting Dahil Paket',
                'slug' => 'hosting-dahil',
                'tagline' => 'Her değişiklik anında alt domaininize yansır',
                'price' => 6000.00,
                'interval' => 'once',
                'max_restaurants' => 1,
                'max_products' => null,
                'setup_table_limit' => null,
                'extra_table_price' => null,
                'features' => [
                    '30+ profesyonel şablon',
                    'Markalı alt domain (isim.neva-qr.com)',
                    'Şablon değişikliği ve her düzenleme ANINDA canlıya geçer',
                    'SSL + güvenli barındırma dahil',
                    'Öncelikli destek',
                ],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Fiziksel QR Basım & Kurulum Paketi',
                'slug' => 'fiziksel-qr',
                'tagline' => '15 masaya kadar baskı + yerinde kurulum',
                'price' => 10000.00,
                'interval' => 'once',
                'max_restaurants' => 1,
                'max_products' => null,
                'setup_table_limit' => 15,
                'extra_table_price' => 300.00,
                'features' => [
                    'Hosting Dahil Paket’in tamamı',
                    '15 masaya kadar fiziksel QR baskı',
                    'Masa standları + yerinde kurulum',
                    '15 masadan sonrası: masa başı +300 ₺',
                ],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        // Eski aylık planları pasifleştir (varsa).
        Plan::whereIn('slug', ['baslangic', 'profesyonel'])->update(['is_active' => false]);
    }
}
