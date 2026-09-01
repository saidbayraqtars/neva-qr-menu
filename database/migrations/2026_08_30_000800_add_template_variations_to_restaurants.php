<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Şablon içi mikro-özelleştirme katmanı ("Gelişmiş Dokunuşlar").
 * Hepsi opsiyonel — varsayılanları şablonun kendi kimliğini korur ('auto' /
 * 'solid' / 'fade'), böylece hızlı kurulum yapan kullanıcı hiç dokunmaz.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('bg_pattern', 16)->default('solid')->after('accent_color');     // solid | dots | noise | glow
            $table->string('entry_anim', 16)->default('fade')->after('bg_pattern');         // fade | slide | pop
            $table->string('image_style', 16)->default('auto')->after('entry_anim');        // auto | small | hero | hidden
            $table->string('corner_radius', 16)->default('auto')->after('image_style');     // auto | sharp | modern | round
            $table->string('price_style', 16)->default('auto')->after('corner_radius');     // auto | right | stacked | badge
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['bg_pattern', 'entry_anim', 'image_style', 'corner_radius', 'price_style']);
        });
    }
};
