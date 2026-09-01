<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plan mimarisi: aylık abonelik + deneme → TEK SEFERLİK paket fiyatlandırması.
 *   - Hosting Hariç Paket
 *   - Hosting Dahil Paket
 *   - Fiziksel QR Basım & Kurulum Paketi (15 masaya kadar + masa başı ek ücret)
 *
 * (interval sütunu kalıyor ama PlanSeeder her pakete 'once' yazıyor.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('tagline')->nullable()->after('name');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->unsignedInteger('setup_table_limit')->nullable()->after('max_products');
            $table->decimal('extra_table_price', 10, 2)->nullable()->after('setup_table_limit');
        });

        if (Schema::hasColumn('plans', 'trial_days')) {
            Schema::table('plans', fn (Blueprint $t) => $t->dropColumn('trial_days'));
        }
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'is_featured', 'setup_table_limit', 'extra_table_price']);
            $table->unsignedInteger('trial_days')->default(14);
        });
    }
};
