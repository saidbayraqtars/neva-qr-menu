<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Gelişmiş Dokunuşlar" ayarları artık ŞABLONA ÖZEL saklanır.
 * restaurants.template_settings = { "<template-key>": { accent_color, font_family,
 *   bg_color, bg_pattern, entry_anim, image_style, corner_radius, heading_weight, text_size } }
 *
 * Böylece kullanıcı A şablonunda yaptığı dokunuşlar B şablonuna sızmaz;
 * A'ya geri dönünce ayarları hazır gelir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->json('template_settings')->nullable()->after('font_family');
        });

        // Mevcut düz sütun değerlerini aktif şablonun altına taşı.
        foreach (DB::table('restaurants')->get() as $r) {
            $bucket = array_filter([
                'accent_color' => $r->accent_color ?? null,
                'font_family' => $r->font_family ?? null,
                'bg_color' => $r->bg_color ?? null,
                'bg_pattern' => $r->bg_pattern ?? null,
                'entry_anim' => $r->entry_anim ?? null,
                'image_style' => $r->image_style ?? null,
                'corner_radius' => $r->corner_radius ?? null,
            ], fn ($v) => $v !== null && $v !== '' && $v !== 'auto' && $v !== 'solid' && $v !== 'fade');

            if ($bucket) {
                DB::table('restaurants')->where('id', $r->id)->update([
                    'template_settings' => json_encode([$r->template => $bucket]),
                ]);
            }
        }

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['bg_color', 'bg_pattern', 'entry_anim', 'image_style', 'corner_radius']);
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('bg_color', 9)->nullable();
            $table->string('bg_pattern', 16)->default('solid');
            $table->string('entry_anim', 16)->default('fade');
            $table->string('image_style', 16)->default('auto');
            $table->string('corner_radius', 16)->default('auto');
            $table->dropColumn('template_settings');
        });
    }
};
