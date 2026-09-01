<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Yayına alma otomasyonu + önbellek sürümleme.
 *
 *  menu_version         : panelde her değişiklikte artar; cache anahtarı değişir,
 *                         böylece güncelleme ANINDA canlıya yansır (TTL beklenmez).
 *  publish_status       : yayın işinin durumu (queued|dns|verifying|live|failed)
 *  publish_error        : son hata mesajı (admin ekranında görünür)
 *  dns_provisioned_at   : DNS kaydının açıldığı an (wildcard ise onay anı)
 *  verified_at          : otomatik HTTP doğrulaması ilk kez başarılı olduğu an
 *  last_health_check_at : en son sağlık kontrolü
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->unsignedInteger('menu_version')->default(1)->after('status');
            $table->string('publish_status', 20)->nullable()->after('menu_version');
            $table->text('publish_error')->nullable()->after('publish_status');
            $table->timestamp('dns_provisioned_at')->nullable()->after('publish_error');
            $table->timestamp('verified_at')->nullable()->after('dns_provisioned_at');
            $table->timestamp('last_health_check_at')->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'menu_version', 'publish_status', 'publish_error',
                'dns_provisioned_at', 'verified_at', 'last_health_check_at',
            ]);
        });
    }
};
