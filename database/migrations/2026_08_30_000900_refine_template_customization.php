<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - "Fiyat konumu" (price_style) mikro-varyasyonu kaldırıldı: her şablon fiyatı
 *   zaten kendi iskeletine göre en uygun yere koyuyor, dışarıdan bozmaya gerek yok.
 * - Kapak görseli olmayan ya da kapak istemeyen işletmeler için özel "arka plan
 *   rengi" (bg_color) eklendi — null ise şablonun kendi token'ı kullanılır.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (Schema::hasColumn('restaurants', 'price_style')) {
                $table->dropColumn('price_style');
            }
            $table->string('bg_color', 9)->nullable()->after('accent_color');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('bg_color');
            $table->string('price_style', 16)->default('auto')->after('corner_radius');
        });
    }
};
