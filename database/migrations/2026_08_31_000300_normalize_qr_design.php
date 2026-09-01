<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * qr_designs anahtarları yeniden tasarlandı (renk → dekoratif çerçeve).
 * Eski anahtarları (siyah, gece-logo ...) yeni varsayılana taşı + kolon default'unu güncelle.
 */
return new class extends Migration
{
    public function up(): void
    {
        $valid = array_keys(config('neva.qr_designs'));

        DB::table('restaurants')
            ->when(true, fn ($q) => $q->whereNotIn('qr_design', $valid))
            ->update(['qr_design' => 'fircadarbe']);

        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('qr_design', 40)->default('fircadarbe')->change();
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('qr_design', 40)->default('siyah')->change();
        });
    }
};
