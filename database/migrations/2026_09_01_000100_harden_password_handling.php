<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * GÜVENLİK: düz-metin geçici şifre saklama kaldırıldı.
 *
 * Eski akış: admin geçici şifreyi ekranda görüyordu; şifre users.temp_password
 * ve membership_requests.temp_password kolonlarında HASH'SİZ duruyordu.
 * Yeni akış: kullanıcıya tek kullanımlık, süreli "şifre belirle" linki gider.
 * Token veritabanında yalnızca hash'i ile tutulur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password_setup_token', 64)->nullable()->after('must_change_password');
            $table->timestamp('password_setup_expires_at')->nullable()->after('password_setup_token');
        });

        // Ortalıkta kalan düz metin şifreleri temizle, sonra kolonları düşür.
        if (Schema::hasColumn('users', 'temp_password')) {
            DB::table('users')->update(['temp_password' => null]);
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('temp_password');
            });
        }

        if (Schema::hasColumn('membership_requests', 'temp_password')) {
            DB::table('membership_requests')->update(['temp_password' => null]);
            Schema::table('membership_requests', function (Blueprint $table) {
                $table->dropColumn('temp_password');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['password_setup_token', 'password_setup_expires_at']);
            $table->string('temp_password')->nullable();
        });

        Schema::table('membership_requests', function (Blueprint $table) {
            $table->string('temp_password')->nullable();
        });
    }
};
