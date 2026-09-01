<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('logo_size', 10)->default('medium')->after('logo_path');
            $table->string('heading_color', 9)->nullable()->after('accent_color');
        });

        // Eski 2 şablon değerini yeni 10'lu sisteme eşle.
        DB::table('restaurants')->where('template', 'cafe')->update(['template' => 'minimalist-kaffe']);
        DB::table('restaurants')->where('template', 'restaurant')->update(['template' => 'dark-prestige']);
        DB::table('restaurants')->whereNotIn('template', [
            'minimalist-kaffe', 'dark-prestige', 'neon-street', 'botanical-green', 'classic-bistro',
            'modern-grid', 'sunset-orange', 'royal-blue', 'compact-fast', 'artisan-crafted',
        ])->update(['template' => 'minimalist-kaffe']);

        // template varsayılanını güncelle
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('template')->default('minimalist-kaffe')->change();
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['logo_size', 'heading_color']);
            $table->string('template')->default('cafe')->change();
        });
    }
};
