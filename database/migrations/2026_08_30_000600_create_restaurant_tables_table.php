<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fiziksel masalar — her masa için tekil bir QR jetonu (qr_token) üretilir.
 * QR PNG çıktısı şu adrese yönlendirir:
 *   https://{subdomain}.nevaqr.com/m/{qr_token}
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();

            $table->string('label');                 // "Masa 1", "Teras 4"
            $table->string('qr_token', 32)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();

            $table->timestamps();

            $table->index(['restaurant_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
