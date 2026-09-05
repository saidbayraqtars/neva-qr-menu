<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "restaurants" tablosu = platformdaki her kiracı (tenant).
 * Alt domain (subdomain) çözümlemesi bu tablo üzerinden yapılır:
 *   {subdomain}.nevaqr.com  ->  restaurants.subdomain
 *
 * subdomain alanı NULL olabilir: kullanıcı henüz alt domain talebinde bulunmamış
 * ya da talep admin onayında beklemede olabilir. Yalnızca status = 'approved'
 * ve subdomain dolu olan kayıtlar canlıda yayınlanır.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();                 // panel içi tekil referans
            $table->string('subdomain')->nullable()->unique(); // canlı alt domain etiketi (ör. "lumina")

            // Şablon & yaşam döngüsü
            $table->string('template')->default('cafe');       // 'cafe' | 'restaurant'
            $table->string('status')->default('draft');        // draft | pending | approved | rejected | suspended

            // Marka / görsel kimlik
            $table->string('logo_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('primary_color', 9)->default('#0F0F0F');
            $table->string('secondary_color', 9)->default('#F5F5F4');
            $table->string('accent_color', 9)->default('#C8A96A');
            $table->string('font_family')->default('Inter');

            // İşletme bilgileri
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('instagram')->nullable();
            $table->string('website')->nullable();
            $table->json('opening_hours')->nullable();

            // Menü ayarları
            $table->string('currency', 3)->default('TRY');
            $table->string('locale', 5)->default('tr');
            $table->boolean('show_prices')->default(true);
            $table->boolean('show_calories')->default(false);

            // Onay akışı izleri
            $table->timestamp('submitted_at')->nullable();     // "Onaya Gönder" tıklandığı an
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'subdomain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
