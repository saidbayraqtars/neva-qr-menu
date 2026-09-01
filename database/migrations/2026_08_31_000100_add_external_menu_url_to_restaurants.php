<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Hosting Hariç" paketi: kullanıcı kendi barındırdığı menünün web adresini girer,
 * sistem bu dış linke özel bağımsız (masa/bahçe/alt domain içermeyen) statik QR üretir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('external_menu_url', 2048)->nullable()->after('subdomain');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('external_menu_url');
        });
    }
};
