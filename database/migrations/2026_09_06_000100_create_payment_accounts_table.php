<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Havale/EFT hesapları.
 *
 * Önceden tek hesap `.env`'de sabitti (NEVA_BANK_*). Birden fazla banka
 * gerekiyor ve bunu her seferinde sunucuya girip .env düzenleyerek yapmak
 * doğru değil — admin panelinden yönetilebilmeli.
 *
 * Tablo boşken uygulama eski .env değerlerine düşer: geriye dönük uyumlu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();

            $table->string('bank_name');            // "Ziraat Bankası"
            $table->string('account_name');         // hesap sahibinin adı
            $table->string('iban', 34);             // normalize saklanır: boşluksuz, büyük harf
            $table->string('note')->nullable();     // "TL hesabı", "yalnızca EFT" gibi

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            // Aynı IBAN iki kez girilmesin — müşteriye çift hesap gösterilmemeli.
            $table->unique('iban');
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_accounts');
    }
};
