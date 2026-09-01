<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Geçici şifre akışı:
 *   - must_change_password = true iken kullanıcı, şifresini değiştirmeden
 *     panele/admine erişemez (EnsurePasswordChanged middleware).
 *   - temp_password: admin'in görüp kullanıcıya ilettiği düz-metin geçici şifre.
 *     Kullanıcı şifresini değiştirince temizlenir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
            $table->string('temp_password')->nullable()->after('must_change_password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['must_change_password', 'temp_password']);
        });
    }
};
