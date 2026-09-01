<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Silinen restoranın alt domaini SERBEST kalsın.
 *
 * `subdomain` tekil indeksli; kayıt yumuşak silindiğinde satır durduğu için
 * etiket sonsuza dek rezerve kalıyordu. Silme anında etiket buraya taşınır,
 * `subdomain` NULL'lanır — böylece ad yeniden alınabilir; geri yükleme
 * (restore) durumunda etiket hâlâ boştaysa sahibine iade edilir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('released_subdomain')->nullable()->after('subdomain');
            $table->timestamp('subdomain_released_at')->nullable()->after('released_subdomain');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['released_subdomain', 'subdomain_released_at']);
        });
    }
};
