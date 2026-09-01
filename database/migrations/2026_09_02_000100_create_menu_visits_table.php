<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Canlı menü tarama/görüntülenme istatistiği.
 *
 * Menü HTML'i önbellekten servis edildiği için sayaç SUNUCU RENDER'INDA
 * artırılamaz; sayfa yüklendikten sonra istemciden atılan hafif bir "beacon"
 * isteği (`/olcum`) bu tabloyu günlük olarak toplar.
 *
 * Ham istek kaydı TUTULMAZ (KVKK): yalnızca restoran + gün + masa etiketi
 * bazında sayaç. IP/user-agent saklanmaz.
 *
 *  views    : toplam sayfa görüntülenmesi
 *  visitors : o gün ilk kez gelen tarayıcı sayısı (istemcideki gün işaretine göre)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->date('visited_on');
            // Masa etiketi yoksa NULL değil BOŞ string tutulur; NULL'lar bazı
            // veritabanlarında tekil indekste eşleşmediği için sayaç bölünürdü.
            $table->string('table_label', 24)->default('');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('visitors')->default(0);
            $table->timestamps();

            $table->unique(['restaurant_id', 'visited_on', 'table_label'], 'menu_visits_unique');
            $table->index(['restaurant_id', 'visited_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_visits');
    }
};
