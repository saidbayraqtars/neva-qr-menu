<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QR kod tasarımı — 10 hazır stil (config/neva.php › qr_designs).
 * Renk + (opsiyonel) merkezde işletme logosu. Tüm QR çıktıları bunu kullanır.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('qr_design', 40)->default('siyah')->after('external_menu_url');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('qr_design');
        });
    }
};
